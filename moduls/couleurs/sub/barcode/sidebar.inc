<?php session_start();
if(!is_array($sidebar['admin']))
    $sidebar['admin'] = Array();
$sidebar['admin'][] = 'colors|4|'.COULEUR_SB_SEE_BARCODE.'|couleurs-barcode';
?>
