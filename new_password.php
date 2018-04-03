
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

<?php

include('./classes/mysql.class.php');

$db = new MySQL();

$mot = $db->SQLFix($_GET['q']);

$db->Query('SELECT id FROM staffs WHERE forgot = "'.$mot.'"');
$row = $db->Row();

$id_staff = $row->id;


if($id_staff > 0 AND $mot != '')
{
?>
<div class="alert fade in alert-success" style="display: none; text-align: center;" id="success_change">

</div>
<div class="alert fade in alert-danger" style="display: none; text-align: center;" id="error_change_general">

</div>
<div class="box" id="element_change">
    <div class="content">
        <!-- Login Formular -->

        <form class="form-vertical login-form" action="" method="get" id="form_change">
            <!-- Title -->
            <h3 class="form-title">Choisir un nouveau mot de passe</h3>

            <!-- Error Message -->
            <div class="alert fade in alert-danger" style="display: none;" id="error_change">

            </div>


            <!-- Input Fields -->
            <div class="form-group">
                <!--<label for="username">Username:</label>-->
                <div class="input-icon">
                    <i class="icon-user"></i>
                    <input type="password" name="pass1" id="pass1" class="form-control" placeholder="Nouveau mot de passe" autofocus="autofocus" data-rule-required="true" data-msg-required="Merci de saisir votre mot de passe!" />
                </div>
            </div>

            <!-- /Input Fields -->
            <div class="form-group">
                <!--<label for="username">Username:</label>-->
                <div class="input-icon">
                    <i class="icon-user"></i>
                    <input type="password" name="pass2" id="pass2" class="form-control" placeholder="Resaisissez votre mot de passe" autofocus="autofocus" data-rule-required="true" data-msg-required="Merci de resaisir votre mot de passe!" />
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="submit btn btn-primary pull-right">
Modifier le mot de passe <i class="icon-angle-right"></i>
                </button>
            </div>
        </form>
        <!-- /Login Formular -->



    </div>
    <?php

}
else
{
    ?>
    <div class="alert fade in alert-danger" style="text-align: center;" id="error_q_text">
    Ce lien est périmé. Merci de refaire une demande de mot de passe oublié.
    </div>

<div class="alert fade in alert-success" style="display: none; text-align: center;" id="success_text">

</div>
<div class="box" id="element_form">
    <div class="content">
        <!-- Login Formular -->

        <form class="form-vertical login-form" action="" method="get" id="form_forgot">
            <!-- Title -->
            <h3 class="form-title">Mot de pass oublié ?</h3>

            <!-- Error Message -->
            <div class="alert fade in alert-danger" style="display: none;" id="error_text">

            </div>


            <!-- Input Fields -->
            <div class="form-group">
                <!--<label for="username">Username:</label>-->
                <div class="input-icon">
                    <i class="icon-user"></i>
                    <input type="email" name="mail" id="mail" class="form-control" placeholder="Adresse mail" autofocus="autofocus" data-rule-required="true" data-msg-required="Merci de saisir votre adresse mail !" />
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
    <?php
}
?>

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
            klient.onreadystatechange = retourClient1;
            klient.open("GET", "./ajax/forgot.php?mail="+mail);
            klient.send(null);
        }
        e.preventDefault();
    });

    function retourClient1()
    {
        $('#error_q_text').hide();
        $('#element_form').hide();
        $('#success_text').show();
        $('#success_text').html('Si l\'adresse mail saisie correspond à un compte, un mail vient d\'être envoyé sur cette adresse.');



    }

    $('#form_change').submit(function(e){
        if($("#pass1").val() == 0 || $('#pass2').val() == 0)
        {
            $('#error_change').html("Merci de remplir tous les champs !");
            $('#error_change').show('slow');
            setTimeout(function(){
                $('#error_change').hide('slow');
            }, 3000);
        }
        else
        {
            if($('#pass1').val() != $('#pass2').val())
            {
                $('#error_change').html("Les mots de passes ne sont pas identiques");
                $('#pass1').val('');
                $('#pass2').val('');
                $('#error_change').show('slow');
                setTimeout(function(){
                    $('#error_change').hide('slow');
                }, 3000);
            }
            else
            {
                var pass = $("#pass1").val();
                var mot = '<?php echo $mot; ?>';
                var id_staff = '<?php echo $id_staff;?>';
                klient2 = new XMLHttpRequest();
                klient2.onreadystatechange = retourClient2;
                klient2.open("GET", "./ajax/new_password.php?pass="+pass+"&mot="+mot+"&id="+id_staff);
                klient2.send(null);
            }
        }
        e.preventDefault();
    });

    function retourClient2()
    {
        $('#element_change').hide();
        if(klient2.responseText == 'true')
        {

            $('#error_q_text').hide();
            $('#error_change_general').hide();
            $('#error_change').hide();
            $('#success_change').html('Votre mot de passe a bien été modifié.');
            $('#success_change').show();

            setTimeout(function(){
                $(location).attr('href', './login.html');
            }, 3000);

        }
        else
        {
            $('#success_change').hide();
            $('#error_change_general').show();
            $('#error_change_general').html(klient2.responseText); //'Une erreur inconnue est survenue !');
        }

    }

</script>



</body>
</html>
