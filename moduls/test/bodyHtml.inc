<?php session_start();



ob_start();
include('./templates/'.TEMPLATE_NAME.'/pages/test.php');
$ModulContent = ob_get_contents();
ob_end_clean();
?>