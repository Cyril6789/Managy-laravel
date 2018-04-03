<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/08/2017
 * Time: 09:52
 */




$modal = new jsonModal('Nouvelle intervention');
$modal->width('70%');

$abo = round((time() - END_SUBSCRIPTION) / 60 / 60 / 24);
if($abo < DAYS_BEFORE_RESTRICTION OR FREE_ACCESS)
{
    if (right('inter', 2)) {

        require_once('./../moduls/customers/classes/SelectCustomer.class.inc');
        require_once('./../moduls/customers/langs/' . $_SESSION['hl'] . '.inc');
        require_once('./../moduls/intervention_informatique/sub/new/langs/' . $_SESSION['hl'] . '.inc');

        $selecteur_page = new SelectCustomer('intervention-new-customer-', 'intervention-new');

        //$selecteur->no_parent();
        $content = $selecteur_page->getHTML();
    } else {
        $content = new Danger('Vous n\'avec pas le droit de créer une intervention', false);
        $modal->error();
    }
}
else
{
    $content = new Danger('Votre accès est restreint. Merci de prolonger votre abonnement ou de contacter votre administrateur', false);
    $modal->error();
}
$modal->content($content);
$modal->hideButtons();

echo $modal;