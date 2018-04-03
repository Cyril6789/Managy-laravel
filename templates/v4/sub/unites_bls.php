<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 22/07/2016
 * Time: 17:33
 */

$unites = new DatabaseWorker('unites_bls');
$unites->setWidget('Tableau des unités de Bl');
$unites->displayedFields(Array('nom'));
$unites->labelsDisplayedFields(Array('Nom'));
$unites->activateAdd('bl-unites', 'AddUnitBl');
$unites->activeModify('bl-unites', 'ModifyUnitBl');
$unites->activeDelete('bl-unites', 'DeleteUnitBl');

$row = new Row($unites);
echo $row;