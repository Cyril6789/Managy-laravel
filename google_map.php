<?php
include('./classes/mysql.class.php');
$db = new MySQL();
$req = "SELECT adresse, ville FROM clients WHERE id = '".$db->SQLFix($_GET['id'])."' ";
$db->Query($req);

$d = $db->Row();
echo $db->Error();

if($d->ville)
{
	$url = urlencode($d->adresse.', '.$d->ville);
	$redir = 'https://maps.google.fr/maps?saddr=32+Rue+Principale,+Bischoffsheim&daddr='.$url.'&hl=fr&mra=ls&t=m&z=12';
}else
	$redir = './';
	
header('location:'.$redir); 

?>