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
    header('location: ./index.php');
    die();
}


if(!empty($_SESSION['referer']))
    $referer = $db->SQLFix($_SESSION['referer']);
else
    $referer = $db->SQLFix($_COOKIE['referer']);

$sql = 'INSERT INTO comptes_principaux 
        (mail_contact, nom_societe, expediteur_sms, signature_sms, cal, fin_abo, prix_abo, template, date_inscription, referer)
        VALUES ("'.$db->SQLFix($_POST['mail_societe']).'", "'.$db->SQLFix($_POST['societe']).'", "'.$db->SQLFix($_POST['societe']).'", "'.$db->SQLFix($_POST['societe']).'",  "'.aleat().'", "'.addMonthToDate(time(), 1).'", "25", "v4", "'.time().'", "'.$referer.'")
        ';


$db->Query($sql);
echo $db->Error();

$lastidc = $db->GetLastInsertID();


$sql = 'SELECT * FROM modules_comptes_types';

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

$sql = 'SELECT * FROM materiels_types';

$db->Query($sql);
while($row = $db->Row())
{
    $db2 = new MySQL();
    $sql = 'INSERT INTO materiels (nom, compte_principal) VALUES ("'.$row->nom.'", "'.$lastidc.'")';
    $db2->Query($sql);
    if($db2->Error())
    {
        echo $db2->Error();
        die();
    }
    $db2->Close();
}

$sql = 'SELECT * FROM se_types';

$db->Query($sql);
while($row = $db->Row())
{
    $db2 = new MySQL();
    $sql = 'INSERT INTO se (nom, compte_principal) VALUES ("'.$row->nom.'", "'.$lastidc.'")';
    $db2->Query($sql);
    if($db2->Error())
    {
        echo $db2->Error();
        die();
    }
    $db2->Close();
}

$sql = 'SELECT * FROM antivirus_types';
$db->Query($sql);
while($row = $db->Row())
{
    $db2 = new MySQL();
    $sql = 'INSERT INTO antivirus (nom, compte_principal) VALUES ("'.$row->nom.'", "'.$lastidc.'")';
    $db2->Query($sql);
    if($db2->Error())
    {
        echo $db2->Error();
        die();
    }
    $db2->Close();
}

$sql = 'SELECT * FROM statuts_types';
$db->Query($sql);
while($row = $db->Row())
{
    $db2 = new MySQL();
    $sql = 'INSERT INTO statuts (nom, intervention_occupee, compte_principal) VALUES ("'.$row->nom.'", "'.$row->intervention_occupee.'", "'.$lastidc.'")';
    $db2->Query($sql);
    if($db2->Error())
    {
        echo $db2->Error();
        die();
    }
    $db2->Close();
}

$sql = 'SELECT * FROM prestations_types';
$db->Query($sql);
while($row = $db->Row())
{
    $db2 = new MySQL();
    $sql = 'INSERT INTO prestations (designation, duree_defaut, compte_principal) VALUES ("'.$row->designation.'", "'.$row->duree_defaut.'", "'.$lastidc.'")';
    $db2->Query($sql);
    if($db2->Error())
    {
        echo $db2->Error();
        die();
    }
    $db2->Close();
}

$sql = 'INSERT INTO staffs
        (pseudo, pass, prenom, nom, mail, compte_principal, status_chat, visible_chat, connexion_by_mail, gerant, cal)
        VALUES
        ("'.$db->SQLFix($_POST['mail_societe']).'", "'.crypt_pass($db->SQLFix($_POST['pass1'])).'", "'.$db->SQLFix($_POST['prenom']).'", "'.$db->SQLFix($_POST['nom']).'", "'.$db->SQLFix($_POST['mail_societe']).'", "'.$lastidc.'", "status", "closed", "1", "1", "'.aleat().'");
        
';

$db->Query($sql);
echo $db->Error();

$lastids = $db->GetLastInsertID();

$sql = 'SELECT * FROM rights_staff_types';

$db->Query($sql);
while($row = $db->Row())
{
    $db2 = new MySQL();
    $sql = 'INSERT INTO rights_staff (id_staff, modul, num) VALUES ("'.$lastids.'", "'.$row->modul.'", "'.$row->num.'")';
    $db2->Query($sql);
    if($db2->Error())
    {
        echo $db2->Error();
        die();
    }
    $db2->Close();
}

$db->Close();


