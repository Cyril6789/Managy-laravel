<?php session_start();
if(!is_array($sidebar['admin']))
        $sidebar['admin'] = Array();  
$sidebar['admin'][] = 'notify|-1|'.NOTIFY_SB_SEE.'|notifys';
?>
