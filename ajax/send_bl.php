<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 10/04/2017
 * Time: 11:20
 */

if(empty($_SESSION['id']))
    die();

include('./../classes/mysql.class.php');
$db = new MySQL();

include('./../classes/Maileur.class.php');
include('./../classes/ExternalLink.class.php');
include('./../constructPage/settings.gen.inc');


$t = explode('_', $_POST['mail']);

$id_client = $t[0];
$id = $db->SQLFix($_POST['id']);
$mail = new Maileur($_POST['sujet']);
$mail->addDest($t[1]);
//$mail->addExpediteur(MAIL_CONTACT, NOM_SOCIETE);
$mail->withAck($id_client, $_POST['sujet'], 'id_bl', $id, $t[1]);
$mail->addDest('contact@depaninfo67.com');
$mail->setLogo($_SESSION['compte_principal']);
$mail->body(nl2br($_POST['texte']));
$mail->addBl($_POST['pdf']);
$mail->addSignature(NOM_SOCIETE, WEB, MAIL_CONTACT, TEL);
$mail->send();



/*$email = $db->SQLFix($_POST['mail']);
$sql = 'INSERT INTO envoie_bl (id_bl, id_staff, timestamp, destinataire, compte_principal) VALUES ("'.$id.'", "'.$_SESSION['id'].'", "'.time().'", "'.$t[1].'", "'.$_SESSION['compte_principal'].'")';
$db->Query($sql);*/

