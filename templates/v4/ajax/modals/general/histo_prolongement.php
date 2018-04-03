<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 18/11/2017
 * Time: 13:48
 */

$id_compte = $db->SQLFix($_POST['id_compte']);

$modal = new jsonModal(new Font('history').' Historique des prolongements d\'abonnement');
$modal->width('90%');


if($_SESSION['compte_principal'] == 1)
{

    $cp = new DataObject('comptes_principaux');
    $cp->find($id_compte, 'id', false);

    $data = new DatabaseWorker('prolongements', false);
    $data->setWidget('Prolongement de '.$cp->nom_societe.' (compte '.$id_compte.')');
    $data->addWhere('compte_principal = '.$id_compte);
    $data->displayedFields(array('timestamp', 'mois_paye', 'mois_prolonge', 'prix_base', 'prix_total', 'transactionid', 'fin_abo'));
    $data->labelsDisplayedFields(array('Date du prolongement', 'Mois soucrits', 'Mois prolongés', 'Prix de base', 'Prix avec options', 'Transaction / Motif', 'Fin d\'abonnement'));
    $data->addDateTimeFields(array('timestamp', 'fin_abo'));
    $content = $data;
}
else
{
    $content = new Danger('Votre accès ne permet pas de voir ce contenu', 'false');
    $modal->error();
}


$modal->content($content);
echo $modal;