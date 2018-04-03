<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 21/09/2017
 * Time: 19:18
 */

$zeendoc = new DatabaseWorker('zeendoc');
$zeendoc->setWidget('Liste des classeurs configurés');
$zeendoc->displayedFields(Array('nom', 'adresse', 'mdp'));
$zeendoc->labelsDisplayedFields(Array('Nom du classeur', 'Adresse FTP', 'Mot de passe'));
$zeendoc->passwordFields('mdp');
$zeendoc->activeDelete();
$zeendoc->activateAdd();
$zeendoc->activeModify();
echo $zeendoc;