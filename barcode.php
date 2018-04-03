<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 16/02/2017
 * Time: 14:57
 */

include('./classes/pi_barcode.class.php');

$bc = new pi_barcode();
$bc->setCode($_GET['id_inter']);
$bc->setType('C128');
$bc->setSize(30, 150, 0);

// Texte sous les barres :
//    'AUTO' : affiche la valeur du codes barres
//    '' : n'affiche pas de texte sous le code
//    'texte a afficher' : affiche un texte libre
//        sous les barres
$bc->setText('');

// Si elle est appelée, cette méthode désactive
// l'impression du Type de code (EAN, C128...)
$bc->hideCodeType();

// Couleurs des Barres, et du Fond au
// format '#rrggbb'
$bc->setColors('#000000', '#FFFFFF');
// Type de fichier : GIF ou PNG (par défaut)
$bc->setFiletype('PNG');
$bc->showBarcodeImage();
?>