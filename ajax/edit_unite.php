<?php session_start();

require('../classes/mysql.class.php');

$db = new MySQL();

$unite = $db->SQLfix($_GET['unite']);
$id = $db->SQLfix($_GET['id']);

$db->Query('UPDATE unites_bls SET nom = "'.$unite.'" WHERE id ="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');

echo $id;
?>
