<?php session_start();


$tab = new DatabaseWorker('antivirus_types', false);
$tab->setWidget('Liste des antivirus types');
$tab->displayedFields('nom');
$tab->labelsDisplayedFields('Nom de l\'antivirus');
$tab->activateAdd();
$tab->activeModify();
$tab->activeDelete();

$row = new Row($tab);
echo $row;
?>
