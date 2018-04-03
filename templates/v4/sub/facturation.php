<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/10/2016
 * Time: 20:05
 */

$widget = new WidgetBox('Tableau des interventions à facturer');
$tableau = new HtmlTable('', 'table table-bordered table-hover table-responsive table-datatable');
$tableau->addTSection('thead');
$tableau->addRow();
$tableau->addCell('#', '', 'thead');
$tableau->addCell('Client', '', 'thead');
$tableau->addCell('Type', '', 'thead');
$tableau->addCell('Matériel déposé', '', 'thead');
$tableau->addCell('Panne constatée, travaux demandés', '', 'thead');
$tableau->addCell('Matériel ajouté, remplacé', '', 'thead');
$tableau->addCell('Prestations effectuées', '', 'thead');
$tableau->addCell('Message interne', '', 'thead');
$tableau->addCell('Date de clôture', '', 'thead');
$tableau->addCell('facturations', '', 'thead');

$tableau->addTSection('tbody');
$tab_modal = Array();
foreach ($tab_inters AS $inter)
{
    $tableau->addRow();

    $label = new Label('success', $inter['prefix'].$inter['id_inter'], './i'.$inter['id_inter']);
    $tableau->addCell($label);

    $client = new GetInfosCustomer($inter['id_client']);



    //$popover = new

    $lien = '<a href="'.$client->GetProfileLink().'">'.$client->GetTitre().' '.$client->GetNom().' '.$client->GetPrenom().'</a>';

    $pop = new PopOver('Détails contact', $client->getFullName().'<br />'.$client->getFullAdress().'<br />Fixe : '.$client->getFixe().'<br />Portable : '.$client->getPort().'<br />Mail : '.$client->GetMail());
    $pop->setLink($lien);

    $dl_vcard = new Button(new Font('address-card').' Télécharger la vCard', './cdv-'.$inter['id_client']);
    $dl_vcard->setClasse('btn-primary btn-xs');
    $tableau->addCell($pop.'<br /><br />'.$dl_vcard);
    if($inter['type_atelier_rdv'] == 1)
        $tableau->addCell($inter['materiel']);
    else
        $tableau->addCell('Intervention sur site '.parse_date($inter['rdv_debut']));

    $tableau->addCell(nl2br($inter['matos']));
    $tableau->addCell(nl2br($inter['panne']));
    $tableau->addCell(nl2br($inter['materiel_ajoute']));

    $prestas = '';
    foreach ($tab_prestas[$inter['id_inter']] As $presta)
    {
        $prestas .= '-'.$presta['designation'].' : '.$presta['duree'].'h<br />';
    }
    $pm = '';
    if($inter['pmm'])
    {
        $pm = '<br /><strong>Déduit du pack maintenance :</strong><br />'.$inter['pmc'].' : '.$inter['pmm'].'h';
    }

    $tableau->addCell($prestas.$pm);
    $tableau->addCell(nl2br($inter['message_interne']));

    if($inter['type_atelier_rdv'] == 1)
        $fiche = '<a href="javascript:void();" onclick="window.open(\'./imprimer-'.$inter['id_inter'].'-s\', \'inscr\',\'width=700,height=680,left=100,top=10,scrollbars=yes\');">'.new Font('file-text').' Fiche sortie</a>';
    else
        $fiche = '<a href="javascript:void();" onclick="window.open(\'./inter-site-'.$inter['id_inter'].'\', \'inscr\',\'width=700,height=680,left=100,top=10,scrollbars=yes\');">'.new Font('file-text').' Fiche sortie</a>';

    $tableau->addCell(parse_date($inter['time_cloture']).'<br />'.$fiche);


    $modal = new modalAjax('intervention_informatique', 'facturation', $inter['id_inter']);
    $modal->oppenButton(new Font('eur').' Facturer', 'btn-success btn-xs', true, 'Classer cette intervention comme facturée');

    $modal->settings(array('id_inter' => $inter['id_inter']));

    $button_sellsy = '';
    if(AccesActivableModul('sellsy')) {
        $button_sellsy = new Button(new Font('eur') . ' Diag sur sellsy', 'https://www.managy.fr/moduls/sellsy/redirect/diagnostic.php?id_inter=' . $inter['id_inter']);
        $button_sellsy->setClasse('btn-xs btn-primary');
        $button_sellsy->setFullWidth();
       // $button_sellsy = $button_sellsy ;
    }

    $tableau->addCell($modal->getModalOpen().$button_sellsy);
    $tab_modal[] = $modal->getModalHtml();
}





$widget->setContent($tableau, false);
echo $widget;

foreach ($tab_modal As $mod)
    echo $mod;


$row = new Row($inters);
echo $row;

?>