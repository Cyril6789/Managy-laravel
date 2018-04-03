<?php session_start();
if(!is_array($sidebar['admin']))
        $sidebar['admin'] = Array();  
$sidebar['admin'][] = 'mail|1|'.MAIL_SB_SEE.'|mail';
?>
