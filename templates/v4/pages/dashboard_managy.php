<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 20/03/2017
 * Time: 10:52
 */

$ligne = '';
foreach ($tab_abo AS $abo)
{
    $unjour = 60 * 60 * 24;
    if($abo['paye']) {
        $couleur = 'success';
        $texte = 'Abonné';
    }
    else
    {
        $couleur = 'warning';
        $texte = 'Evaluation';
    }

    $lg = 22;
    if(strlen($abo['nom']) > $lg)
        $nom = mb_strimwidth($abo['nom'], 0, $lg, '...');
    else
        $nom = $abo['nom'];

    $ribbon = new Ribbon('<a href="?compte='.$abo['id'].'">'.$nom.'</a>', $texte, $couleur, 'right', 'ribbon_'.$abo['id']);
    $ribbon->setIcone('industry');
    $jours_restants = round(($abo['fin_abo'] - time()) / $unjour);

    if(!$abo['paye'])
    {
        $jours_abo = round(($abo['fin_abo']-$abo['date_inscription'])/$unjour);
        $jour_consommes = round((time() - $abo['date_inscription']) / $unjour);
    }
    else
    {
        $fa = parse_date($abo['fin_abo']);
        $date_renouvellement = minusMonthsToDate($abo['fin_abo'], $abo['mois_prolonge']);
        $jours_abo = round(($abo['fin_abo']-$date_renouvellement )/$unjour);
        $jour_consommes = round((time() - $date_renouvellement ) / $unjour);
    }

    $progress = new ProgressBar($jour_consommes, $jours_abo, 'jours ('.$jours_restants.' jours restants)');
    $progress->display_text();

    $coordonnees = '<strong>Date inscription : </strong>'.parse_date($abo['date_inscription']).'<br />Téléphone : '.$abo['tel'].'<br />E-mail : '.$abo['mail_contact'].'<br />Adresse : '.$abo['adresse'];

    $team = '<br /><br /><strong>Equipe :</strong><br />';
    foreach($abo['staffs'] AS $s)
    {
        $staff = new GetInfosStaff($s['id']);
        
        if($staff->getGerant())
            $font = 'user-circle-o';
        else
        {
            if($s['id_l'])
            {
                if($s['incluse'])
                    $font = 'check';
                else
                {
                    if($s['date_fin'] > time())
                        $font = 'unlock';
                    else
                        $font = 'lock';
                }
            }
            else
                $font = 'times';
        }
            
        $team .= new Font($font).' <a href="'.$staff->GetProfileLink().'?compte='.$abo['id'].'">'.$staff->GetPrenom().' '.$staff->GetNom().'</a><br />';
    }



    $histo = new modalAjax('general', 'histo_prolongement', $abo['id']);
    $histo->settings(array('id_compte' => $abo['id']));


    $prolonger = new modalAjax('general', 'prolonger_manuellement', $abo['id']);
    $prolonger->settings(array('id_compte' => $abo['id']));

    //$histo->setDebug();

    $bouton_prol = new Button(new Font('refresh').' Prolonger', $prolonger->getHref());
    $bouton_prol->setClasse('btn-primary btn-xs');


    $bouton_histo = new Button(new Font('history').' Historique', $histo->getHref());
    $bouton_histo->setClasse('btn-primary btn-xs');

    $more = $coordonnees.$team;

    $content = '<p>Abonné jusqu\'au : '.date('d/m/Y', $abo['fin_abo']).'<br />'.$bouton_prol.' '.$bouton_histo.'<br />'.$progress.'</p><p style="text-align:center;"><a href="javascript:void();" onclick="$(\'#more_'.$abo['id'].'\').toggle(\'slow\');   $(this).text($(this).text() == \'Voir plus\' ? \'Voir moins\' : \'Voir plus\');">Voir plus</a></p><p id="more_'.$abo['id'].'"  style="display: none;">'.$more.'</p>';


    $ribbon->setContent($content.$histo->getModalHtml().$prolonger->getModalHtml());
    $col = new Col(4);
    $col->setContent($ribbon);
    $ligne .= $col;
}

$row = new Row($ligne);
echo $row;


$widget_graphique = new WidgetBox('Interventions ces 12 derniers mois', 12);

$graph_inter = new GraphsLines('graph_inters');
$graph_inter->addColor('#67B7DC', 'Intervention atelier ');

$table_time_atelier = Array();
$table_exist_atelier = array();

$tab_mois = Array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aôut', 'Septembre', 'Octobre', 'Novembre', 'Décembre');

foreach($tab_graph AS $val)
{
    if($val['type'] == 1)
    {
        $time = $tab_mois[$val['mois'] - 1].' '.$val['annee']; //mktime(0,0,0,$val['mois'], 1, $val['annee']);
        $table_time_atelier[] = $time;
        $table_exist_atelier[$time]['val'] = $val['nb'];
        $graph_inter->addData($val['nb'], $time);
    }

}

