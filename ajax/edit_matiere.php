<?php session_start();

require('../classes/mysql.class.php');

$db = new MySQL();

$matiere = $db->SQLfix($_GET['matiere']);
$id = $db->SQLfix($_GET['id']);

$db->Query('UPDATE matieres SET nom = "'.$matiere.'" WHERE id ="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');

echo $id;
?>
