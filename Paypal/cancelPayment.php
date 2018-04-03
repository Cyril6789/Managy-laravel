<?php session_start();

if(empty($_SESSION['compte_principal']) OR empty($_SESSION['compte_principal'])){
    header('Location: ../dashboard');
    die();
}
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 22/03/2016
 * Time: 09:56
 */

require('../classes/mysql.class.php');
$db = new MySQL();

$token = $db->SQLFix($_GET['token']);
$db->Query('DELETE FROM paiements_en_cours WHERE token = "'.$token.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ');

$_SESSION['notify'] = 'cancelPayment';
header('Location: ../dashboard');

?>