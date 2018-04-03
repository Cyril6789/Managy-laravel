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
    $db->Query('SELECT id_module FROM paiements_modules_en_cours WHERE token = "'.$token.'" AND compte_principal = "'.$_SESSION['compte_principal'].'"');
    $r = $db->Row();

    $id_module = $r->id_module;

    $sql = 'SELECT nom, nom_propre FROM modules_comptes WHERE compte_principal="'.$_SESSION['compte_principal'].'" AND id="'.$id_module.'" ';
    $db->Query($sql);
    $row = $db->Row();
    $nom_propre = $row->nom_propre;

    $sql = 'SELECT fin_abo FROM comptes_principaux WHERE id="'.$_SESSION['compte_principal'].'" ';
    $db->Query($sql);
    $row2 = $db->Row();

    $sql = 'UPDATE modules_acces SET fin_abo = "1", date_fin = "'.$row2->fin_abo.'" WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND nom="'.$row->nom.'" AND acces = "1" AND fin_abo = "0" ';
    
    $db->Query($sql);


    $mail = new Maileur('Merci pour votre  achat de Module !');
    $mail->addDest(MAIL_CONTACT);
    $mail->addExpediteur('contact@managy.fr', 'Managy.fr');
    $mail->AddTitle('Achat de module');

    $body = 'Cher utilisateur de Managy.fr,<br /><br />Vous venez d\'acheter le module "'.$nom_propre.'" et nous vous en remercions !<br /><br/>Nous vous enverrons une facture dans les prochains jours.<br /><br />En cas de problème, n\'hestitez pas à nous contacter en répondant à ce mail.<br /><br />Cordialement,<br />Cyril de Managy.fr';

    $mail->body($body);

    $mail->send();


    $mail = new Maileur(NOM_SOCIETE.' module commandé');
    $mail->addDest('contact@depaninfo67.com');
    $mail->addExpediteur('contact@managy.fr', 'Managy.fr');
    $mail->AddTitle('Achat d\'un module pour '.NOM_SOCIETE);
    $body = 'Cyril,<br/>La société <strong>'.NOM_SOCIETE.'</strong> vient d\'acheter le module "'.$nom_propre.'".';
    $mail->body($body);

    $mail->send();


    $token = $db->SQLFix($_GET['token']);
    $db->Query('DELETE FROM paiements_modules_en_cours WHERE token = "'.$token.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ');

    $_SESSION['notify'] = 'OrderModule';
    header('Location: ../modules');

}
else
{

    $_SESSION['notify'] = 'ErrorOrderModule';
    header('Location: ../modules');
}


?>