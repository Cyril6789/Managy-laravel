<?php session_start();
if($_SESSION['nb_erreur']) {
    for ($i = 1; $i <= $_SESSION['nb_erreur']; $i++)
        echo $_SESSION['erreur'][$i] . ' <br />';

    $_SESSION['nb_erreur'] = 0;
    $_SESSION['erreur'] = '';

    die();
}

?>
<!DOCTYPE html>

<html lang="fr">

<head>
    <meta charset="UTF-8">

    <!-- Mobile viewport optimized -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1.0, user-scalable=no">

    <!-- Meta Tags - Description for Search Engine purposes -->
    <meta name="description" content=" Managy.fr - Le logiciel en ligne de gestion d'interventions">
    <meta name="keywords" content="managy, interventions, informaticiens, dépanneur, gestionnaire">
    <meta name="author" content="Cyril Heilmann">

    <!-- Website Title -->
    <title>Managy.fr - Le logiciel en ligne de gestion d'interventions  <</title>
    <link rel="shortcut icon" href="landing/images/favicon.png" type="image/x-icon">
    <link rel="apple-touch-icon-precomposed" href="landing/images/apple-touch-icon.png">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,400i,500,700|Roboto:400,700" rel="stylesheet">

    <!-- CSS links -->
    <link rel="stylesheet" type="text/css" href="landing/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="landing/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="landing/css/swiper.min.css">
    <link rel="stylesheet" type="text/css" href="landing/css/owl.carousel.css">
    <link rel="stylesheet" type="text/css" href="landing/css/style.css">
    <link rel="stylesheet" type="text/css" href="landing/css/responsive.css">
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script src="landing/js/jquery-3.1.1.min.js"></script>
</head>

<body id="page-top">

<!-- ===== Loader ===== -->
<div class="loader">
    <div class="spinner">
        <div class="cloud1"><img src="landing/images/clouds/cloud-blue.svg" alt=""></div>
        <div class="cloud2"><img src="landing/images/clouds/cloud-green.svg" alt=""></div>
    </div>
</div>
<!-- ===== End of Loader ===== -->



<!-- ===== Top Header ===== -->
<div class="top-header">
    <div class="container">
        <div class="row">
            <!-- phone and social begin -->
            <div class="col-md-6 col-sm-6 col-xs-12">
                <ul class="social">
                    <li><a href="tel:0687470091"><i class="fa fa-phone"></i> 06 87 47 00 91</a></li>
                    <li><a href="https://www.facebook.com/managy.fr/"><i class="fa fa-facebook"></i></a></li>
                </ul>
            </div>
            <!-- phone and social end -->

            <div class="col-md-6 col-sm-6 col-xs-12">
                <!-- chat and account button begin -->
                <ul class="top-button">
                    <li class="login">
                        <a id="modal_trigger" class="btn btn-border-rev btn-blue" href="#"><i class="fa fa-user"></i>Accès client | Inscription</a>
                    </li>
                </ul>
                <!-- chat and account button end -->
            </div>
        </div>
    </div>
</div>
<!-- ===== End of Top Header ===== -->



<!-- ===== Navigation ===== -->
<header class="fixed">
    <nav class="navbar navbar-default navbar-static-top fluid_header centered">
        <div class="container">

            <!-- Logo -->
            <div class="col-md-2 col-sm-3 col-xs-3">
                <a class="navbar-brand" href="dashboard"><img src="/landing/images/logo.png" alt="logo" ></a>
                <!-- INSERT YOUR LOGO HERE -->
            </div>

            <!-- Main Menu -->
            <div class="col-md-10 col-sm-9 col-xs-9">
                <div class="navbar-header page-scroll">
                    <button type="button" class="navbar-toggle toggle-menu menu-right push-body" data-toggle="collapse" data-target="#main-nav" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>

                <div class="collapse navbar-collapse pull-right cbp-spmenu cbp-spmenu-vertical cbp-spmenu-right" id="main-nav">
                    <ul class="nav navbar-nav navbar-right">
                        <li class="active"><a href="#home" class="page-scroll" role="button">Accueil</a></li>
                        <li><a href="#services" class="page-scroll" role="button">Fonctionnalités</a></li>
                        <li><a href="#pricing" class="page-scroll" role="button">Tarifs</a></li>
                        <li><a href="#contact" class="page-scroll" role="button">contact</a></li>
                    </ul>
                </div>
            </div>
            <!-- End of Main Menu -->
        </div>
    </nav>
