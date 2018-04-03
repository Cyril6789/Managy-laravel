<?php session_start();

$error = new Danger(INTERVENTION_NO_INTER_WITH_THIS_NUM, false);
echo $error;
?>