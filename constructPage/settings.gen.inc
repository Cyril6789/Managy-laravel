<?php session_start();

$db->Query('SELECT * FROM comptes_principaux WHERE id="'.$_SESSION['compte_principal'].'" ');
$row = $db->Row();
define(MAIL_CONTACT, $row->mail_contact);
define(NOM_SOCIETE, $row->nom_societe);
define(LOGO, $row->logo);
define(WEB, $row->web);
define(TEL, $row->tel);
define(EXP_SMS, $row->expediteur_sms);
define(SIGNATURE_SMS, $row->signature_sms);

$one_year = time() + (60 * 60 * 24 * 365);
define(END_SUBSCRIPTION, $row->fin_abo);
define(PRICE_SUBSCRIPTION, $row->prix_abo);
define(ADRESS_SOCIETY, $row->adresse);
define(CGU_VERSION, $row->id_cgu);
define(DAYS_BEFORE_RESTRICTION, 15);
$_SESSION['cal'] = $row->cal;

if($_GET['template'] != '' OR $_SESSION['template'] != '')
{
    if($_GET['template'] != '')
    {
        $_SESSION['template'] = $_GET['template'];
        $theme = $_SESSION['template'];
    }
    elseif($_SESSION['template'] != '')
    {
        $theme= $_SESSION['template'];
    }
    else
    {
        $theme = $row->template;
        //$theme = 'mango';
    }
}
else
    $theme = $row->template; //"melon";

//echo 'coucou'.$theme;

if(!is_dir('./templates/'.$theme) AND !is_dir('../templates/'.$theme))
    $theme = 'v4';


define(TEMPLATE_NAME, $theme);
$_SESSION['template'] = $theme;

if($_SESSION['compte_principal'] == 2)
    define('FREE_ACCESS', false); //Défini la gratuité du site
else
    define('FREE_ACCESS', true); //Défini la gratuité du site


define(GOOGLE_API_KEY, 'AIzaSyCsOlv-JNJADae5UPtoZLeOlsjLGoBDoiU');
 

$_SESSION['tab_lang'] = '';
$path_flags = './templates/'.TEMPLATE_NAME.'/img/flags';
if(is_dir($path_flags))
{
    $tab_flag = scandir($path_flags);

    foreach ($tab_flag as $one_flag) {
        if ($one_flag != '.' AND $one_flag != '..' AND !is_dir($path_flags . '/' . $one_flag)) {
            $_SESSION['tab_lang'][] = str_replace('.png', '', $one_flag);
        }
    }
}

//$_SESSION['tab_lang'] = Array('EN', 'FR', 'DE', 'IT');

$new_lang = $_GET['hl'];

global $db;


//Changement de compte 
if(isset($_GET['compte']) AND !empty($_GET['compte']) AND is_numeric($_GET['compte']) AND $_SESSION['id'] == '1')
{
    $compte = $db->SQLFix($_GET['compte']);
    $db->Query('SELECT COUNT(*) AS nb FROM comptes_principaux WHERE id="'.$compte.'" ');
    $row = $db->Row();
    if($row->nb)
        $_SESSION['compte_principal'] = $compte;
}

//Modules activables
$_SESSION['modules'] = '';

$_SESSION['modules'] = Array();

$db->Query('SELECT nom FROM modules_acces WHERE compte_principal="'.$_SESSION['compte_principal'].'" AND acces = "1" ');
while($row = $db->Row())
{
    $_SESSION['modules'][] = $row->nom;
}


if($new_lang == '')
{
    if($_SESSION['hl'] != '')
    {
        define(HL, $_SESSION['hl']);
    }
    else 
    {
        define(HL, 'fr');
        $_SESSION['hl'] = 'fr';
    }
   
}
else
{
    if(in_array(strtoupper($new_lang), $_SESSION['tab_lang']))
    {
        define(HL, strtolower($new_lang));
        $_SESSION['hl'] = strtolower($new_lang);
    
    }
    else
    {
        define(HL, 'fr');
        $_SESSION['hl'] = 'fr';
    }
}

/*
 * Clé de CRYPTAGE NE PAS SUPPRIMER OU MODIFIER !
 */
define(CRYPT_KEY, 'f5h6zed2c0b0e8s5q6k2h8j5u41rtfh125y1jt51ftgfhghfghhhh5fghfgh7855');


//Ajout d'une action dans la table
$db->Query('UPDATE staffs SET last_action = "'.time().'" WHERE id = "'.$_SESSION['id'].'" ');


?>
