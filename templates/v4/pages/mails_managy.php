<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 23/06/2017
 * Time: 15:20
 */

$mails = new DatabaseWorker('mails_managy', false);
$mails->setWidget('Liste des mails automatiques');
$mails->displayedFields(Array('nom', 'titre', 'sujet', 'message', 'jours', 'inscription_finabo'));
$mails->labelsDisplayedFields(Array('Nom du mail', 'Titre', 'Sujet', 'Corp du message', 'Nombre de jours avant ou après', 'Date d\'inscription ou fin d\'abonnement'));
$mails->addWysiwygFields('message');
$mails->activateAdd();
$mails->activeModify();
$mails->activeDelete();


$row = new Row($mails);
echo $row;