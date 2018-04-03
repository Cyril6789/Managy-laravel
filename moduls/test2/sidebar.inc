<?php session_start();

if(!is_array($sidebar['test']))
    $sidebar['test'] = Array();
$sidebar['test']['first'] = 'script-code|Pages de tests';
$sidebar['test'][] = 'test|1|Voir la page|test';
$sidebar['test'][] = 'test|1|Voir la page|test';

?>
