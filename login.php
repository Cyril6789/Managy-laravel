<?php session_start();
require_once('./classes/mysql.class.php');
require_once('./classes/Maileur.class.php');
require_once('./classes/PhpMailer/PHPMailerAutoload.php');
require_once('./classes/notificationMail.class.php');
require_once('./moduls/logs/classes/addLog.class.php');
require_once('./functions/crypt_pass.func.php');
require_once('./classes/SmsFactor.class.php');
require_once('./classes/smseur.class.php');

if(is_file('./moduls/login/langs/fr.inc'))
    include ('./moduls/login/langs/fr.inc');
    $_SESSION['id'] = '';
    $_SESSION['prenom'] = '';
    $_SESSION['nom'] = '';
    $_SESSION['mail'] = '';
    $_SESSION['compte_principal'] = '';
    $_SESSION['template'] = '';
    $_SESSION['gerant'] = '';
    $_SESSION['modal_subscribe'] = false;



if(isset($_POST['username']) AND isset($_POST['password'])) {


    $db = new MySQL();

    $pseudo = $db->SQLFix($_POST['username']);
    $pass = crypt_pass($db->SQLFix($_POST['password']));


    $db->Query("SELECT staffs.id, prenom, staffs.nom, staffs.portable, mail, staffs.compte_principal, status_chat, gerant, nom_societe, two_steps_auth
            FROM staffs
            INNER JOIN comptes_principaux
            ON (comptes_principaux.id = staffs.compte_principal)
            LEFT JOIN licences_staffs AS ls
            ON (staffs.licence = ls.id)
            WHERE (pseudo='" . $pseudo . "' OR mail='" . $pseudo . "')
            AND pass='" . $pass . "'
            AND comptes_principaux.bloque = '0'
            AND
                ( ls.date_fin > '" . time() . "' OR ls.incluse = '1' OR staffs.gerant = '1')
            ");
    //echo $db->Error();
    //echo $db->GetHTML();


    $row = $db->Row();
    if ($db->RowCount()) {

        $tsa = $row->two_steps_auth; //connexion en 2 étapes

        if ($tsa)
        {
            if ($_COOKIE['session' . sha1($row->id)])
            {
                //echo $_COOKIE['session' . sha1($row->id)];
                //die();
                $valeur_cookie = $db->SQLFix($_COOKIE['session' . sha1($row->id)]);

                $sql = 'SELECT COUNT(*) as nb FROM two_steps_auth WHERE id_staff="'.$row->id.'" AND cle="'.$valeur_cookie.'" ';
                $db->Query($sql);
                $nb = $db->Row();

                if($nb->nb)
                    $connection = true;
                else
                    $sms = true;
            }
            else
                {
                $sms = true;
            }

        } else {
            $connection = true;
        }


        if($connection)
            echo 'con';
        if($sms) {
            //echo 'sms'.$row->portable;

            $_SESSION['rand'] = rand(100000, 999999);
            $_SESSION['time'] = time() + (700); // 11 min

            //$sql = 'UPDATE staffs SET conf_code_auth = "'.$rand.'", time_valide_conf_code_auth = "'.$time.'" WHERE id="'.$row->id.'" ';

            $db->Query($sql);
           // echo $sql;
            echo $db->Error();

            $sms = new Smseur('Voici votre code d\'authentification : '.$_SESSION['rand'].'. Il n\'est valable que 10 minutes !', '1');
            $sms->AddExpediteur('Managy');
            $sms->AddNumero($row->portable);
            $sms->envoie();

            $_SESSION['id_temp'] = $row->id;
            $_SESSION['prenom_temp'] = $row->prenom;
            $_SESSION['nom_temp'] = $row->nom;
            $_SESSION['mail_temp'] = $row->mail;
            $_SESSION['compte_principal_temp'] = $row->compte_principal;
            $_SESSION['status_temp'] = $row->status_chat;
            $_SESSION['gerant_temp'] = $row->gerant;

        }

        if ($connection) {
            $_SESSION['id'] = $row->id;
            $_SESSION['prenom'] = $row->prenom;
            $_SESSION['nom'] = $row->nom;
            $_SESSION['mail'] = $row->mail;
            $_SESSION['compte_principal'] = $row->compte_principal;
            $_SESSION['status'] = $row->status_chat;
            $_SESSION['gerant'] = $row->gerant;


            $db->Query('UPDATE staffs SET session_id = "' . session_id() . '" WHERE id="' . $row->id . ' "');
            if ($_SESSION['id'] > 1) {

                $mail = new Maileur('Connexion de ' . $_SESSION['prenom'] . ' sur managy');
                $mail->addExpediteur('noreply@managy.fr', 'Managy');
                $mail->addDest('contact@depaninfo67.com');
                $mail->AddTitle('Connexion de ' . $_SESSION['prenom']);
                $mail->setLogo('managy');

                $texte = '<strong>' . $_SESSION['prenom'] . '</strong> vient de se connecter sur managy.fr<br />
        Nom de la société : <strong>' . $row->nom_societe . '</strong><br />
        Adresse IP de connexion : <strong>' . $_SERVER['REMOTE_ADDR'] . '</strong>';


                $mail->body($texte);
                $mail->send();


                //Notification pour le gérant de chaque société
                if (!$_SESSION['gerant']) {
                    $notif = new notificationMail(5);
                    $notif->tab_parse(array('%prenom_staff%' => $_SESSION['prenom'], '%nom_staff%' => $_SESSION['nom'], '%ip%' => $_SERVER['REMOTE_ADDR']));
                    $notif->sendMail();
                }

            }
            $log = new addLog('s\'est connecté sur Managy.fr (IP : ' . $_SERVER["REMOTE_ADDR"] . ')');
            $log->insert();
            if($_SESSION['redirect'] != '' and $_SESSION['redirect'] != '/login.php' and $_SESSION['redirect'] != '/login.html')
                header('location: .'.$_SESSION['redirect']);
            else
               header('location: ./dashboard');
        }
    }
    else
        $error = true;



    $db->Close();
}

if(isset($_POST['code']))
{
    //print_r($_POST);
    //print_r($_SESSION);

    if($_SESSION['rand'] == $_POST['code'] AND time() <= $_SESSION['time'])
    {
        $db = new MySQL();

        $_SESSION['id'] = $_SESSION['id_temp'];
        $_SESSION['prenom'] = $_SESSION['prenom_temp'];
        $_SESSION['nom'] = $_SESSION['nom_temp'];
        $_SESSION['mail'] = $_SESSION['mail_temp'];
        $_SESSION['compte_principal'] = $_SESSION['compte_principal_temp'];
        $_SESSION['status'] = $_SESSION['status_temp'];
        $_SESSION['gerant'] = $_SESSION['gerant_temp'];


        if($_POST['navigateur']) {

            $valeur_cookie = sha1(rand(100, 100000000));

            setcookie('session' . sha1($_SESSION['id']), $valeur_cookie, time() + (60 * 60 * 24 * 365));


            $sql = 'INSERT INTO two_steps_auth (id_staff, cle) VALUES ("' . $_SESSION['id'] . '", "' . $valeur_cookie . '") ';

            $db->Query($sql);
        }

        $db->Query('UPDATE staffs SET session_id = "' . session_id() . '" WHERE id="' . $_SESSION['id'] . ' "');
        if ($_SESSION['id'] > 1) {

            $mail = new Maileur('Connexion de ' . $_SESSION['prenom'] . ' sur managy');
            $mail->addExpediteur('noreply@managy.fr', 'Managy');
            $mail->addDest('contact@depaninfo67.com');
            $mail->AddTitle('Connexion de ' . $_SESSION['prenom']);
            $mail->setLogo('managy');

            $texte = '<strong>' . $_SESSION['prenom'] . '</strong> vient de se connecter sur managy.fr<br />
        Nom de la société : <strong>' . $row->nom_societe . '</strong><br />
        Adresse IP de connexion : <strong>' . $_SERVER['REMOTE_ADDR'] . '</strong>';


            $mail->body($texte);
            $mail->send();


            //Notification pour le gérant de chaque société
            if (!$_SESSION['gerant']) {
                $notif = new notificationMail(5);
                $notif->tab_parse(array('%prenom_staff%' => $_SESSION['prenom'], '%nom_staff%' => $_SESSION['nom'], '%ip%' => $_SERVER['REMOTE_ADDR']));
                $notif->sendMail();
            }

        }
        $log = new addLog('s\'est connecté sur Managy.fr (IP : ' . $_SERVER["REMOTE_ADDR"] . ')');
        $log->insert();
        header('location: ./dashboard');
    }
    else
        $error_sms = true;


    $sms = true;

}

?>

<!DOCTYPE html>
<!--
Template Name: Metronic - Responsive Admin Dashboard Template build with Twitter Bootstrap 3.3.7
Version: 4.7.5
Author: KeenThemes
Website: http://www.keenthemes.com/
Contact: support@keenthemes.com
Follow: www.twitter.com/keenthemes
Dribbble: www.dribbble.com/keenthemes
Like: www.facebook.com/keenthemes
Purchase: http://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes
Renew Support: http://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes
License: You must have a valid license purchased only from themeforest(the above link) in order to legally use the theme for your project.
-->
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="fr">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title><?php echo LOGIN_TITLE;?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="Preview page of Metronic Admin Theme #1 for " name="description" />
    <meta content="" name="author" />
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="./templates/v4/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="./templates/v4/assets/global/css/components.min.css" rel="stylesheet" id="style_components" type="text/css" />
    <link href="./templates/v4/assets/global/css/plugins.min.css" rel="stylesheet" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="./templates/v4/assets/pages/css/login-5.min.css" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL STYLES -->
    <!-- BEGIN THEME LAYOUT STYLES -->
    <!-- END THEME LAYOUT STYLES -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="apple-touch-icon" sizes="57x57" href="img/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="img/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="img/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="img/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="img/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="img/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="img/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="img/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="img/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="img/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="img/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon/favicon-16x16.png">
    <link rel="manifest" href="img/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="img/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
<!-- END HEAD -->

<body class=" login">
<!-- BEGIN : LOGIN PAGE 5-1 -->
<div class="user-login-5">
    <div class="row bs-reset">
        <div class="col-md-6 bs-reset mt-login-5-bsfix">
            <div class="login-bg" style="background-image:url(./templates/v4/assets/pages/img/login/bg.jpg)">
                <img class="login-logo" src="./templates/v4/img/logo-light.png" width="200px" /> </div>
        </div>
        <div class="col-md-6 login-container bs-reset mt-login-5-bsfix">
            <div class="login-content">
                <h1>Connexion à Managy</h1><?php
                if(!$sms) {
                    $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
                ?>
                <p> Managy.fr, le premier gestionnaire d'interventions pour les informaticiens.</p>
                <form action="" class="login-form" method="post">


                        <div class="alert alert-danger display-hide">
                            <button class="close" data-close="alert"></button>
                            <span>Veuillez saisir votre identifiant et votre mot de passe. </span>
                        </div>
                        <?php if ($error) {
                            ?>
                            <div class="alert alert-danger">
                                <button class="close" data-close="alert"></button>
                                <span>Identifiant ou mot de passe incorrect</span>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="row">
                            <div class="col-xs-6">
                                <input class="form-control form-control-solid placeholder-no-fix form-group" type="text"
                                       placeholder="Utilisateur" name="username" required/></div>
                            <div class="col-xs-6">
                                <input class="form-control form-control-solid placeholder-no-fix form-group"
                                       type="password" placeholder="Mot de passe" name="password" required/></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">

                            </div>
                            <div class="col-sm-8 text-right">
                                <div class="forgot-password">
                                    <a href="./forgot.php" id="forget-password" class="forget-password">Mot de passe
                                        oublié ?</a>
                                </div>
                                <button class="btn green" type="submit">Connexion</button>
                            </div>
                        </div>
                </form>
                        <?php
                    }
                    else
                    {
                        ?>
                <p> Managy vient de vous envoyer un code par SMS. Merci de le saisir ci-dessous pour valider votre connexion</p>
                <form action="" class="login-form" method="post">
                        <div class="alert alert-danger display-hide">
                            <button class="close" data-close="alert"></button>
                            <span>Veuillez saisir votre code reçu par SMS </span>
                        </div>
                        <?php if ($error_sms) {
                        ?>
                        <div class="alert alert-danger">
                            <button class="close" data-close="alert"></button>
                            <span>Code incorrect</span>
                        </div>
                        <?php
                    }
                        ?>
                        <div class="row">
                            <div class="col-xs-6">
                                <input class="form-control form-control-solid placeholder-no-fix form-group" type="text"
                                       placeholder="Code SMS" name="code" required/></div>

                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <label class="rememberme mt-checkbox mt-checkbox-outline">
                                    <input type="checkbox" name="navigateur" <?php if($_POST['navigateur']) { echo 'checked'; } ?> value="1" /> Mémoriser ce navigateur pour plus tard ?
                                    <span></span>
                                </label>
                            </div>
                            <div class="col-sm-8 text-right">
                                <div class="forgot-password">
                                    <a href="./login.php" id="forget-password" class="forget-password">Code non reçu ?</a>
                                </div>
                                <button class="btn green" type="submit">Valider</button>
                            </div>
                        </div>

                    </form>
                        <?php
                    }
                    ?>
                <!-- BEGIN FORGOT PASSWORD FORM --
                <form class="forget-form" action="javascript:;" method="post">
                    <h3 class="font-green">Forgot Password ?</h3>
                    <p> Enter your e-mail address below to reset your password. </p>
                    <div class="form-group">
                        <input class="form-control placeholder-no-fix form-group" type="text" autocomplete="off" placeholder="Email" name="email" /> </div>
                    <div class="form-actions">
                        <button type="button" id="back-btn" class="btn green btn-outline">Back</button>
                        <button type="submit" class="btn btn-success uppercase pull-right">Submit</button>
                    </div>
                </form>
                <!-- END FORGOT PASSWORD FORM -->
            </div>
            <div class="login-footer">
                <div class="row bs-reset">

                    <div class="col-xs-7 bs-reset">
                        <div class="login-copyright text-right">
                            <p>Copyright &copy; Managy <?php echo date('Y');?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END : LOGIN PAGE 5-1 -->
<!--[if lt IE 9]>
<script src="./templates/v4/assets/global/plugins/respond.min.js"></script>
<script src="./templates/v4/assets/global/plugins/excanvas.min.js"></script>
<script src="./templates/v4/assets/global/plugins/ie8.fix.min.js"></script>
<![endif]-->
<!-- BEGIN CORE PLUGINS -->
<script src="./templates/v4/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./templates/v4/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>

<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="./templates/v4/assets/pages/scripts/login-5.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<!-- BEGIN THEME LAYOUT SCRIPTS -->
<!-- END THEME LAYOUT SCRIPTS -->

</body>

</html>
