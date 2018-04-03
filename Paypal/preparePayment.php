<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 21/03/2016
 * Time: 19:33
 */

if(empty($_SESSION['compte_principal']) OR empty($_GET['mois'])){
    header('Location: ../dashboard');
    die();
}




require_once ('../classes/mysql.class.php');
$db = new MySQL();

$mois = $db->SQLFix($_GET['mois']);


if(!$mois)
    die();

$sql = 'SELECT coeff FROM choix_abo WHERE mois="'.$mois.'" ';
$tab_choix_abo = Array();
$i = 0;
$db->Query($sql);
$row = $db->Row();

$coeff = $row->coeff;

if(!$coeff)
    die();

require_once ('../classes/Paypal.class.php');
require_once ('../constructPage/settings.gen.inc');



$db->Query('SELECT nom, nom_propre, prix FROM modules_comptes WHERE type="2" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
$tab_mods_active = Array();
$tab_mods_not_active = Array();
$i = 0;
$j = 0;
while($row = $db->Row())
{

    if(in_array($row->nom,  $_SESSION['modules']) AND $row->prix > 0)
    {
        $tab_mods_active[$i]['link'] = $row->nom;
        if($row->nom_propre)
            $tab_mods_active[$i]['nom'] = $row->nom_propre;
        else
            $tab_mods_active[$i]['nom'] = $row->nom;
        $tab_mods_active[$i]['prix'] = $row->prix;
        $i++;
    }
}




$total = 0;

$products[0]['name'] = 'Abonnement '.$mois.' mois';
$products[0]['price'] = PRICE_SUBSCRIPTION * $coeff;
$products[0]['priceTVA'] = PRICE_SUBSCRIPTION * $coeff;
$products[0]['count'] = 1;


$base = PRICE_SUBSCRIPTION * $coeff;
$total += PRICE_SUBSCRIPTION * $coeff;

$i = 1;
foreach($tab_mods_active AS $mod)
{
    if($mod['prix'] > 0)
    {
        $products[$i]['name'] = 'Module "'.$mod['nom'].'" '.$mois.' mois';
        $products[$i]['price'] = $mod['prix'] * $coeff;
        $products[$i]['priceTVA'] = $mod['prix'] * $coeff;
        $products[$i]['count'] = 1;

        $total += $mod['prix'] * $coeff;
        $i++;
    }
}


/* NB licences staffs*/
$sql = 'SELECT COUNT(*) AS nb FROM licences_staffs WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND incluse="0"';
$db->Query($sql);
$row = $db->Row();
$nb_licences_suppl = $row->nb;

$sql = 'SELECT prix_staff_suppl FROM comptes_principaux WHERE id = "'.$_SESSION['compte_principal'].'" ';
$db->Query($sql);
$row = $db->Row();
$prix_staff_suppl = $row->prix_staff_suppl;

$products[$i]['name'] = $nb_licences_suppl.' Licences employés '.$mois.' mois';
$products[$i]['price'] = $nb_licences_suppl * $prix_staff_suppl * $coeff;
$products[$i]['priceTVA'] = $nb_licences_suppl * $prix_staff_suppl * $coeff;
$products[$i]['count'] = 1;

$total += $nb_licences_suppl * $prix_staff_suppl * $coeff;


$paypal = new Paypal();

$parametres = Array(
    'RETURNURL' => 'https://www.managy.fr/Paypal/processPayment.php',
    'CANCELURL' => 'https://www.managy.fr/Paypal/cancelPayment.php',

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
    $db->Query('DELETE FROM paiements_en_cours WHERE compte_principal =  "'.$_SESSION['compte_principal'].'" ');

    $db->Query('INSERT INTO paiements_en_cours (token, mois, mois_paye, timestamp, prix_base, prix_total, compte_principal) VALUES ("'.$response['TOKEN'].'", "'.$mois.'", "'.$coeff.'", "'.time().'", "'.$base.'", "'.$total.'", "'.$_SESSION['compte_principal'].'")  ');

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