</header>
<!-- ===== End of Navigation ===== -->


<!-- ===== Main Section ===== -->
<section class="main" id="home">

    <!-- Demo 3 -->
    <div class="demo3">

        <!-- Main Content -->
        <div class="demo-content container">
            <div class="col-md-12">
                <h2><span>Managy.fr</span><br />votre gestionnaire d'interventions <br> entièrement <span>en ligne</span>.</h2>
                <p>Rapide et simple de prise en main
                    <br>inscrivez-vous maintenant et bénéficiez d'un mois d'essai gratuit.<br /></p>
                <div>
                    <a href="javascript:void();" class="btn btn-border btn-blue" onclick="inscription();"><i class="fa fa-star"></i>Inscription</a>
                    <a href="#pricing" class="btn btn-border-rev btn-green">Voir les tarifs</a>
                </div>
            </div>
            <div class="col-md-0">

                </div>
        </div>
        <!-- End of Main Content -->

        <!-- Start of Video background -->
        <video class="video-bg" preload="auto" loop="" autoplay="" poster="./landing/video/video.jpg">
            <source src="./landing/video/video.webm" type="video/webm">
        </video>
        <!-- End of Video background -->

    </div>
    <!-- End of Demo 3 -->
</section>
<!-- ===== End of Main Section ===== -->





<!-- ===== Start of About ===== -->
<section id="about">
    <div class="container">
        <div class="col-md-6">
            <h2>Bienvenue sur Managy.fr</h2>
            <p>Managy.fr, le premier gestionnaire d'interventions pour les réparateurs. Notre logiciel saura pour satisfaire en tout points pour gérer vos interventions atelier et vos interventions sur site.<br />N'hesitez pas à nous contacter pour en savoir plus.</p>
            <a href="javascript:void();" class="btn btn-border btn-blue" onclick="inscription();">Inscription</a>
        </div>

        <!-- About Image -->
        <div class="col-md-6 about-image">
            <img src="landing/images/about.svg" alt="">
        </div>

        <!-- Clouds that are used in the animation -->
        <div class="clouds">
            <img src="landing/images/clouds/cloud1.svg" alt="" class="cloud1">
            <img src="landing/images/clouds/cloud2.svg" alt="" class="cloud2">
            <img src="landing/images/clouds/cloud3.svg" alt="" class="cloud3">
            <img src="landing/images/clouds/cloud1.svg" alt="" class="cloud4">
            <img src="landing/images/clouds/cloud3.svg" alt="" class="cloud5">
        </div>
    </div>
</section>
<!-- ===== End of of About ===== -->



