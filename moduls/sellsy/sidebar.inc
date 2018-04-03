<?php session_start();
if(!is_array($sidebar['admin']))
    $sidebar['admin'] = Array();
$sidebar['admin'][] = 'sellsy|1|'.SELLSY_SB_SEE.'|sellsy';
?>
