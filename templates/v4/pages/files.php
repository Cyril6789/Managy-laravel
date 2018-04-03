<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 19/02/2017
 * Time: 18:11
 */

$fichiers = new elFinderPerso();
$fichiers->addPath('e/', 'Fichiers communs');

$fichiers->addPath('p'.$_SESSION['id'].'/', 'Mes fichiers personnels');
$fichiers->addPath('i/', 'Fichiers fiches interventions');
$fichiers->addPath('c/', 'Fichiers fiches clients');
echo $fichiers;

?>