$mail_client = new Maileur('Bienvenue sur Managy !');
$mail_client->addDest($_POST['mail_societe']);
$mail_client->addExpediteur('contact@managy.fr', 'Managy.fr');
$mail_client->AddTitle('Bienvenue '.$_POST['prenom']);
$mail_client->setLogo('managy');
$texte = 'Bonjour '.$_POST['prenmo'].' !<br />
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
$mail->addDest('valentin.b@managy.fr');
$mail->addExpediteur('noreply@managy.fr', 'Managy.fr');
$mail->AddTitle('Societe '.$_POST['societe']);
$mail->setLogo('managy');
$texte = 'Bonjour !<br />
Une nouvelle société vient de s\'inscrire sur managy !<br /><br />
Société : <strong>'.$_POST['societe'].'</strong><br /><br />';
$mail->body($texte);

$mail->send();



$mail_inscription = new Maileur('Féliciation ! - Managy.fr');
$mail_inscription->addExpediteur('valentin.b@managy.fr', 'Valentin - Managy');
$mail_inscription->AddTitle('Bienvenue et félicitations ! ');


$corps = '
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8"> <!-- utf-8 works for most cases -->
    <meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn\'t be necessary -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Use the latest (edge) version of IE rendering engine -->
    <meta name="x-apple-disable-message-reformatting">  <!-- Disable auto-scale in iOS 10 Mail entirely -->
    <title></title> <!-- The title tag shows in email notifications, like Android 4.4. -->

    <!-- Web Font / @font-face : BEGIN -->
	<!-- NOTE: If web fonts are not required, lines 10 - 27 can be safely removed. -->
    
    <!-- Desktop Outlook chokes on web font references and defaults to Times New Roman, so we force a safe fallback font. -->
    <!--[if mso]>
        <style>
            * {
                font-family: sans-serif !important;
            }
        </style>
    <![endif]-->
    
    <!-- All other clients get the webfont reference; some will render the font and others will silently fail to the fallbacks. More on that here: http://stylecampaign.com/blog/2015/02/webfont-support-in-email/ -->
    <!--[if !mso]><!-->
        <!-- insert web font reference, eg: <link href=\'https://fonts.googleapis.com/css?family=Roboto:400,700\' rel=\'stylesheet\' type=\'text/css\'> -->
    <!--<![endif]-->

    <!-- Web Font / @font-face : END -->
    
	<!-- CSS Reset -->
    <style>

        /* What it does: Remove spaces around the email design added by some email clients. */
        /* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
        html,
        body {
            margin: 0 auto !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
        }
        
        /* What it does: Stops email clients resizing small text. */
        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        
        /* What is does: Centers email on Android 4.4 */
        div[style*="margin: 16px 0"] {
            margin:0 !important;
        }
        
        /* What it does: Stops Outlook from adding extra spacing to tables. */
        table,
        td {
            mso-table-lspace: 0pt !important;
            mso-table-rspace: 0pt !important;
        }
                
        /* What it does: Fixes webkit padding issue. Fix for Yahoo mail table alignment bug. Applies table-layout to the first 2 tables then removes for anything nested deeper. */
        table {
            border-spacing: 0 !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin: 0 auto !important;
        }
        table table table {
            table-layout: auto; 
        }
        
        /* What it does: Uses a better rendering method when resizing images in IE. */
        img {
            -ms-interpolation-mode:bicubic;
        }
        
        /* What it does: A work-around for iOS meddling in triggered links. */
        .mobile-link--footer a,
        a[x-apple-data-detectors] {
            color:inherit !important;
            text-decoration: underline !important;
        }

        /* What it does: Prevents underlining the button text in Windows 10 */
        .button-link {
            text-decoration: none !important;
        }
      
        /* What it does: Removes right gutter in Gmail iOS app: https://github.com/TedGoas/Cerberus/issues/89  */
        /* Create one of these media queries for each additional viewport size you\'d like to fix */
        /* Thanks to Eric Lepetit @ericlepetitsf) for help troubleshooting */
        @media only screen and (min-device-width: 375px) and (max-device-width: 413px) { /* iPhone 6 and 6+ */
            .email-container {
                min-width: 375px !important;
            }
        }
    
    </style>
    
    <!-- Progressive Enhancements -->
    <style>
        
        /* What it does: Hover styles for buttons */
        .button-td,
        .button-a {
            transition: all 100ms ease-in;
        }
        .button-td:hover,
        .button-a:hover {
            background: #29b1fd !important;
            border-color: #29b1fd !important;
        }

    </style>

