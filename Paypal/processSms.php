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
$db = new MySQL();

include('../functions/addMonthsToDate.php');

include('../classes/Maileur.class.php');
include('../constructPage/settings.gen.inc');


$paypal = new Paypal();



$response = $paypal->request('GetExpressCheckoutDetails', Array(
    'TOKEN' => $_GET['token'],
));



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
    $db->Query('SELECT pack FROM paiements_sms_en_cours WHERE token = "'.$token.'" AND compte_principal = "'.$_SESSION['compte_principal'].'"');
    $r = $db->Row();

    $pack_add = $r->pack;

    $sql = 'SELECT COUNT(*) AS nb FROM stocks_sms WHERE compte_principal="'.$_SESSION['compte_principal'].'" ';
    $db->Query($sql);

    $r = $db->Row();

    if($r->nb == 0)
        $sql = 'INSERT INTO stocks_sms (conso, commande, compte_principal) VALUES ("0", "'.$pack_add.'", "'.$_SESSION['compte_principal'].'")';
    else
        $sql = 'UPDATE stocks_sms SET commande = commande  + "'.$pack_add.'" WHERE compte_principal = "'.$_SESSION['compte_principal'].'" ';

    $db->Query($sql);
    echo $db->Error();

    $sql = 'DELETE FROM paiements_sms_en_cours WHERE compte_principal = "'.$_SESSION['compte_principal'].'"';
    $db->Query($sql);


    $mail = new Maileur('Merci pour votre  achat de SMS !');
    $mail->addDest(MAIL_CONTACT);
    $mail->addExpediteur('contact@managy.fr', 'Managy.fr');
    $mail->AddTitle('Achat de <strong>'.$pack_add.'</strong> SMS');

    $body = 'Cher utilisateur de Managy.fr,<br /><br />Vous venez d\'acheter un pack de <strong>'.$pack_add.'</strong> SMS et nous vous en remercions !<br /><br/>Votre compte Managy.fr a été crédité et nous vous enverrons une facture dans les prochains jours.<br /><br />En cas de problème, n\'hestitez pas à nous contacter en répondant à ce mail.<br /><br />Cordialement,<br />Cyril de Managy.fr';

    $mail->body($body);

    $mail->send();


    $mail = new Maileur(NOM_SOCIETE.' a acheté '.$pack_add.' SMS');
    $mail->addDest('contact@depaninfo67.com');
    $mail->addExpediteur('contact@managy.fr', 'Managy.fr');
    $mail->AddTitle('Achat de <strong>'.$pack_add.'</strong> SMS de '.NOM_SOCIETE);
    $body = 'Cyril,<br/>La société <strong>'.NOM_SOCIETE.'</strong> vient d\'acheter un pack de <strong>'.$pack_add.'</strong> SMS';
    $mail->body($body);

    $mail->send();


    $_SESSION['notify'] = 'OrderSms';
    header('Location: ../dashboard');

}
else
{
    $sql = 'DELETE FROM paiements_sms_en_cours WHERE compte_principal = "'.$_SESSION['compte_principal'].'"';
    $db->Query($sql);
    $_SESSION['notify'] = 'ErrorOrderSms';
    header('Location: ../dashboard');
}

$sql = 'DELETE FROM paiements_sms_en_cours WHERE compte_principal = "'.$_SESSION['compte_principal'].'"';
$db->Query($sql);

?>