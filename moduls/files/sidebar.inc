<?php session_start();
if(!is_array($sidebar['files']))
    $sidebar['files'] = Array();
$sidebar['files']['first'] = 'file|Fichiers';
$sidebar['files'][] = 'dash|1|Voir les fichiers|files';
?>