</head>
<body width="100%" bgcolor="#222222" style="margin: 0; mso-line-height-rule: exactly;">
    <center style="width: 100%; background: #ededed;">

        <!-- Visually Hidden Preheader Text : BEGIN -->
        <div style="display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;font-family: sans-serif;">
            Félicitations ! vous n\'avez plus qu\'à prendre en main Managy pour voir exploser votre créativité ! 
        </div>
        <!-- Visually Hidden Preheader Text : END -->

        <!--    
            Set the email width. Defined in two places:
            1. max-width for all clients except Desktop Windows Outlook, allowing the email to squish on narrow but never go wider than 600px.
            2. MSO tags for Desktop Windows Outlook enforce a 600px width.
        -->
        <div style="max-width: 600px; margin: auto;" class="email-container">
            <!--[if mso]>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" align="center">
            <tr>
            <td>
            <![endif]-->

            <!-- Email Header : BEGIN -->
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px;">
                <tr>
                    <td style="padding: 20px 0; text-align: center">
                        <a href="https://www.managy.fr"><img src="http://mailing.managy.fr/Managy-Phase-1/elements/logo.png" width="200" height="50" alt="Logo Managy" border="0" style="height: auto; background: #29b1fd; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;"></a>
                    </td>
                </tr>
            </table>
            <!-- Email Header : END -->
            
            <!-- Email Body : BEGIN -->
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px;">
                
                <!-- Hero Image, Flush : BEGIN -->
                <tr>
                    <td bgcolor="#ffffff">
                        <a href="https://www.managy.fr"><img src="http://mailing.managy.fr/Managy-Phase-1/elements/Super-1.jpg" width="600" height="" alt="Comment ranger votre bureau et vous faciliter la vie ?" border="0" align="center" style="width: 100%; max-width: 600px; height: auto; background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;"></a>
                    </td>
                </tr>
                <!-- Hero Image, Flush : END -->

                <!-- 1 Column Text + Button : BEGIN -->
                <tr>
                    <td bgcolor="#ffffff">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                            <tr>
                                <td style="padding: 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
                                    Bonjour,
                                    <br><br>
                                    <strong style="font-size:18px; color:#29b1fd;font-weight:bold;">Bienvenue et félicitations !</strong> 
                                    <br>
                                    Vous venez de faire un pas incroyable vers la simplification de votre activité. Vous vous rendrez vite compte que Managy est un outil exceptionnel.                                   
                                    <br><br>
                                    <strong style="font-size:18px; color:#29b1fd;font-weight:bold;">Par où commencer ?</strong>
                                    <br>
                                    Bien que développé pour répondre à votre intuition, Managy peut poser quelques questions de démarrage. Pour ne pas  perdre un instant, nous avons créé une foire aux questions. Nous la complétons chaque jour pour vous aider à prendre en main Managy. <br><br>
                                    <!-- Button : Begin -->
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: auto;">
                                        <tr>
                                            <td style="border-radius: 3px; background: #29b1fd; text-align: center;" class="button-td">
                                                <a href="http://faq.managy.fr/" style="background: #fe9600; border: 15px solid #fe9600; font-family: sans-serif; font-size: 13px; line-height: 1.1; text-align: center; text-decoration: none; display: block; border-radius: 3px; font-weight: bold;" class="button-a">
                                                    <span style="color:#ffffff;" class="button-link">&nbsp;&nbsp;&nbsp;&nbsp;La F.A.Q. c\'est par là !&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- Button : END -->
                                    <br>
                                    <strong style="font-size:18px; color:#29b1fd;font-weight:bold;">Je n\'ai pas trouvé de réponse...</strong><br>
                                    Alors comment poursuivre votre quête ? Nous sommes à vos côtés par le chat intégré, par mail ou par téléphone pour vous guider. Quelques soient vos questions, oubliez les mots "stupide", "bête" ou "c**" ! Au contraire : nous souhaitons répondre à vos besoins et améliorer constamment Managy. Nous sommes à votre disposition pour répondre à vos interrogations.
                                    <br><br>
                                    
                                    <strong style="font-size:18px; color:#29b1fd;font-weight:bold;">En route pour la productivité !</strong>
                                    <br>
                                    Sans plus tarder partenaire, embarquez dès maintenant avec Managy et ne perdez plus une seconde !<br><br>
                                    <!-- Button : Begin -->
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: auto;">
                                        <tr>
                                            <td style="border-radius: 3px; background: #29b1fd; text-align: center;" class="button-td">
                                                <a href="https://www.managy.fr/" style="background: #fe9600; border: 15px solid #fe9600; font-family: sans-serif; font-size: 13px; line-height: 1.1; text-align: center; text-decoration: none; display: block; border-radius: 3px; font-weight: bold;" class="button-a">
                                                    <span style="color:#ffffff;" class="button-link">&nbsp;&nbsp;&nbsp;&nbsp;Accrochez-vous, ça déménage !&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- Button : END -->
                                    <br>
                                    Au plaisir de vous accompagner,<br>
                                    Je suis à votre disposition,<br>
                                    Cordialement,
                                    <br><br>
                                    Valentin - Managy
                                    <br>--<br>
                                    valentin.b@managy.fr<br>
                                    06 85 39 35 43
                                </td>
                                </tr>
                        </table>
                    </td>
                </tr>
                <!-- 1 Column Text + Button : END -->
               
                <!-- 2 Even Columns : BEGIN 
                <tr>
                    <td bgcolor="#ffffff" align="center" height="100%" valign="top" width="100%" style="padding-bottom: 40px">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" width="100%" style="max-width:560px;">
                            <tr>
                                <td align="center" valign="top" width="50%">
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="font-size: 14px;text-align: left;">
                                        <tr>
                                            <td style="text-align: center;font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555; padding: 10px 10px 0;" class="stack-column-center">
                                                <strong style="font-size:18px;color:#29b1fd;font-weight:bold;">Solution responsive</strong><br><br>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center; padding: 0 10px;">
                                                <img src="http://mailing.managy.fr/Managy-Phase-1/elements/oeil-2.jpg" width="200" height="" alt="N\'hésitez plus à devenir mobile" border="0" align="center" style="width: 100%; max-width: 200px; background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555; padding: 10px 10px 0;" class="stack-column-center">
                                                Peu importe où vous êtes, gardez un oeil sur vos interventions grâce à <strong style="color:#29b1fd;font-weight:bold;">votre smartphone, votre tablette ou votre ordinateur.</strong>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td align="center" valign="top" width="50%">
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="font-size: 14px;text-align: left;">
                                        <tr>
                                            <td style="text-align: center; padding: 0 10px;">
                                                <img src="http://mailing.managy.fr/Managy-Phase-1/elements/client-2.jpg" width="200" height="" alt="Vos clients n\'en seront que plus satisfaits" border="0" align="center" style="width: 100%; max-width: 200px; background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555; padding: 10px 10px 0;" class="stack-column-center">
                                                Prévenez vos clients dès la fin de l\'intervention.<strong style="color:#29b1fd;font-weight:bold;"> Envoyez un mail ou un sms préformaté</strong> directement depuis Managy. 
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Two Even Columns : END -->

                <!-- Clear Spacer : BEGIN
                <tr>
                    <td height="40" style="font-size: 0; line-height: 0;">
                        &nbsp;
                    </td>
                </tr>
                <!-- Clear Spacer : END -->

                <!-- 1 Column Text + Button : BEGIN
                <tr>
                    <td bgcolor="#ffffff">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                            <tr>
                                <td style="padding: 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
                                    Pour ne plus perdre de temps et assurer le suivi de vos intervention, utilisez Managy, <strong style="color:#29b1fd;font-weight:bold;">la solution clé en main.</strong>
                                    <br><br>
                                    Je vous remercie par avance de l’attention que vous porterez à ce mail et je reste à votre disposition,
                                    <br><br>
                                    Valentin - Managy
                                    <br>--<br>
                                    valentin.b@managy.fr<br>
                                    06 85 39 35 43
                                </td>
                                </tr>
                        </table>
                    </td>
                </tr>
                <!-- 1 Column Text + Button : END -->

            </table>
            <!-- Email Body : END -->
          
            <!-- Email Footer : BEGIN -->
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px;">
                <tr>
                    <td style="padding: 40px 10px;width: 100%;font-size: 12px; font-family: sans-serif; line-height:18px; text-align: center; color: #888888;">
                        <webversion><a style="color:#cccccc; text-decoration:underline; font-weight: bold;" href="http://mailing.managy.fr/Managy-Phase-1/Managy-phase-1V3.html">Regarder comme une page web</a></webversion>
                        <br><br>
                        Managy<br><span class="mobile-link--footer">32 rue Principale, 67870 Bischoffsheim</span><br><span class="mobile-link--footer"><a href="https://www.managy.fr">www.managy.fr</a></span>
                        <br><br>
                        <unsubscribe style="color:#888888; text-decoration:underline;">Si vous préférez encore perdre votre temps, nous retirons votre adresse de nos base sur simpel demande par retour de mail.</unsubscribe>
                    </td>
                </tr>
            </table>
            <!-- Email Footer : END -->

            <!--[if mso]>
            </td>
            </tr>
            </table>
            <![endif]-->
        </div>
    </center>
</body>
</html>';


/*$mail_inscription->noFormating();
$mail_inscription->body($corps);
$mail_inscription->addDest($_POST['mail_societe']);
$mail_inscription->send();*/

header('location: https://www.managy.fr/login.html');
?>



 