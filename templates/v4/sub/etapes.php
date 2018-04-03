<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 21/07/2016
 * Time: 15:26
 */
$etapes = new DatabaseWorker('etapes');
$etapes->setWidget('Tableau des étapes');
$etapes->displayedFields(Array('nom', 'temps'));
$etapes->labelsDisplayedFields(Array('Nom de l\'étape', 'Gestion du temps'));
$etapes->activateAdd();
$etapes->activeDelete();
$etapes->activeModify();

$row = new Row($etapes);
echo $row;