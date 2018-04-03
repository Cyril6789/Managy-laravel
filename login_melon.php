<?php session_start();
if(is_file('./moduls/login/langs/fr.inc'))
    include ('./moduls/login/langs/fr.inc');
    $_SESSION['id'] = '';
    $_SESSION['prenom'] = '';
    $_SESSION['nom'] = '';
    $_SESSION['mail'] = '';
    $_SESSION['compte_principal'] = '';
    $_SESSION['template'] = '';
    $_SESSION['gerant'] = '';
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title><?php echo LOGIN_TITLE;?></title>
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

    <!--=== CSS ===-->

    <!-- Bootstrap -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

    <!-- Theme -->
    <link href="assets/css/main.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/plugins.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/responsive.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.css" rel="stylesheet" type="text/css" />

    <!-- Login -->
    <link href="assets/css/login.css" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="assets/css/fontawesome/font-awesome.min.css">
    <!--[if IE 7]>
    <link rel="stylesheet" href="assets/css/fontawesome/font-awesome-ie7.min.css">
    <![endif]-->

    <!--[if IE 8]>
    <link href="assets/css/ie8.css" rel="stylesheet" type="text/css" />
    <![endif]-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>

    <!--=== JavaScript ===-->

    <script type="text/javascript" src="assets/js/libs/jquery-1.10.2.min.js"></script>

    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="assets/js/libs/lodash.compat.min.js"></script>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="assets/js/libs/html5shiv.js"></script>
    <![endif]-->

    <!-- Beautiful Checkboxes -->
    <script type="text/javascript" src="plugins/uniform/jquery.uniform.min.js"></script>

    <!-- Form Validation -->
    <script type="text/javascript" src="plugins/validation/jquery.validate.min.js"></script>

    <!-- Slim Progress Bars -->
    <script type="text/javascript" src="plugins/nprogress/nprogress.js"></script>
    <script type="text/javascript" src="plugins/validation.js"></script>

</head>

<body class=login onload="document.getElementById('login_name').focus()">

<!-- Logo -->
<div class="logo">
    <a href="/tf-mango/"><img src="./templates/melon/img/logo.png" alt="Managy"  height="500"></a>
</div>
<!-- /Logo -->

<div class="box">
    <div class="content">
    <!-- Login Formular -->

    <form class="form-vertical login-form" action="" method="get" id="form_login">
        <!-- Title -->
        <h3 class="form-title"><?php echo LOGIN_WELCOME;?></h3>

        <!-- Error Message -->
        <div class="alert fade in alert-danger" style="display: none;" id="error_text">

        </div>

        <!-- Input Fields -->
        <div class="form-group">
            <!--<label for="username">Username:</label>-->
            <div class="input-icon">
                <i class="fa fa-user"></i>
                <input type="text" name="login_name" id="login_name" class="form-control" placeholder="Utilisateur" autofocus="autofocus" data-rule-required="true" data-msg-required="Merci de saisir votre identifiant !" />
            </div>
        </div>
        <div class="form-group">
            <!--<label for="password">Password:</label>-->
            <div class="input-icon">
                <i class="fa fa-lock"></i>
                <input type="password" name="login_pw" id="login_pw" class="form-control" placeholder="Mot de passe" data-rule-required="true" data-msg-required="Merci de saisir votre mot de passe !" />
            </div>
        </div>
        <!-- /Input Fields -->

        <!-- Form Actions -->
        <div class="form-actions">
            <label class="checkbox pull-left"><input type="checkbox" class="uniform" name="remember" disabled> Rester connecté ?</label>
            <button type="submit" class="submit btn btn-primary pull-right">
                Connexion <i class="fa fa-angle-right"></i>
            </button>
        </div>
    </form>
    <!-- /Login Formular -->


    </div>

    <div class="inner-box">
        <div class="content">
            <!-- Close Button -->
            <!-- Link as Toggle Button -->
            <a href="./forgot.html" class="forgot-password-link">Mot de passe oublié ?</a>

        </div> <!-- /.content -->
    </div>

<!-- /Login Box -->
<script>
    $('#form_login').submit(function(e){
        if($("#login_pw").val() == 0 || $("#login_name").val() == 0){
            $('#error_text').html("Merci de saisir votre identifiant et votre mot de passe !");
            $('#error_text').show('slow');
            setTimeout(function(){
                $('#error_text').hide('slow');
            }, 3000);
        }
        else{
            var pseudo = $("#login_name").val();
            var password = $("#login_pw").val();
            klient = new XMLHttpRequest();
            klient.onreadystatechange = retourClient;
            klient.open("GET", "./ajax/login.php?login_name="+pseudo+"&login_pw="+password);
            klient.send(null);
        }
        e.preventDefault();
    });

    function retourClient() {
        if(klient.responseText == 'true')
        {
            $('#error_text').hide();
            $('#error_text').html('Redirection en cours');
            $('#error_text').show('slow');
            $(location).attr('href', '.<?php if($_SERVER['REQUEST_URI'] == '/login.php' OR $_SERVER['REQUEST_URI'] == '/login.html') echo '/dashboard'; else echo $_SERVER['REQUEST_URI'];?>');
        }
        else
        {
                $('#error_text').html('Vos informations de connexion sont incorrectes.');
                $('#error_text').show('slow');
        }
        setTimeout(function () {
            $('#error_text').hide('slow');
        }, 3000);
    }
</script>

</body>
</html>
