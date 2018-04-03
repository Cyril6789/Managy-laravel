<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 26/02/2017
 * Time: 16:35
 */




$mods = new DatabaseWorker('descriptions_modules', false);
$mods->displayedFields(Array('nom', 'texte', 'faq'));
$mods->labelsDisplayedFields(Array('Nom du module (liaison)', 'Description', 'Lien vers la FAQ'));
$mods->addWysiwygFields('texte');
$mods->noPrintModals();
$mods->activateAdd();
$mods->activeModify();
$mods->activeDelete();

$row = new Row($mods);
echo $row;

echo $mods->printAllModals();
?>