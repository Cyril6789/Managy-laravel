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
    $db->Query('SELECT mois, mois_paye, prix_base, prix_total FROM paiements_en_cours WHERE token = "'.$token.'" AND compte_principal = "'.$_SESSION['compte_principal'].'"');
    $r = $db->Row();

    $mois_add = $r->mois;
    $mois_paye = $r->mois_paye;
    $prix_base = $r->prix_base;
    $prix_total = $r->prix_total;


    $db->Query('SELECT fin_abo FROM comptes_principaux WHERE id = "'.$_SESSION['compte_principal'].'" ');
    $r = $db->Row();

    $date_actuelle = $r->fin_abo;

    if($date_actuelle)
    {
        $nouvelle_date =  addMonthToDate($date_actuelle, $mois_add);
    }
    else
    {
        $nouvelle_date = addMonthToDate(time(), $mois_add);
    }

    $sql = 'UPDATE comptes_principaux SET fin_abo = "'.$nouvelle_date.'" WHERE id = "'.$_SESSION['compte_principal'].'" ';
    

    $db->Query($sql);

    $sql = 'UPDATE licences_staffs SET date_fin = "'.$nouvelle_date.'" WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND incluse = "0" ';
    
    $db->Query($sql);

    $sql = 'UPDATE modules_acces SET fin_abo = "1", date_fin="'.$nouvelle_date.'" WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND acces = "1" ';
    $db->Query($sql);

    $sql = 'UPDATE modules_acces SET fin_abo = "0" WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND acces = "0" ';
    $db->Query($sql);


    $mail = new Maileur('Merci pour votre achat !');
    $mail->addDest(MAIL_CONTACT);
    $mail->addExpediteur('contact@managy.fr', 'Managy.fr');
    $mail->AddTitle('Prolongement de votre abonnement');

    $body = 'Cher utilisateur de Managy.fr,<br /><br />Vous venez de prolonger votre abonnement Managy.fr de <strong>'.$mois_add.'</strong> mois et nous vous en remercions !<br /><br/>Nous vous enverrons une facture dans les prochains jours reprenant le détail de votre transaction.<br /><br />En cas de problème, n\'hestitez pas à nous contacter en répondant à ce mail.<br /><br />Cordialement,<br />Cyril de Managy.fr';

    $mail->body($body);

    $mail->send();


    $mail = new Maileur(NOM_SOCIETE.' : prolongement d\'abonnement');
    $mail->addDest('contact@depaninfo67.com');
    $mail->addExpediteur('contact@managy.fr', 'Managy.fr');
    $mail->AddTitle('Abonnement prolongé de '.$mois_add.' mois : '.NOM_SOCIETE);
    $body = 'Cyril,<br/>La société <strong>'.NOM_SOCIETE.'</strong> vient de prolonger son abonnement de <strong>'.$mois_add.'</strong> mois.';
    $mail->body($body);

    $mail->send();

    $token = $db->SQLFix($_GET['token']);
    $db->Query('DELETE FROM paiements_en_cours WHERE token = "'.$token.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ');


    $sql = 'INSERT INTO prolongements (compte_principal, timestamp, fin_abo, mois_prolonge, mois_paye, prix_base, prix_total, transactionid) VALUES ("'.$_SESSION['compte_principal'].'", "'.time().'", "'.$nouvelle_date.'", "'.$mois_add.'", "'.$mois_paye.'", "'.$prix_base.'", "'.$prix_total.'", "Transaction PayPal N°'.$response['TRANSACTIONID'].'" ) ';
    $db->Query($sql);

    $_SESSION['notify'] = 'OrderAbo';
    header('Location: ../dashboard');

}
else
{

    $_SESSION['notify'] = 'ErrorOrderAbo';
    header('Location: ../dashboard');
}


?>