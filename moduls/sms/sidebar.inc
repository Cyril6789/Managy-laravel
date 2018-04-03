<?php session_start();
if(!is_array($sidebar['admin']))
        $sidebar['admin'] = Array();  
$sidebar['admin'][] = 'sms|1|'.SMS_SB_SEE.'|sms';
?>
