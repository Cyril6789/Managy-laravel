<?php session_start();


$tab = new DatabaseWorker('se_types', false);
$tab->setWidget('Liste des système d\'exploitation types');
$tab->displayedFields('nom');
$tab->labelsDisplayedFields('Nom du Système d\'exploitation');
$tab->activateAdd();
$tab->activeModify();
$tab->activeDelete();

$row = new Row($tab);
echo $row;
?>