<!-- ===== Start of Services ===== -->
<section id="services">
    <div class="container main-content">
        <div class="section-title">
            <h2>Fonctionnalités</h2>
            <p>Profitez de notre interface intuitive pour organiser vos commandes, vos rendez-vous, vos interventions. La prise en main est facile est rapide. Tout est prévu pour vous simplifier la vie.</p>
        </div>

        <!-- 1st Row of Service Section -->
        <div class="row">
            <div class="col-sm-4 col-xs-12 service">
                <img src="landing/images/icons/cloud-server.svg" alt="">
                <h3>Stockage illimité</h3>
                <p>Une fois inscrit, vous n'avez plus aucune limite. Profitez de toute la puissance de Managy.fr pour organiser vos interventions.</p>
            </div>
            <div class="col-sm-4 col-xs-12 service">
                <img src="landing/images/icons/locked.svg" alt="">
                <h3>Serveur sécurisé</h3>
                <p>Site sécurisé, votre accès ne craindra aucune intrusion. </p>
            </div>
            <div class="col-sm-4 col-xs-12 service">
                <img src="landing/images/icons/transfer.svg" alt="">
                <h3>Travail collaboratif</h3>
                <p>Créez un accès à vos techniciens et commencez à être plus productif avec Managy.fr</p>
            </div>
        </div>
        <!-- End of 1st Row of Service Section -->

        <!-- 2nd Row of Service Section -->
        <div class="row">
            <div class="col-sm-4 col-xs-12 service">
                <img src="landing/images/icons/chat1.svg" alt="">
                <h3>Chat interne</h3>
                <p>Discutez avec vos collaborateurs directement par le biais du chat interne. </p>
            </div>
            <div class="col-sm-4 col-xs-12 service">
                <img src="landing/images/icons/customer-service.svg" alt="">
                <h3>Support technique</h3>
                <p>Nous sommes à votre écoute pour toute question ou aide au démarrage. Nous prenons aussi en compte vos suggestions d'amélioration.</p>
            </div>
            <div class="col-sm-4 col-xs-12 service">
                <img src="landing/images/icons/optimization.svg" alt="">
                <h3>Statistiques</h3>
                <p>Profitez de la puissance de Managy.fr pour évaluer votre rendement ainsi que celui de vos techniciens.</p>
            </div>
        </div>
        <!-- End of 2nd Row of Service Section -->
    </div>

    <!-- Start of Info 1 -->
    <div class="container-fluid main-content info">
        <div class="container">
            <div class="col-md-6 info-text">
                <h3>Evolution permanente</h3>
                <p>Managy version 3 est mis à jour continuellement ! De nouvelles fonctionnalités fleurissent au fil du temps... Ces mises à jour sont gratuites !</p>
                <ul>
                    <li>Logiciel réellement adapté aux informaticiens</li>
                    <li>Nouvelles fonctionnalités régulières</li>
                    <li>Dévelloppeur à l'écoute de vos suggestions</li>
                </ul>
            </div>
            <div class="col-md-4 col-md-offset-2 info-image">
                <img src="landing/images/icons/custom.svg" alt="">
            </div>
        </div>
    </div>
    <!-- End of Info 1 -->

    <!-- Start of Info 2 -->
    <div class="container-fluid main-content info" id="secondary">
        <div class="container">
            <div class="col-md-4 info-image">
                <img src="landing/images/icons/responsive-design.svg" alt="">
            </div>
            <div class="col-md-offset-2 col-md-6 info-text">
                <h3>Entièrement responsive</h3>
                <p>Managy 3 sera capable de s'intégrer parfaitement à chacune de vos plateformes. Pc de bureau, tablettes ou smartphones.</p>
                <ul>
                    <li>Design moderne et flat</li>
                    <li>Intuitif et facile de pris en main</li>
                    <li>Fonctionne partout</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- End of Info 2 -->

    <!-- Start of Info 3 -->
    <div class="container-fluid main-content info">
        <div class="container">
            <div class="col-md-6 info-text">
                <h3>Adaptable</h3>
                <p>Managy est d'abord prévu pour les réparateurs. Mais d'autres sociétés spécialisées dans d'autre domaines font partie de nos clients ! N'hestez pas à nous contacter pour adapter Managy à vos besoins !</p>
                <ul>
                    <li>Metallurgistes..</li>
                    <li>SAV..</li>
                    <li>Etc...</li>
                </ul>
            </div>
            <div class="col-md-4 col-md-offset-2 info-image">
                <img src="landing/images/icons/pixel-perfect.svg" alt="">
            </div>
        </div>
    </div>
    <!-- End of Info 3 -->
</section>
<!-- ===== End of Service Section ===== -->





<!-- ===== Start of Pricing ===== -->
<section id="pricing">
    <div class="container main-content">
        <div class="section-title">
            <h2>Tarifications</h2>
        </div>

        <!-- starter, basic, agency, enterprise -->
        <div class="table-responsive">
            <!-- start of 1st price table -->
            <div class="col-sm-3 pricing-plan" id="starter">
                <!-- start price section -->
                <div class="row price">
                    <div>
                        <span class="currency">€</span>
                        <span class="amount">25</span>
                        <span class="month">par mois</span>
                    </div>
                </div>
                <!-- end of price section -->
                <div class="plan-type">
                    <span>Informaticiens</span>
                </div>
                <!-- start of detail section -->
                <ul class="nav">
                    <li><i class="fa fa-check"></i> 2 collaborateurs inclus</li>
                    <li><i class="fa fa-check"></i> Nombre d'interventions illimité</li>
                    <li><i class="fa fa-check"></i> Gestion des commandes</li>
                    <li><i class="fa fa-check"></i> Gestion des sous-traitances</li>
                    <li><i class="fa fa-check"></i> Calendrier des rendez-vous avec synchro iOs</li>
                    <li><i class="fa fa-check"></i> Envoie de mails prédefinis</li>
                    <li><i class="fa fa-check"></i> Envoie de SMS prédefinis (SMS en supplément)</li>
                    <li><i class="fa fa-check"></i> Gestion des accès collaborateurs</li>
                    <li><i class="fa fa-check"></i> Statistiques techniciens</li>
                    <li><i class="fa fa-check"></i> Statistiques interventions</li>
                </ul>
                <a href="javascript:void();" class="btn btn-border btn-blue" onclick="inscription();">Inscrivez-vous</a>
                <!-- end of detail section -->
            </div>
            <!-- end of 1st price table -->
 

        </div>
    </div>
