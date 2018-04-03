<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 10/02/2016
 * Time: 08:34
 */

if(empty($_GET['token']) OR empty($_SESSION['compte_principal'])){
    header('Location: ../dashboard');
    die();
}
include('../classes/Paypal.class.php');
include('../classes/mysql.class.php');
include('../functions/addMonthsToDate.php');

$db = new MySQL();

include('../classes/Maileur.class.php');
include('../constructPage/settings.gen.inc');

$paypal = new Paypal();

$response = $paypal->request('GetExpressCheckoutDetails', Array(
    'TOKEN' => $_GET['token'],
));

//var_dump($response);

$response = $paypal->request('DoExpressCheckoutPayment', array(
    'TOKEN' => $_GET['token'],
    'PAYERID' => $_GET['PayerID'],
    'PAYMENTREQUEST_0_AMT' => $response['AMT'],
    'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
    'PAYMENTACTION' => 'Sale'
));

if($response)
{
    $token = $db->SQLFix($_GET['token']);
    $db->Query('SELECT id_licence FROM paiements_licences_en_cours WHERE token = "'.$token.'" AND compte_principal = "'.$_SESSION['compte_principal'].'"');
    $r = $db->Row();

    $id_licence = $r->id_licence;

    $sql = 'SELECT fin_abo FROM comptes_principaux WHERE id="'.$_SESSION['compte_principal'].'" ';
    $db->Query($sql);
    $row = $db->Row();
    $fin_abo = $row->fin_abo;


    $sql = 'UPDATE licences_staffs SET date_fin = "'.$fin_abo.'" WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND id="'.$id_licence.'" AND incluse = "0" AND date_fin = "0" ';
    ;

    $db->Query($sql);


    $mail = new Maileur('Merci pour votre  achat de licence !');
    $mail->addDest(MAIL_CONTACT);
    $mail->addExpediteur('contact@managy.fr', 'Managy.fr');
    $mail->AddTitle('Achat de licence');

    $body = 'Cher utilisateur de Managy.fr,<br /><br />Vous venez d\'acheter une licence "technicien" et nous vous en remercions !<br /><br/>Nous vous enverrons une facture dans les prochains jours.<br /><br />En cas de problème, n\'hestitez pas à nous contacter en répondant à ce mail.<br /><br />Cordialement,<br />Cyril de Managy.fr';

    $mail->body($body);

    $mail->send();


    $mail = new Maileur(NOM_SOCIETE.' licence commandée');
    $mail->addDest('contact@depaninfo67.com');
    $mail->addExpediteur('contact@managy.fr', 'Managy.fr');
    $mail->AddTitle('Achat d\'une licence pour '.NOM_SOCIETE);
    $body = 'Cyril,<br/>La société <strong>'.NOM_SOCIETE.'</strong> vient d\'acheter licence "technicien".';
    $mail->body($body);

    $mail->send();

    $token = $db->SQLFix($_GET['token']);
    $db->Query('DELETE FROM paiements_liences_en_cours WHERE token = "'.$token.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ');

    $_SESSION['notify'] = 'OrderLicence';
    header('Location: ../staffs');

}
else
{

    $_SESSION['notify'] = 'ErrorOrderLicence';
    header('Location: ../staffs');
}


?>