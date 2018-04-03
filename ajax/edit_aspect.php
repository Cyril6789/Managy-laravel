<?php session_start();

require('../classes/mysql.class.php');

$db = new MySQL();

$aspect = $db->SQLfix($_GET['aspect']);
$id = $db->SQLfix($_GET['id']);

$db->Query('UPDATE aspects SET nom = "'.$aspect.'" WHERE id ="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');

echo $id;
?>