</section>
<!-- ===== End of Pricing ===== -->


<!-- ===== Start of Footer ===== -->
<footer>
    <!-- start of top footer -->
    <div class="top-footer">
        <div class="container">
            <!-- start of footer contact section -->
            <div class="row" id="contact">
                <div class="container">
                    <div class="contact-us">
                        <a href="" class="expand-form"><i class="fa fa-envelope"></i></a>
                        <!-- Expanded Contact Form -->
                        <div class="contact-form">
                            <div class="col-md-8 col-md-offset-2">

                                <!-- start of form -->
                                <form id="contact-form">

                                    <!-- contact result -->
                                    <div id="contact-result"></div>
                                    <!-- end of contact result -->

                                    <div class="col-md-6">
                                        <input class="form-control input-box" type="text" name="name" placeholder="Nom">
                                    </div>
                                    <div class="col-md-6">
                                        <input class="form-control input-box" type="email" name="email" placeholder="Adresse e-mail">
                                    </div>

                                    <div class="col-md-12">
                                        <input class="form-control input-box" type="text" name="subject" placeholder="Sujet">
                                        <textarea class="form-control textarea-box" rows="8" name="message" placeholder="Message"></textarea>
                                        <button class="btn btn-border btn-blue" type="submit">Envoyez votre message</button>
                                    </div>
                                </form>
                                <!-- end of form -->

                            </div>
                        </div>
                        <!-- End of Expanded Contact Form -->
                    </div>
                </div>
            </div>
            <!-- end of footer contact section -->

            <!-- start of footer information & links section -->
            <div class="row footer-info">

                <div class="col-sm-4">
                    <a href="dashboard"><img src="landing/images/logo.png" alt=""></a>
                    <ul class="footer-links">
                        <li><i class="fa fa-phone"></i>06 87 47 00 91</li>
                        <li><i class="fa fa-envelope"></i><a href="mailto:contact@managy.fr?subject=Questions concernant votre logiciel Managy.fr&body=Bonjour, je viens de vois votre logiciel Managy.fr, mais j'ai encore une ou l'autre question à vous poser : ">contact@managy.fr</a></li>
                        <li><i class="fa fa-map-marker"></i>32 rue principale 67870 Bischoffsheim</li>
                    </ul>
                    <div class="payment">
                        <h4>Paiement</h4>
                        <ul>
                            <li><i class="fa fa-cc-paypal"></i></li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- end of footer information & links section -->
        </div>
    </div>
    <!-- end of top footer -->

    <!-- start of copyright section -->
    <div class="copyright">
        Copyright &copy; www.depaninfo67.com. Tous droits reservés
    </div>
    <!-- end of copyright section -->

</footer>
<!-- ===== End of Footer ===== -->



