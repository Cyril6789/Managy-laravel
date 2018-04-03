<?php session_start();
if(!is_array($sidebar['zeendoc']))
    $sidebar['zeendoc'] = Array();
$sidebar['zeendoc']['first'] = 'cloud|'.ZEENDOC_SB_ZEENDOC;
$sidebar['zeendoc'][] = 'osef|-1|'.ZEENDOC_SB_ZEENDOC_SETTINGS.'|zeendoc';
?>
