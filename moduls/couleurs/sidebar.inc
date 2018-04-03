<?php session_start();
if(!is_array($sidebar['stocks']))
    $sidebar['stocks'] = Array();
$sidebar['stocks']['first'] = 'cubes|'.COULEUR_SB_STOCK;
$sidebar['stocks'][] = 'colors|2|'.COULEUR_SB_SEE_STOCK.'|couleurs-stocks';
$sidebar['stocks'][] = 'colors|3|'.COULEUR_SB_MODIFY_STOCK.'|couleurs-modifystocks';
if(!is_array($sidebar['admin']))
    $sidebar['admin'] = Array();
$sidebar['admin'][] = 'colors|1|'.COULEUR_SB_SEE.'|couleurs';
?>
