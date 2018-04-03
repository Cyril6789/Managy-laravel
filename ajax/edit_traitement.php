<?php session_start();

require('../classes/mysql.class.php');

$db = new MySQL();

$traitement = $db->SQLfix($_GET['traitement']);
$id = $db->SQLfix($_GET['id']);

$db->Query('UPDATE traitements SET nom = "'.$traitement.'" WHERE id ="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');

echo $id;
?>
