<?php session_start();
$error = new Danger(GEN_NO_RIGHTS, false);
echo $error;

?>