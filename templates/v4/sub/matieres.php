<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 21/07/2016
 * Time: 08:59
 */


$matieres = new DatabaseWorker('matieres');
$matieres->setWidget('Tableau des matières');
$matieres->labelsDisplayedFields(Array('Nom de la matière'));
$matieres->displayedFields(Array('nom'));
$matieres->activateAdd();
$matieres->activeDelete();
$matieres->activeModify();

$row = new Row($matieres);
echo $row;

?>