<?php session_start();

if(isset($_GET['rdv_suppr']) AND is_numeric($_GET['rdv_suppr']))
{
global $db;
$id_rdv = $db->SQLfix($_GET['rdv_suppr']);


$db->Query('DELETE FROM rdv WHERE id="'.$id_rdv.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
header('Location: ./dashboard#tab_dashboard_calendar');


}