<!-- ===== Start of Login Pop Up div ===== -->
<div class="cd-user-modal">
    <!-- this is the entire modal form, including the background -->
    <div class="cd-user-modal-container">
        <!-- this is the container wrapper -->
        <ul class="cd-switcher">
            <li><a href="#0">Connexion</a></li>
            <li><a href="#1">Inscription</a></li>
        </ul>

        <div id="cd-login">
            <!-- log in form -->
            <form class="cd-form" id="form_login">
                <div class="alert fade in alert-danger" style="display: none;" id="error_text">
                </div>
                <p class="fieldset">
                    <label class="image-replace cd-email" for="signin-email">Identifiant</label>
                    <input class="full-width has-padding has-border" id="login_name" type="text" placeholder="Identifiant">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-password" for="signin-password">Mot de passe</label>
                    <input class="full-width has-padding has-border" id="login_pw" type="password" placeholder="Mot de passe">
                </p>
                <p class="fieldset">
                    <input type="checkbox" id="remember-me" disabled>
                    <label for="remember-me">Rester connecté</label>
                </p>
                <p class="fieldset">
                    <button type="submit" value="Login" class="btn btn-border btn-blue" onclick="$('#form_login').submit();">Connexion</button>
                </p>
            </form>
        </div>
        <!-- cd-login -->

        <div id="cd-signup">
            <!-- sign up form -->
            <form class="cd-form" id="register_form" action="register.php" method="post">
                <div class="alert fade in alert-danger error_register" style="display: none;" id="">
                </div>
                <p>Informations société</p>
                <p class="fieldset">
                    <label class="image-replace cd-username" for="societe">Societé</label>
                    <input class="full-width has-padding has-border" id="societe" name="societe" type="text" placeholder="Nom de votre société">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-email" for="mail_societe">E-mail société</label>
                    <input class="full-width has-padding has-border" id="mail_societe" name="mail_societe" type="email" placeholder="E-mail de la société">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-password" for="web">Site Web</label>
                    <input class="full-width has-padding has-border" id="web" name="web" type="text" placeholder="Site Web">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-password" for="tel">Téléphone</label>
                    <input class="full-width has-padding has-border" id="tel" name="tel" type="text" placeholder="Téléphone">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-password" for="sadresse">Adresse</label>
                    <input class="full-width has-padding has-border" id="adresse" name="adresse" type="text" placeholder="Adresse">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-password" for="cp">Code postal</label>
                    <input class="full-width has-padding has-border" id="cp" name="cp" type="text" placeholder="Code postal">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-password" for="ville">Ville</label>
                    <input class="full-width has-padding has-border" id="ville" name="ville" type="text" placeholder="Ville">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-password" for="siret">N° SIRET</label>
                    <input class="full-width has-padding has-border" id="siret" name="siret" type="texte" placeholder="N° SIRET">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-password" for="ape">Site Web</label>
                    <input class="full-width has-padding has-border" id="ape" name="ape" type="texte" placeholder="Code APE">
                </p>
                <p>
                    Informations personnelles
                </p>

                <p class="fieldset">
                    <label class="image-replace cd-password" for="nom">Nom</label>
                    <input class="full-width has-padding has-border" id="nom" name="nom" type="text" placeholder="Nom">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-password" for="prenom">Prénom</label>
                    <input class="full-width has-padding has-border" id="prenom" name="prenom" type="text" placeholder="Prénom">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-password" for="pass1">Mot de passe</label>
                    <input class="full-width has-padding has-border" id="pass1" name="pass1" type="password" placeholder="Créer un mot de passe">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-password" for="pass2">Resaisissez votre mot de passe</label>
                    <input class="full-width has-padding has-border" id="pass2" name="pass2" type="password" placeholder="Resaisissez votre mot de passe">
                </p>
                <p class="fieldset">
                <div class="g-recaptcha" data-sitekey="6Ley5ScTAAAAAD3Khq87gMp2w0I7e8AN3cWQrNi1"></div>
                </p>
                <div class="alert fade in alert-danger error_register" style="display: none;" id="">
                </div>
                <p class="fieldset">
                    <button class="btn btn-border btn-blue" type="submit" value="Create account">Inscrivez-vous</button>
                </p>
            </form>
        </div>
        <!-- cd-signup -->
    </div>
    <!-- cd-user-modal-container -->
</div>
<!-- cd-user-modal -->
<!-- ===== End of Login Pop Up div ===== -->



<!-- ===== All Javascript at the bottom of the page for faster page loading ===== -->

<script src="landing/js/bootstrap.min.js"></script>
<script src="landing/js/modernizr.min.js"></script>
<script src="landing/js/wow.min.js"></script>
<script src="landing/js/swiper.min.js"></script>
<script src="landing/js/owl.carousel.min.js"></script>
<script src="landing/js/simple-expand.min.js"></script>
<script src="landing/js/jquery.countTo.js"></script>
<script src="landing/js/jquery.inview.min.js"></script>
<script src="landing/js/jquery.easing.min.js"></script>
<script src="landing/js/jquery.nav.js"></script>
<script src="landing/js/jquery.ajaxchimp.js"></script>
<script src="landing/js/jquery-ui.min.js"></script>
<script src="landing/js/custom.js"></script>

