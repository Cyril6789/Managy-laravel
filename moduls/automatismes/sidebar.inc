<?php session_start();
if(!is_array($sidebar['automatismes']))
    $sidebar['automatismes'] = Array();
$sidebar['automatismes']['first'] = 'magic|Automastismes';
$sidebar['automatismes'][] = 'automatismes|1|'.AUTOMATISMES_LIST_SB_SEE.'|automatismes';
?>
