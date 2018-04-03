<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/08/2017
 * Time: 09:12
 */

$subscribe = new jsonModal(new Font('shopping-cart').' Prolongez votre abonnement');

$subscribe->hideButtons();

if($_SESSION['gerant'])
{
    $subscribe->width('80%');
    /* liste des module activés*/
    $db->Query('SELECT nom, nom_propre, prix FROM modules_comptes WHERE type="2" AND compte_principal="' . $_SESSION['compte_principal'] . '" ');
    $tab_mods_active = Array();
    $tab_mods_not_active = Array();
    $i = 0;
    $j = 0;
    while ($row = $db->Row()) {

        if (in_array($row->nom, $_SESSION['modules']) AND $row->prix > 0) {
            $tab_mods_active[$i]['link'] = $row->nom;
            if ($row->nom_propre)
                $tab_mods_active[$i]['nom'] = $row->nom_propre;
            else
                $tab_mods_active[$i]['nom'] = $row->nom;
            $tab_mods_active[$i]['prix'] = $row->prix;
            $i++;
        }
    }

    /* NB licences staffs*/
    $sql = 'SELECT COUNT(*) AS nb FROM licences_staffs WHERE compte_principal = "' . $_SESSION['compte_principal'] . '" AND incluse="0"';
    $db->Query($sql);
    $row = $db->Row();
    $nb_licences_suppl = $row->nb;

    $sql = 'SELECT prix_staff_suppl FROM comptes_principaux WHERE id = "' . $_SESSION['compte_principal'] . '" ';
    $db->Query($sql);
    $row = $db->Row();
    $prix_staff_suppl = $row->prix_staff_suppl;

    $sql = 'SELECT nom FROM licences_staffs WHERE compte_principal = "' . $_SESSION['compte_principal'] . '" AND incluse="0"';
    $db->Query($sql);
    $tab_licences_suppl = Array();
    while ($row = $db->Row()) {
        $tab_licences_suppl[] = $row->nom;
    }

    /*
                 * Choix abo
                 */

    $sql = 'SELECT mois, coeff FROM choix_abo ORDER BY mois DESC';
    $tab_choix_abo = Array();
    $i = 0;
    $db->Query($sql);
    while ($row = $db->Row()) {
        $tab_choix_abo[$i]['mois'] = $row->mois;
        $tab_choix_abo[$i]['coeff'] = $row->coeff;
        $i++;
    }
    $nb_choix = $i;


    if (END_SUBSCRIPTION < time() - (10 * 24 * 60 * 60) AND $prix_t)
        $extend_modal->disableClose();

    $abo = new WidgetBox('Choisissez la durée de votre abonnement');

    $l = '';
    foreach ($tab_choix_abo AS $choix) {

        $prix_t = PRICE_SUBSCRIPTION * $choix['coeff'];
        foreach ($tab_mods_active AS $mod)
            $prix_t += $mod['prix'] * $choix['coeff'];

        $prix_t += $nb_licences_suppl * $prix_staff_suppl * $choix['coeff'];

        if ($choix['coeff'] - $choix['mois'] > 0) {
            $m = ($choix['coeff'] - $choix['mois']) * 100;
            $majo = '<br />(+' . $m . '%)';
        } else {
            $month = round($prix_t / $choix['mois'], 2);

            $majo = '<br />('.$month.'€ HT/mois)';
        }


        $f1 = new WidgetBox($choix['mois'] . ' mois : ' . $prix_t . '€ HT ' . $majo, round(12 / $nb_choix));
        $f1_html = 'Abonnement : ' . PRICE_SUBSCRIPTION * $choix['coeff'] . '€ HT<br />';
        $t_options = 0;
        foreach ($tab_mods_active AS $mod)
            $t_options += $mod['prix'] * $choix['coeff'];
        $f1_html .= 'Options : ' . $t_options . '€ HT<br />';
        $f1_html .= 'Licences : ' . $nb_licences_suppl * $prix_staff_suppl * $choix['coeff'] . '€ HT<br />';

        $checkbox_f1 = new Radio('formule', $choix['mois']);
        if (!$l)
            $checkbox_f1->checked();
        $checkbox_f1->onChange('changeDuration(' . $choix['mois'] . ');');
        if ($choix['mois'] - $choix['coeff'] > 0)
            $offre_f1 = new Label('success', $choix['mois'] - $choix['coeff'] . ' mois offerts');
        else
            $offre_f1 = '';
        $f1_html .= '<div style="text-align: center;">' . $checkbox_f1 . '<br />' . $offre_f1 . '</div>';
        $f1->setContent($f1_html);

        $l .= $f1;
    }

    $ligne = new Row($l);

    $abo->setContent($ligne, false);


    $modules = new WidgetBox('Vos options');

    $options = new Feeds();
    $options->noIcone();
    foreach ($tab_mods_active AS $mod)
        $options->addLine($mod['nom'], $mod['prix'] . ' € HT / mois');

    $modules->setContent($options, false);

    $wlicences = new WidgetBox('Licences employés');
    $licences = new Feeds();
    $licences->noIcone();
    foreach ($tab_licences_suppl AS $lic)
        $licences->addLine($lic, $prix_staff_suppl . ' € HT / mois');
    $wlicences->setContent($licences, false);


    $paiement = new WidgetBox('Paiement');


    //$bouton_paypal = new Button('<i class="fa fa-paypal"></i> Payer avec Paypal', './Paypal/preparePayment.php?mois=12', '', 'paypal_btn');
    $bouton_paypal = new Button(new Font('envelope').' Contactez-nous en cliquant ici', 'mailto:contact@managy.fr?subject=Demande de prolongement d\'abonnement à Managy', '', 'btn');

    $bouton_paypal->setClasse('btn-primary');



    $paiement->setContent('<div style="text-align: center">' . $bouton_paypal . '</div>', false);
    //$paiement->setContent('<div style="text-align: center"><a href="mailto:contact@managy.fr?subject=Demande de prolongement d\'abonnement à Managy">Contactez-nous en cliquant ici contact@managy.fr </a></div>', false);


    $gauche = new Col(8);
    $gauche->setContent($abo);

    $ligne = new  Row($modules . $wlicences);
    $droite = new Col(4);
    $droite->setContent($ligne);


    $subscribe->content($gauche . $droite . $paiement);
}
else {
    $alert = new Danger('Votre accès ne permet pas de pronloger l\'abonnement de ce compte. Veuillez en référer à votre gérant.', false);
    $subscribe->content($alert);
    $subscribe->error();
}
echo $subscribe;