$y = date('Y');
for($m = date('n') - 11; $m<=date('n'); $m++) {
    if ($m < 1) {
        $mois = $m + 12;
        $annee = $y - 1;

    } else {
        $mois = $m;
        $annee = $y;
    }
    $timestamp = $tab_mois[$mois - 1].' '.$annee; //mktime(0, 0, 0, $mois, 1, $annee);

    if(!in_array($timestamp, $table_time_atelier))
        $graph_inter->addData(0, $timestamp);
    else
        $graph_inter->addData($table_exist_atelier[$timestamp]['val'], $timestamp);
}


$graph_inter->addColor('yellow', 'Intervention sur site');
$table_time_site = Array();
$table_exist_site = array();

foreach($tab_graph AS $val)
{
    if($val['type'] == 2)
    {
        $time = $tab_mois[$val['mois'] - 1].' '.$val['annee']; //mktime(0,0,0,$val['mois'], 1, $val['annee']);
        $table_time_site[] = $time;
        $table_exist_site[$time]['val'] = $val['nb'];
        $graph_inter->addData($val['nb'], $time);
    }

}

for($m = date('n') - 11; $m<=date('n'); $m++) {
    if ($m < 1) {
        $mois = $m + 12;
        $annee = $y - 1;

    } else {
        $mois = $m;
        $annee = $y;
    }
    $timestamp = $tab_mois[$mois - 1].' '.$annee; //mktime(0, 0, 0, $mois, 1, $annee);

    if(!in_array($timestamp, $table_time_site))
        $graph_inter->addData(0, $timestamp);
    else
        $graph_inter->addData($table_exist_site[$timestamp]['val'], $timestamp);
}


$widget_graphique->setContent($graph_inter, false);


$row = new Row($widget_graphique);
echo $row;
//echo $widget_graphique;

/*
$comptes = new DatabaseWorker('comptes_principaux', false);
//$comptes->setDataTable();
$comptes->setWidget('Tableau des comptes principaux');
$comptes->displayedFields(Array('id', 'nom_societe', 'mail_contact', 'logo', 'web', 'tel', 'expediteur_sms', 'signature_sms', 'adresse', 'siret', 'ape', 'cal', 'bloque', 'fin_abo', 'prix_abo', 'nombre_staff', 'prix_staff_suppl', 'template'));
$comptes->labelsDisplayedFields(Array('N°', 'Nom de la société', 'Mail de contact', 'Image du logo', 'Site internet', 'Numéro de téléphone', 'Expediteur SMS', 'Signature SMS', 'Adresse', 'N° Siret', 'Code APE', 'Code calendrier', 'Compte bloqué ?', 'Fin d\'abonnement le', 'Prix de l\'abonnement', 'Nombre d\'employés inclus', 'Prix de l\'employé supplémentaire', 'Nom du template'));
$comptes->addDateFields(Array('fin_abo'));
$comptes->noModifyFields('id');
$comptes->activateAdd();
$comptes->activeModify();
//echo $comptes;
$row = new Row($comptes);
echo $row;*/



$messages_site = new DatabaseWorker('messages_site', false);
$messages_site->setWidget('Messages d\'information');
$messages_site->displayedFields(Array('message', 'date_debut', 'date_fin', 'type'));
$messages_site->labelsDisplayedFields(Array('Message à afficher', 'Affiché du', 'Au', 'Couleur du message'));
$messages_site->addDateTimeFields(Array('date_debut', 'date_fin'));
$messages_site->addWysiwygFields('message');
$messages_site->activeModify();
$messages_site->activateAdd();
$messages_site->activeDelete();
$row = new Row($messages_site);
echo $row;



if ($_SESSION['id'] == 1) {
    $choix_abo = new DatabaseWorker('choix_abo', false);
    $choix_abo->setWidget('Tableau des plans des abonnements');
    $choix_abo->displayedFields(Array('mois', 'coeff'));
    $choix_abo->labelsDisplayedFields(Array('Nombre de mois souscris', 'Nombre de mois payés'));
    $choix_abo->activeDelete();
    $choix_abo->activateAdd();
    $choix_abo->activeModify();
    $row = new Row($choix_abo);
    echo $row;




    $packs_sms = new DatabaseWorker('packs_sms', false);
    $packs_sms->setWidget('Packs SMS disponibles');
    $packs_sms->displayedFields(Array('qte', 'prix'));
    $packs_sms->labelsDisplayedFields(Array('Quantité dans le pack', 'Prix HT du Pack'));
    $packs_sms->activeModify();
    $packs_sms->activateAdd();
    $packs_sms->activeDelete();
    $packs_sms = new Row($packs_sms);
    echo $packs_sms;
}



$maintenance_site = new DatabaseWorker('maintenance_site', false);
$maintenance_site->setWidget('Maintenance du site');
$maintenance_site->noDatatable();
$maintenance_site->displayedFields(Array('date', 'texte', 'texte_prevention', 'active'));
$maintenance_site->labelsDisplayedFields(Array('Date de début de maintenance', 'Texte à afficher pour les non autorisés', 'Texte de prévention', 'Maintenance active ?'));
$maintenance_site->addDateTimeFields('date');
$maintenance_site->activeModify();

$row = new Row($maintenance_site);
echo $row;

