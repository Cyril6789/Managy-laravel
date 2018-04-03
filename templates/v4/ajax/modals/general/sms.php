<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/08/2017
 * Time: 09:46
 */



$sms_modal = new jsonModal('<i class="fa fa-shopping-cart"></i> Acheter des SMS');
$sms_modal->hideButtons();

if(0)//$_SESSION['gerant'])
{
    $sms_widget = new FormLayout('Choisissez un pack');
    $sms_widget->setFormControls('sms_buy_form', './Paypal/prepare_sms.php', 'get');
    $sms_widget->setValueButton('Payez avec PayPal');

    $select_pack_sms = new Select('nb_sms');
    $select_pack_sms->withSearch();

    $packs = new DataObject('packs_sms');
    $tab = $packs->findAll(false);

    foreach ($tab AS $t)
        $select_pack_sms->addOption($t->qte, 'Pack de '.$t->qte.' SMS à '.$t->prix.'€ HT ('. round($t->prix/$t->qte, 2) .'€/sms)');

    $sms_widget->addLine('Pack :', $select_pack_sms);

    $sms_modal->content($sms_widget);
    $sms_modal->width('40%');
}
else
{
    $alert = new Danger('Votre accès ne permet pas d\'acheter des SMS. Veuillez en référer à votre gérant.', false);
    $sms_modal->content($alert);
    $sms_modal->error();
}

echo $sms_modal;