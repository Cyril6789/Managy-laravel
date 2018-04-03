<?php session_start();
require('../classes/mysql.class.php');

$db = new MySQL();

$db->Query('SELECT count(*) AS nb FROM chat WHERE id_destinataire = "'.$_SESSION['id'].'" AND lu = "0" ');
$r = $db->Row();
if($r->nb)
    echo $r->nb;

?>