<!-- css3-mediaqueries.js for IE8 or older -->
<!--[if lt IE 9]>
<script src="landing/js/respond.min.js"></script>
<![endif]-->
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->


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



    $('#register_form').submit(function(e){



        if($('#societe').val() == '')
        {
            $('#societe').css('border-color', 'red');
            $('.error_register').html("Merci de saisir le nom de la societé");
            $('.error_register').show('slow');
        }
        else {
            if($('#mail_societe').val() == '')
            {
                $('#mail_societe').css('border-color', 'red');
                $('.error_register').html("Merci de saisir l'adresse mail de la societé");
                $('.error_register').show('slow');
            }
            else {
                if($('#tel').val() == '')
                {
                    $('#tel').css('border-color', 'red');
                    $('.error_register').html("Merci de saisir le téléphone de la societé");
                    $('.error_register').show('slow');
                }
                else
                {
                    if($('#adresse').val() == '')
                    {
                        $('#adresse').css('border-color', 'red');
                        $('.error_register').html("Merci de saisir l'adresse de la societé");
                        $('.error_register').show('slow');
                    }
                    else
                    {
                        if($('#cp').val() == '')
                        {
                            $('#cp').css('border-color', 'red');
                            $('.error_register').html("Merci de saisir le code postal de la societé");
                            $('.error_register').show('slow');
                        }
                        else
                        {
                            if($('#ville').val() == '')
                            {
                                $('#ville').css('border-color', 'red');
                                $('.error_register').html("Merci de saisir la ville de la societé");
                                $('.error_register').show('slow');
                            }
                            else
                            {
                                if($('#siret').val() == '')
                                {
                                    $('#siret').css('border-color', 'red');
                                    $('.error_register').html("Merci de saisir le N°SIRET de la societé");
                                    $('.error_register').show('slow');
                                }
                                else
                                {
                                    if($('#ape').val() == '')
                                    {
                                        $('#ape').css('border-color', 'red');
                                        $('.error_register').html("Merci de saisir le code APE de la societé");
                                        $('.error_register').show('slow');
                                    }
                                    else
                                    {
                                        if($('#nom').val() == '')
                                        {
                                            $('#nom').css('border-color', 'red');
                                            $('.error_register').html("Merci de saisir votre nom");
                                            $('.error_register').show('slow');
                                        }
                                        else
                                        {
                                            if($('#prenom').val() == '')
                                            {
                                                $('#prenom').css('border-color', 'red');
                                                $('.error_register').html("Merci de saisir votre prénom");
                                                $('.error_register').show('slow');
                                            }
                                            else
                                            {
                                                if($('#pass1').val() == '')
                                                {
                                                    $('#pass1').css('border-color', 'red');
                                                    $('.error_register').html("Merci de saisir votre mot de passe");
                                                    $('.error_register').show('slow');
                                                }
                                                else
                                                {
                                                    if($('#pass2').val() == '')
                                                    {
                                                        $('#pass2').css('border-color', 'red');
                                                        $('.error_register').html("Merci de resaisir votre mot de passe");
                                                        $('.error_register').show('slow');
                                                    }
                                                    else
                                                    {
                                                        if($('#pass1').val() != $('#pass2').val())
                                                        {
                                                            $('#pass1').css('border-color', 'red');
                                                            $('#pass2').css('border-color', 'red');
                                                            $('.error_register').html("Les deux mots de passe ne sont pas identiques");
                                                            $('.error_register').show('slow');
                                                        }
                                                        else
                                                        {
                                                            $('.error_register').hide('slow');
                                                            $('#register_form').submit();

                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }



        e.preventDefault();
    });

    function retourClient() {
        if(klient.responseText == 'true')
        {
            $('#error_text').hide();
            $('#error_text').html('Redirection en cours');
            $('#error_text').show('slow');
            $(location).attr('href', './dashboard');
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


    function inscription() {

        var $form_modal = $('.cd-user-modal'),
            $form_login = $form_modal.find('#cd-login'),
            $form_signup = $form_modal.find('#cd-signup'),
            $form_modal_tab = $('.cd-switcher'),
            $tab_login = $form_modal_tab.children('li').eq(0).children('a'),
            $tab_signup = $form_modal_tab.children('li').eq(1).children('a');

        $form_login.removeClass('is-selected');
        $form_signup.addClass('is-selected');
        $tab_login.removeClass('selected');
        $tab_signup.addClass('selected');
        $form_modal.addClass('is-visible');


    }

</script>

</body>

</html>