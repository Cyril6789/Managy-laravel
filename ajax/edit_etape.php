<?php session_start();

require('../classes/mysql.class.php');

$db = new MySQL();

$etape = $db->SQLfix($_GET['etape']);
$id = $db->SQLfix($_GET['id']);
$time = $db->SQLfix($_GET['time']);
if($time == 'y')
    $t = '1';
else
    $t = '0';

$db->Query('UPDATE etapes SET nom = "'.$etape.'", temps = "'.$t.'" WHERE id ="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');

echo $id;
?>
