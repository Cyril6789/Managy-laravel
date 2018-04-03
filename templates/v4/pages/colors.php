<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 21/07/2016
 * Time: 15:33
 */

$couleur = new DatabaseWorker('couleurs');
$couleur->setWidget('Tableau des couleurs', 6);
$couleur->displayedFields(Array('nom', 'ral'));
$couleur->labelsDisplayedFields(Array('Couleur', 'Ral'));
$couleur->activateAdd();
$couleur->activeModify();

$aspects = new DatabaseWorker('aspects');
$aspects->setWidget('Tableau des finitions', 6);
$aspects->displayedFields(Array('nom'));
$aspects->labelsDisplayedFields(Array('Aspect'));
$aspects->activeModify();
$aspects->activateAdd();

echo $couleur.$aspects;

