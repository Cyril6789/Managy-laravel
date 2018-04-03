<?php session_start();


$tab = new DatabaseWorker('prestations_types', false);
$tab->setWidget('Liste des prestations types');
$tab->displayedFields(Array('designation', 'duree_defaut'));
$tab->labelsDisplayedFields(Array('Désignation', 'Durée par défaut'));
$tab->activateAdd();
$tab->activeModify();
$tab->activeDelete();

$row = new Row($tab);
echo $row;
?>
