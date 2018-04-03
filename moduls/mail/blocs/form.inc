<?php session_start();

if($dialog)
    $style = 'style="display: none;"';
else
    $style = '';

include('./templates/'.TEMPLATE_NAME.'/blocs/mailForm.php');

?>


