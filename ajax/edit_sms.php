<?php session_start();

require('../classes/mysql.class.php');

$db = new MySQL();

$titre = $db->SQLfix($_GET['titre']);
$message = $db->SQLfix($_GET['message']);
$id = $db->SQLfix($_GET['id']);

$db->Query('UPDATE sms_types SET titre = "'.$titre.'", message = "'.$message.'" WHERE id ="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');

echo $id;
?>
