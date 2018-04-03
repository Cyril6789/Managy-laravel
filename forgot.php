<?php session_start();
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
    <title>Mot de passe oublié</title>

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
<div class="alert fade in alert-success" style="display: none; text-align: center;" id="success_text">

</div>
<div class="box" id="element_form">
    <div class="content">
    <!-- Login Formular -->

    <form class="form-vertical login-form" action="" method="get" id="form_forgot">
        <!-- Title -->
        <h3 class="form-title">Mot de passe oublié ?</h3>

        <!-- Error Message -->
        <div class="alert fade in alert-danger" style="display: none;" id="error_text">

        </div>


        <!-- Input Fields -->
            <div class="form-group">
                <!--<label for="username">Username:</label>-->
                <div class="input-icon">
                    <i class="fa fa-envelope"></i>
                    <input type="email" name="mail" id="mail" class="form-control" placeholder="Adresse e-mail" autofocus="autofocus" data-rule-required="true" data-msg-required="Merci de saisir votre adresse mail !" />
                </div>
            </div>

            <!-- /Input Fields -->

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="submit btn btn-primary pull-right">
                    Envoyer un mail de réinitialisation <i class="icon-angle-right"></i>
                </button>
            </div>
    </form>
    <!-- /Login Formular -->



</div>
<!-- /Login Box -->
<script>
    $('#form_forgot').submit(function(e){
        if($("#mail").val() == 0){
            $('#error_text').html("Merci de saisir votre adresse mail !");
            $('#error_text').show('slow');
            setTimeout(function(){
                $('#error_text').hide('slow');
            }, 3000);
        }
        else{
            var mail = $("#mail").val();
            klient = new XMLHttpRequest();
            klient.onreadystatechange = retourClient;
            klient.open("GET", "./ajax/forgot.php?mail="+mail);
            klient.send(null);
        }
        e.preventDefault();
    });

    function retourClient()
    {
        $('#element_form').hide();
        $('#success_text').show();
        $('#success_text').html('Si l\'adresse mail saisie correspond à un compte, un mail vient d\'être envoyé sur cette adresse.');



    }
</script>



</body>
</html>
