<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 21/03/2016
 * Time: 19:33
 */

if(empty($_SESSION['compte_principal']) OR empty($_GET['nb_sms'])){
    header('Location: ../dashboard');
    die();
}

$autorized_pack = Array();
$autorized_pack[] = 500;
$autorized_pack[] = 1000;
$autorized_pack[] = 2500;
$autorized_pack[] = 5000;

if(!in_array($_GET['nb_sms'], $autorized_pack))
    die();



require_once ('../classes/mysql.class.php');
$db = new MySQL();
require_once ('../classes/Paypal.class.php');
require_once ('../constructPage/settings.gen.inc');
require_once ('../classes/DataObject.class.php');


$pack = $db->SQLFix($_GET['nb_sms']);


$packs = new DataObject('packs_sms');
$packs->find($pack, 'qte', false);

if($packs->id)
    $prix = $packs->prix;

else
{
    header('location: ../dashboard');
}

$products[0]['name'] = 'Pack '.$pack.' SMS';
$products[0]['price'] = $prix;
$products[0]['priceTVA'] = $prix;
$products[0]['count'] = 1;

$total = $prix;




$paypal = new Paypal();

$parametres = Array(
    'RETURNURL' => 'https://www.managy.fr/Paypal/processSms.php',
    'CANCELURL' => 'https://www.managy.fr/Paypal/cancelSms.php',

    'PAYMENTREQUEST_0_AMT' => $total,
    'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
    'PAYPENTREQUEST_0_ITEMAMT' => $total


);

foreach($products AS $k => $p)
{
    $parametres['L_PAYMENTREQUEST_0_NAME'.$k] = $p['name'];
    $parametres['L_PAYMENTREQUEST_0_DESC'.$k] = '';
    $parametres['L_PAYMENTREQUEST_0_AMT'.$k] = $p['priceTVA'];
    $parametres['L_PAYMENTREQUEST_0_QTY'.$k] = $p['count'];
}


$response = $paypal->request('SetExpressCheckout', $parametres);



if($response)
{
    $db->Query('DELETE FROM paiements_sms_en_cours WHERE compte_principal =  "'.$_SESSION['compte_principal'].'" ');

    $db->Query('INSERT INTO paiements_sms_en_cours (token, pack, compte_principal) VALUES ("'.$response['TOKEN'].'", "'.$pack.'", "'.$_SESSION['compte_principal'].'")  ');

    $paypal = 'https://www.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token='.$response['TOKEN'];

    header('Location: '.$paypal);

}
else
{
    var_dump($paypal->errors);
    die('Erreur');
}



?>

<a href="<?php echo $paypal;?>">Payer</a>
