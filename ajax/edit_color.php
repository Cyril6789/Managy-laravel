<?php session_start();

require('../classes/mysql.class.php');

$db = new MySQL();

$couleur = $db->SQLfix($_GET['couleur']);
$ral = $db->SQLfix($_GET['ral']);
$id = $db->SQLfix($_GET['id']);

$db->Query('UPDATE couleurs SET nom = "'.$couleur.'", ral="'.$ral.'" WHERE id ="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');

echo $id;
?>
