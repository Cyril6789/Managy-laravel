<?php session_start();


$tab = new DatabaseWorker('materiels_types', false);
$tab->setWidget('Liste des matériels types');
$tab->displayedFields('nom');
$tab->labelsDisplayedFields('Nom du matériel');
$tab->activateAdd();
$tab->activeModify();
$tab->activeDelete();

$row = new Row($tab);
echo $row;
?>
