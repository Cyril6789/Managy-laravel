<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 18/08/2016
 * Time: 17:57
 */
//die();
require('./functions/crypt_pass.func.php');
require('./classes/mysql.class.php');
require('./classes/reCaptcha.class.php');
require('./functions/addMonthsToDate.php');
require('./classes/Maileur.class.php');
require_once './classes/PhpMailer/PHPMailerAutoload.php';



function aleat($longueur=10)
{
    $lettres = Array ('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');

    $string = '';
    $chiffre = false;
    for($i=0; $i<$longueur; $i++)
    {
        if($chiffre)
        {
            $string .= rand(0, 9);
            $chiffre = false;
        }
        else{
            $string .= $lettres[rand(0, count($lettres)-1)];
            $chiffre = true;
        }
    }
    return $string;
}

/*print_r($_POST);
echo $_POST['g-recaptcha-response'];*/
$recaptcha = new reCaptcha();
$erreur_nb = 0;
if(!$recaptcha->checkHuman($_POST['g-recaptcha-response']))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur captcha';
}
if(empty($_POST['mail_societe']))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur mail';
}
if(empty($_POST['societe']))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur nom societe';
}
if(empty($_POST['tel']))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur telephone';
}
if(empty($_POST['adresse']))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur adresse';
}
if(empty($_POST['cp']))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur cp';
}
if(empty($_POST['ville']))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur ville';
}
if(empty($_POST['siret']))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur siret ';
}
if(empty($_POST['ape']))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur ape';
}
if(empty($_POST['nom']))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur nom';
}
if(empty($_POST['prenom']))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur prenom';
}
if(empty($_POST['pass1']))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur pass1';
}
if(empty($_POST['pass2']))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur pass2';
}
if($_POST['pass1'] != $_POST['pass2'])
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur pass';
}
if(!filter_var($_POST['mail_societe'], FILTER_VALIDATE_EMAIL))
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur mail invalide';
}



$db = new MySQL();

$nombre=0;
$sql = 'SELECT count(*) AS nb FROM comptes_principaux WHERE mail_contact="'.$db->SQLFix($_POST['mail_societe']).'" ';
$db->Query($sql);
$row = $db->Row();
$nombre = $row->nb;
$sql = 'SELECT count(*) AS nb FROM staffs WHERE pseudo="'.$db->SQLFix($_POST['mail_societe']).'" OR mail="'.$db->SQLFix($_POST['mail_societe']).'"';
$db->Query($sql);
$row = $db->Row();
$nombre += $row->nb;

if($nombre)
{
    $erreur_nb++; $_SESSION['erreur'][$erreur_nb]= 'erreur adresse mail déja utilisée';
}



if($erreur_nb)
{
    $_SESSION['nb_erreur'] = $erreur_nb;
    //header('location: ./index.php');
    //die();
}


if($_SESSION['nb_erreur']) {
    for ($i = 1; $i <= $_SESSION['nb_erreur']; $i++)
        echo $_SESSION['erreur'][$i] . ' <br />';

    $_SESSION['nb_erreur'] = 0;
    $_SESSION['erreur'] = '';

    die();
}

$adresse = $db->SQLFix($_POST['adresse']).' '.$db->SQLFix($_POST['cp']).' '.$db->SQLFix($_POST['ville']);


$sql = 'INSERT INTO comptes_principaux 
        (mail_contact, nom_societe, web, tel, expediteur_sms, signature_sms, adresse, siret, ape, cal, fin_abo, prix_abo, template)
        VALUES ("'.$db->SQLFix($_POST['mail_societe']).'", "'.$db->SQLFix($_POST['societe']).'", "'.$db->SQLFix($_POST['web']).'", "'.$db->SQLFix($_POST['tel']).'", "'.$db->SQLFix($_POST['societe']).'", "'.$db->SQLFix($_POST['societe']).'", "'.$adresse.'", "'.$db->SQLFix($_POST['siret']).'", "'.$db->SQLFix($_POST['ape']).'", "'.aleat().'", "'.addMonthToDate(time(), 1).'", "25", "melon")
        ';

/*
$db->Query($sql);
echo $db->Error();

$lastidc = $db->GetLastInsertID();
*/

$sql = 'SELECT * FROM modules_comptes_types';
/*
$db->Query($sql);
while($row = $db->Row())
{
    $db2 = new MySQL();
    $sql = 'INSERT INTO modules_comptes (nom, nom_propre, alias, prix, type, compte_principal) VALUES ("'.$row->nom.'", "'.$row->nom_propre.'", "'.$row->alias.'", "'.$row->prix.'", "'.$row->type.'", "'.$lastidc.'")';
    $db2->Query($sql);
    if($db2->Error())
    {
        echo $db2->Error();
        die();
    }
    $db2->Close();
}
*/

$sql = 'INSERT INTO staffs
        (pseudo, pass, prenom, nom, mail, compte_principal, status_chat, visible_chat, connexion_by_mail, gerant)
        VALUES
        ("'.$db->SQLFix($_POST['mail_societe']).'", "'.crypt_pass($db->SQLFix($_POST['pass1'])).'", "'.$db->SQLFix($_POST['prenom']).'", "'.$db->SQLFix($_POST['nom']).'", "'.$db->SQLFix($_POST['mail_societe']).'", "'.$lastidc.'", "status", "closed", "1", "1");
        
';
/*
$db->Query($sql);
echo $db->Error();*/

$lastids = $db->GetLastInsertID();

$sql = 'SELECT * FROM rights_staff_types';
/*
$db->Query($sql);
while($row = $db->Row())
{
    $db2 = new MySQL();
    //$sql = 'INSERT INTO rights_staff (id_staff, modul, num) VALUES ("'.$lastids.'", "'.$row->modul.'", "'.$row->num.'")';
    //$db2->Query($sql);
    if($db2->Error())
    {
        echo $db2->Error();
        die();
    }
    $db2->Close();
}*/

$db->Close();


$mail_client = new Maileur('Bienvenue sur Managy !');
$mail_client->addDest($_POST['mail_societe']);
$mail_client->addExpediteur('contact@managy.fr', 'Managy.fr');
$mail_client->AddTitle('Bienvenue '.$_POST['prenom']);
$mail_client->setLogo('managy');
$texte = 'Bonjour '.$_POST['prenom'].' !<br />
Votre compte vient d\'etre créé sur managy.fr<br /><br />
Pour rappel vos identifiants de connexion sont les suivants : <br /><br />
Identifiant : '.$_POST['mail_societe'].'<br />
Mot de passe : <strong>'.$_POST['pass1'].'</strong><br /><br />
<strong>Ne communiquez jamais ces identifiants à qui que ce soit !</strong><br /><br />
N\'hésitez-pas à nous contacter par mail à contact@managy.fr ou par téléphone au 06.87.47.00.91.<br />
À bientôt sur Managy.fr !<br /><br />
Cyril Heilmann - Dirigeant';
$mail_client->body($texte);

$mail_client->send();

$mail = new Maileur('Nouveau client Managy !!');
$mail->addDest('contact@managy.fr');
$mail->addExpediteur('noreply@managy.fr', 'Managy.fr');
$mail->AddTitle('Societe '.$_POST['societe']);
$mail->setLogo('managy');
$texte = 'Bonjour Cyril !<br />
Une nouvelle société vient de s\'inscrire sur managy !<br /><br />
Société : <strong>'.$_POST['societe'].'</strong><br /><br />';
$mail->body($texte);

$mail->send();


header('location: ./login.php');
