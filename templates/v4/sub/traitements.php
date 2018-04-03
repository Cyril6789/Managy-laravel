<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 21/07/2016
 * Time: 15:30
 */

$traitements = new DatabaseWorker('traitements');
$traitements->setWidget('Tableaux des traitements');
$traitements->displayedFields(Array('nom'));
$traitements->labelsDisplayedFields(Array('Nom du traitement'));
$traitements->activeModify();
$traitements->activeDelete();
$traitements->activateAdd();

$row = new Row($traitements);
echo $row;