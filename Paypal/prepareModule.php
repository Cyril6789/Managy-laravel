<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 21/03/2016
 * Time: 19:33
 */

if(empty($_SESSION['compte_principal']) OR empty($_GET['id_module'])){
    header('Location: ../dashboard');
    die();
}





require_once ('../classes/mysql.class.php');
$db = new MySQL();

$id_module= $db->SQLFix($_GET['id_module']);

$sql = 'SELECT mc.nom_propre, mc.nom, mc.prix
        FROM modules_comptes AS mc
        INNER JOIN modules_acces AS ma
        ON (ma.nom = mc.nom)
        AND (ma.compte_principal = "'.$_SESSION['compte_principal'].'")
        WHERE ma.fin_abo = "0" 
        AND mc.id = "'.$id_module.'" 
        AND mc.type="2" 
        AND mc.prix > "0" 
        AND mc.compte_principal="'.$_SESSION['compte_principal'].'" ';
$db->Query($sql);
echo $db->Error();
$row = $db->Row();

if($db->RowCount() != 1)
    die();

if($row->nom)
    $nom_module = $row->nom_propre;
else
    $nom_licence = $row->nom;

$prix = $row->prix;

require_once ('../classes/Paypal.class.php');
require_once ('../constructPage/settings.gen.inc');


$sql = 'SELECT fin_abo FROM comptes_principaux WHERE id="'.$_SESSION['compte_principal'].'" ';
$db->Query($sql);
$row = $db->Row();
$fin_abo = $row->fin_abo;
//echo $fin_abo;

$start = date('Y-m-d', $fin_abo);
$end = date('Y-m-d');
$datetime1 = new DateTime($start);
$datetime2 = new DateTime($end);
$interval = $datetime1->diff($datetime2);
$nbmonth= $interval->format('%y')*12 +$interval->format('%m') + 1; //Retourne le nombre de mois
//echo $nbmonth;


$prix = $nbmonth * $prix;



$products[0]['name'] = 'Module "'.$nom_module.'" ('.$nbmonth.' mois)';
$products[0]['price'] = $prix;
$products[0]['priceTVA'] = $prix;
$products[0]['count'] = 1;

$total = $prix;




$paypal = new Paypal();

$parametres = Array(
    'RETURNURL' => 'https://www.managy.fr/Paypal/processModule.php',
    'CANCELURL' => 'https://www.managy.fr/Paypal/cancelModule.php',

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
    $db->Query('DELETE FROM paiements_modules_en_cours WHERE compte_principal =  "'.$_SESSION['compte_principal'].'" ');

    $db->Query('INSERT INTO paiements_modules_en_cours (token, id_module, compte_principal) VALUES ("'.$response['TOKEN'].'", "'.$id_module.'", "'.$_SESSION['compte_principal'].'")  ');
    echo $db->Error();


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
