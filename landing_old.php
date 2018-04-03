<?php session_start();
if($_SESSION['nb_erreur']) {
    for ($i = 1; $i <= $_SESSION['nb_erreur']; $i++)
        echo $_SESSION['erreur'][$i] . ' <br />';

    $_SESSION['nb_erreur'] = 0;
    $_SESSION['erreur'] = '';

    die();
}

?>
<!doctype html>
<!--[if IE 7 ]>    <html lang="en-gb" class="isie ie7 oldie no-js"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en-gb" class="isie ie8 oldie no-js"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en-gb" class="isie ie9 no-js"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="fr_FR" class="no-js"> <!--<![endif]-->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!--[if lt IE 9]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <![endif]-->

    <title> Managy.fr - Le logiciel en ligne de gestion d'interventions  </title>

    <meta name="description" content="Logiciel de gestion d'intervention pour les réparateurs !">
    <meta name="author" content="Managy.fr">
    <meta property="og:image" content="https://www.managy.fr/landing/images/revolution/616x521.png" />
    <meta property="og:url" content="http://www.managy.fr#inscription"/>

    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!--[if lte IE 8]>
    <script type="text/javascript" src="http://explorercanvas.googlecode.com/svn/trunk/excanvas.js"></script>
    <![endif]-->

    <!-- **Favicon** -->
    <link href="./landing/favicon.ico" rel="shortcut icon" type="image/x-icon" />

    <!-- **CSS - stylesheets** -->
    <link id="default-css" href="./landing/style.css" rel="stylesheet" media="all" />
    <link id="shortcodes-css" href="./landing/css/shortcode.css" rel="stylesheet" media="all" />
    <link href="/landing/css/meanmenu.css" rel="stylesheet" type="text/css" media="all" />
    <link id="skin-css" href="./landing/skins/skyblue/style.css" rel="stylesheet" media="all" />

    <link href="./landing/css/responsive.css" rel="stylesheet" type="text/css" />

    <!-- **Animation stylesheets** -->
    <link href="./landing/css/animations.css" rel="stylesheet" type="text/css" />
    <link href="./landing/css/isotope.css" rel="stylesheet" type="text/css" media="all" />
    <link href="./landing/css/prettyPhoto.css" rel="stylesheet" type="text/css" media="all" />

    <!-- **Font Awesome** -->
    <link href="./landing/css/font-awesome.min.css" rel="stylesheet" type="text/css" />

    <!--[if IE 7]>
    <link rel="stylesheet" href="./landing/css/font-awesome-ie7.min.css" />
    <![endif]-->

    <!-- **Google - Fonts** -->
    <link href='http://fonts.googleapis.com/css?family=Titillium+Web:400,300,600' rel='stylesheet' type='text/css' />
    <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,300italic,400italic,600' rel='stylesheet' type='text/css' />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />

    <!-- SLIDER STYLES STARTS -->
    <link rel="stylesheet" type="text/css" href="./landing/js/revolution/settings.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="./landing/js/layerslider/layerslider.css" media="screen">
    <!-- SLIDER STYLES ENDS -->

    <!-- **jQuery** -->
    <script src="./landing/js/modernizr-2.6.2.min.js"></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<body>
<div class="wrapper">
    <div class="inner-wrapper">
        <!-- Header div Starts here -->
        <header id="header">
            <div class="container">
                <div id="logo">
                    <a href="landing_old.php"> <img src="./landing/images/logo.png" alt="" title=""> </a>
                </div>
                <div id="menu-container">
                    <nav id="main-menu">
                        <ul class="group">
                            <li class="menu-item current_page_item"><a href="#home">Accueil</a></li>
                            <li class="menu-item"><a href="#services">Services</a></li>
                            <li class="menu-item"><a href="#features">Fonctionnalités</a></li>
                            <li class="menu-item"><a href="#pricing">Tarifs</a></li>
                            <li class="menu-item"><a href="#contact">Contact</a></li>
                            <li class="external-link"><a href="./login.html" onclick="$(location).attr('href', './login.html');">Connexion</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </header>
        <!-- Header div Ends here -->
        <div id="main">
            <!-- home section Starts here -->
            <section id="home" class="content">
                <div class="fullwidthbanner-container banner">
                    <div class="fullwidthbanner">
                        <ul>

                            <li data-transition="random" data-slotamount="7" data-masterspeed="300">
                                <img src="./landing/images/revolution/1920x570.png"  alt="slider-bg2">
                                <div class="tp-caption lfb"
                                     data-x="0"
                                     data-y="48"
                                     data-speed="1000"
                                     data-start="1000"
                                     data-easing="easeOutExpo"><img src="./landing/images/revolution/616x521.png" alt="Image 1">
                                </div>

                                <div class="tp-caption custom_title lft"
                                     data-x="650"
                                     data-y="120"
                                     data-speed="1000"
                                     data-start="1500"
                                     data-easing="easeOutExpo">Fonctionnalités de Managy
                                </div>

                                <div class="tp-caption custom_contenttext sfr"
                                     data-x="677"
                                     data-y="182"                     data-speed="1000"
                                     data-start="2000"
                                     data-easing="easeOutExpo">Entièrement responsive
                                </div>

                                <div class="tp-caption fade"
                                     data-x="650"
                                     data-y="188"
                                     data-speed="1000"
                                     data-start="2000"
                                     data-easing="easeInCubic"><img src="./landing/images/revolution/tick.png" alt="Image 4">
                                </div>

                                <div class="tp-caption fade"
                                     data-x="650"
                                     data-y="228"
                                     data-speed="1000"
                                     data-start="2500"
                                     data-easing="easeInQuad"><img src="./landing/images/revolution/tick.png" alt="Image 4">
                                </div>

                                <div class="tp-caption custom_contenttext sfr"
                                     data-x="677"
                                     data-y="222"
                                     data-speed="1000"
                                     data-start="2500"
                                     data-easing="easeOutExpo">Développé pour tous les réparateurs
                                </div>

                                <div class="tp-caption fade"
                                     data-x="650"
                                     data-y="268"
                                     data-speed="1000"
                                     data-start="2800"
                                     data-easing="easeInQuad"><img src="./landing/images/revolution/tick.png" alt="Image 4">
                                </div>

                                <div class="tp-caption custom_contenttext sfr"
                                     data-x="677"
                                     data-y="262"
                                     data-speed="1000"
                                     data-start="3100"
                                     data-easing="easeOutExpo">Adaptable selon vos métiers
                                </div>

                                <div class="tp-caption fade"
                                     data-x="650"
                                     data-y="308"
                                     data-speed="1000"
                                     data-start="3400"
                                     data-easing="easeInQuad"><img src="./landing/images/revolution/tick.png" alt="Image 4">
                                </div>

                                <div class="tp-caption custom_contenttext sfr"
                                     data-x="677"
                                     data-y="302"
                                     data-speed="1000"
                                     data-start="3700"
                                     data-easing="easeOutExpo">Multi-utilisateurs
                                </div>

                                <div class="tp-caption fade"
                                     data-x="650"
                                     data-y="348"
                                     data-speed="1000"
                                     data-start="4000"
                                     data-easing="easeInQuad"><img src="./landing/images/revolution/tick.png" alt="Image 4">
                                </div>

                                <div class="tp-caption custom_contenttext sfr"
                                     data-x="677"
                                     data-y="342"
                                     data-speed="1000"
                                     data-start="4300"
                                     data-easing="easeOutExpo">Sécurisation en ligne des données
                                </div>

                                <div class="tp-caption sft"
                                     data-x="430"
                                     data-y="58"
                                     data-speed="300"
                                     data-start="1300"
                                     data-easing="easeInOutBounce"><img src="./landing/images/revolution/bulb.png" alt="Image 15">
                                </div>

                            </li>
                            <li data-transition="random" data-slotamount="7" data-masterspeed="300" data-delay="10000" >
                                <img src="./landing//images/revolution/1920x570.png"  alt="slider-bg"  data-fullwidthcentering="on">
                                <div class="tp-caption lfl"
                                     data-x="-6"
                                     data-y="67"
                                     data-speed="1000"
                                     data-start="500"
                                     data-easing="easeOutExpo"> <img src="./landing/images/revolution/601x397.png" alt="Image 1">
                                </div>

                                <div class="tp-caption custom_title lft"
                                     data-x="560"
                                     data-y="58"
                                     data-speed="1000"
                                     data-start="2500"
                                     data-easing="easeOutExpo">Managy,  <br>
                                    votre gestionnaire d'interventions.
                                </div>

                                <div class="tp-caption custom_subtitle sft"
                                     data-x="560"
                                     data-y="182"
                                     data-speed="1000"
                                     data-start="3000"
                                     data-easing="easeOutExpo">Entièrement en ligne
                                </div>

                                <div class="tp-caption custom_subtitle sft"
                                     data-x="560"
                                     data-y="278"
                                     data-speed="1000"
                                     data-start="4500"
                                     data-easing="easeOutExpo">Rapide & Simple <br>de prise en main
                                </div>

                                <div class="tp-caption custom_subtitle sft"
                                     data-x="560"
                                     data-y="374"
                                     data-speed="1000"
                                     data-start="6000"
                                     data-easing="easeOutExpo">Sans investissement
                                </div>

                                <!--<div class="tp-caption sfl"
                                     data-x="560"
                                     data-y="438"
                                     data-speed="1000"
                                     data-start="6500"
                                     data-easing="easeOutExpo"><img src="./landing/images/revolution/star3.png" alt="Image 6">
                                </div>-->

                            </li>

                            <li data-transition="random" data-slotamount="7" data-masterspeed="300">
                                <img src="./landing/images/revolution/1920x570.png"  alt="slider-bg">
                                <div class="tp-caption lfr"
                                     data-x="360"
                                     data-y="105"
                                     data-speed="1000"
                                     data-start="500"
                                     data-easing="easeOutExpo"><img src="./landing/images/revolution/596x467.png" alt="Responsive">
                                </div>

                                <div class="tp-caption lfb"
                                     data-x="255"
                                     data-y="246"
                                     data-speed="1000"
                                     data-start="2000"
                                     data-easing="easeOutExpo"><img src="./landing/images/revolution/288x325.png" alt="Image 2">
                                </div>

                                <div class="tp-caption custom_title2 lft"
                                     data-x="0"
                                     data-y="166"
                                     data-speed="1000"
                                     data-start="1500"
                                     data-easing="easeOutExpo">Envie d'un logiciel accessible ?
                                </div>

                                <div class="tp-caption custom_content sfl"
                                     data-x="0"
                                     data-y="224"
                                     data-speed="1000"
                                     data-start="2500"
                                     data-easing="easeOutExpo">C'est toute la puissance de Managy. <br> Vous aurez la possibilité de travailler <br>de n'importe où, n'importe quand.
                                </div>

                            </li>

                        </ul>

                    </div>

                </div>
                <div class="shadow"></div>
                <div class="container">
                    <div class="aligncenter welcome">
                        <div class="margin35"></div>
                        <h1>Managy, le premier gestionnaire d'interventions<br /> pour les réparateurs</h1>
                        <p>Vous êtes responsable d'une entreprise spécialisée dans la réparation atelier votre solution de gestion de vos interventions n'est pas adaptée ou trop chère ? Avec Managy, vous avez enfin trouvé le logiciel entièrement accessible par internet, multi-utilisateurs, qu'il vous faut. Conçu par un dépanneur informaticien, il répondra à tous les besoins de gestion d'interventions de ce domaine.</p>
                        <div class="margin20"></div>
                        <a href="#inscription" onclick="" class="button small animate" data-animation="bounceIn" data-delay="100">Inscrivez-vous maintenant et bénéficiez d'un mois gratuit !<span class="icon-caret-right"></span></a>
                    </div>
                    <div style="display: none;" id="register">

                    </div>
                </div>


            </section>
            <!-- home section Ends here -->
            <!-- inscription -->
            <section id="inscription" class="content">
                <div class="main-title">
                    <div class="container">
                        <h2>Inscription</h2>
                    </div>
                </div>
                <div class="content-main">
                    <div class="container">
                        <h2>Inscrivez-vous dès maintenant et bénéficiez d'un mois gratuit !</h2>
                        <form method="post" action="./register.php" name="inscription_form" id="inscription_form" class="contact-frm">
                            <div class="margin35"></div>
                            <h3>Informations société : </h3>
                            <input type="text" name="societe" required placeholder="Nom de votre société"/><br />
                            <input type="email" name="mail_societe" required placeholder="E-mail de contact"/><br />
                            <input type="text" name="web" placeholder="Site web"/><br />
                            <input type="tel" name="tel" required placeholder="Numéro de téléphone"/><br />
                            <input type="text" name="adresse" required placeholder="Adresse"/><br />
                            <input type="text" name="cp" required placeholder="Code postal"/><br />
                            <input type="text" name="ville" required placeholder="Ville" /><br />
                            <input type="text" name="siret" required placeholder="Numéro SIRET"/><br />
                            <input type="text" name="ape" required placeholder="Code APE"><br />
                            <hr />
                            <h3>Informations personnelles :</h3>
                            <input type="text" name="nom" required placeholder="Votre nom"><br />
                            <input type="text" name="prenom" required placeholder="Votre prénom"><br />
                            <input type="password" name="pass1" required placeholder="Créez un mot de passe"/><br />
                            <input type="password" name="pass2" required placeholder="Resaisissez votre mot de passe"/><br />
                            <div class="g-recaptcha" data-sitekey="6Ley5ScTAAAAAD3Khq87gMp2w0I7e8AN3cWQrNi1"></div>
                            <input type="submit" class="button" value="Inscription avec un mois gratuit" name="btnsend">
                        </form>
                        <div id="ajax_contact_msg"></div>
                    </div>
                </div>
            </section>
            <!-- fin inscription -->
            <!-- services section Starts here -->
            <section id="services" class="content">
                <div class="main-title">
                    <div class="container">
                        <h2>Services</h2>
                    </div>
                </div>
                <div class="content-main">
                    <div class="container">
                        <div class="one-fourth column no-space">
                            <div class="service">
                                <i class="icon-laptop animate" data-animation="rollIn" data-delay="100"></i>
                                <div class="margin20"></div>
                                <h4>Interface professionnelle</h4>
                                <p>Profitez de notre interface intuitive pour organiser vos commandes, vos rendez-vous, vos interventions. La prise en main est facile est rapide. Tout est prévu pour vous simplifier la vie.</p>
                            </div>
                        </div>
                        <div class="one-fourth column no-space">
                            <div class="service">
                                <i class="icon-mobile-phone special animate" data-animation="rollIn" data-delay="300"></i>
                                <div class="margin20"></div>
                                <h4>Responsive</h4>
                                <p>Notre ERP fonctionne sur n'importe quel support. Rien à installer sur votre PC, il sera accessible de n'importe où. Responsive, il saura s'adapter à la perfection à vos tablettes et smartphones.</p>
                            </div>
                        </div>
                        <div class="one-fourth column no-space">
                            <div class="service">
                                <i class="icon-magic special animate" data-animation="rollIn" data-delay="500"></i>
                                <div class="margin20"></div>
                                <h4>Evolution constante</h4>
                                <p>Outil en évolution permanente, vous profiterez des dernières mises à jour gratuitement et automatiquement. Les nouvelles fonctionnalités fleurirons au fil de votre expérience sur Managy.<br />&nbsp;</p>
                            </div>
                        </div>
                        <div class="one-fourth column no-space last">
                            <div class="service">
                                <i class="icon-comments special animate" data-animation="rollIn" data-delay="700"></i>
                                <div class="margin20"></div>
                                <h4>Support dédié</h4>
                                <p>Notre support dédié est là pour vous écouter et vous accompagner dans l'utilisation de managy. Vous pourrez nous contacter par mail ou par téléphone à tout instant en cas de problème.</p>
                            </div>
                        </div>

                        <div class="margin50"></div>
                        <!--<h2 class="border-title">What do we provide?<span></span></h2>-->
                        <!--<div class="margin15"></div>-->
                        <!--<div class="tabs-vertical-container">-->
                        <!--<ul class="tabs-vertical-frame one-third column">-->
                        <!--<li><a href="#"> <span> 1</span> Flexible App </a></li>-->
                        <!--<li><a href="#"> <span> 2</span> Powerful Admin Panel </a></li>-->
                        <!--<li><a href="#"> <span> 3</span> Best Inbuilt features </a></li>-->
                        <!--</ul>-->
                        <!--<div class="tabs-vertical-frame-content two-third column last">-->
                        <!--<h3> Flexible App </h3>-->
                        <!--<p>Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. </p>-->
                        <!--<p> Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.  </p>-->
                        <!--</div>-->
                        <!--<div class="tabs-vertical-frame-content two-third column last">-->
                        <!--<h3> Powerful Admin Panel </h3>-->
                        <!--<p> Morbi vel elit magna, at laoreet eros. Nullam non lectus fringilla nisl tincidunt pellentesque. Vivamus odio velit, laoreet ac molestie nec, sodales at quam. Nullam pellentesque tristique tristique.</p>-->
                        <!--<p> Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.</p>-->
                        <!--</div>-->
                        <!--<div class="tabs-vertical-frame-content two-third column last">-->
                        <!--<h3> Best Inbuilt features </h3>-->
                        <!--<p> Pellentesque augue lacus, porta vel ultricies vitae, ullamcorper eget purus. Aliquam risus mauris, suscipit eget scelerisque in, dapibus at mauris. </p>-->
                        <!--<p> Ut euismod dapibus tempor. Nulla metus metus, interdum et fringilla eget, rutrum facilisis ante. Nullam convallis metus elit, ac commodo est. </p>-->
                        <!--</div>-->
                        <!--</div>-->
                    </div>
                </div>
            </section>
            <!-- services section Ends here -->
            <!-- features section Starts here -->
            <section id="features" class="content">
                <div class="main-title">
                    <div class="container">
                        <h2>Fonctionnalités</h2>
                    </div>
                </div>
                <div class="content-main">
                    <div class="container">
                        <div class="one-half column">
                            <img src="./landing/images/mobile-view-map.png" alt="mobilemap" title="" class="aligncenter rollImage animate" data-animation="fadeInLeft">
                        </div>
                        <div class="one-half column last">
                            <div class="custom-services">
                                <span class="icon-one animate" data-animation="bounceIn"></span>
                                <h3>Evolution permanante</h3>
                                <p>Managy version 3 est mis à jour continuellement ! De nouvelles fonctionnalités fleurissent au fil du temps... Ces mises à jour sont gratuites !</p>
                            </div>
                            <div class="margin35"></div>
                            <div class="custom-services">
                                <span class="icon-eight animate" data-animation="bounceIn"></span>
                                <h3> Totalement Responsive </h3>
                                <p>Managy 3 sera capable de s'intégrer parfaitement à chacune de vos plateformes. Pc de bureau, tablettes ou smartphones.</p>
                            </div>
                            <div class="margin35"></div>
                            <div class="custom-services">
                                <span class="icon-nine animate" data-animation="bounceIn"></span>
                                <h3> Adaptables </h3>
                                <p>Managy est d'abord prévu pour les réparateurs. Mais d'autres sociétés spécialisées dans d'autre domaines font partie de nos clients ! N'hestez pas à nous contacter pour adapter Managy à vos besoins !</p>
                            </div>
                        </div>
                        <div class="margin65"></div>
                        <div class="one-half column pull-up">
                            <div class="custom-services">
                                <span class="icon-two animate" data-animation="bounceIn"></span>
                                <h3>Statistiques techniciens</h3>
                                <p>Une page dédiée aux statistiques vous permet d'évaluer le rendement de chacun de vos techiniciens grâce au compteur de temps.</p>
                            </div>
                            <div class="margin35"></div>
                            <div class="custom-services">
                                <span class="icon-three animate" data-animation="bounceIn"></span>
                                <h3>Intuitivité</h3>
                                <p>Notre logiciel a été conçu pour être le plus facile d'utilisation possible. Toute votre équipe appréciera l'expérience ressentie avec manangy.</p>
                            </div>
                            <div class="margin35"></div>
                            <div class="custom-services">
                                <span class="icon-four animate" data-animation="bounceIn"></span>
                                <h3>Chat intégré</h3>
                                <p>Dans la version 3 de Managy, la mise en place d'un chat interne vous permet de dialoguer en temps réel avec vos collaborateurs.</p>
                            </div>
                        </div>
                        <div class="one-half column last">
                            <img src="./landing/images/mobile-two.png" alt="mobiletwo" title="" class="aligncenter fadeImage animate" data-animation="fadeInRight">
                        </div>

                    </div>
                </div>
            </section>
            <!-- features section Ends here -->
            <!-- pricing section Starts here -->
            <section id="pricing" class="content">
                <div class="main-title">
                    <div class="container">
                        <h2>Tarifs</h2>
                    </div>
                </div>
                <div class="content-main">
                    <div class="container">
                        <div class="one-third column">
                            <div class="pr-tb-col active animate" data-animation="flipInY">
                                <div class="tb-header">
                                    <div class="tb-title">
                                        <h5>Informaticiens</h5>
                                    </div>
                                    <div class="price"> 25 €<span>(Ht) Par mois + 2 collaborateurs</span> </div>
                                    <div class="guarantee">
                                        <p>1 mois d'essai gratuit !</p>
                                    </div>
                                </div>
                                <ul class="tb-content">
                                    <li>Nombre d'interventions illimité</li>
                                    <li>Gestion des commandes</li>
                                    <li>Gestion des sous-traitances</li>
                                    <li>Calendrier des rendez-vous avec synchro Mobile iOs</li>
                                    <li>Envoie de mails prédefinis</li>
                                    <li>Envoie de sms prédefinis (sms payants)</li>
                                    <li>Gestion des privilèges pour chacun de vos collaborateurs</li>
                                    <li>Gestion des statistiques / heures techiniciens</li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
            </section>
            <!-- pricing section Ends here -->
            <!-- about section Starts here -->

            <!-- about section Ends here -->
            <!-- screenshot section Starts here -->

            <!-- screenshot section Ends here -->
            <!-- contact section Starts here -->
            <section id="contact" class="content">
                <div class="main-title">
                    <div class="container">
                        <h2>Contact</h2>
                    </div>
                </div>
                <div class="content-main">
                    <div class="container">
                        <div class="location">
                            <h4 class="map-title">Localisation de la société Dépan'Info 67, gérante de Managy.fr</h4>

                            <div id="map"> </div>

                            <div class="margin55"></div>
                            <div class="contact-info">
                                <div class="one-half column">
                                    <h4>Managy - Sous filliale de <a href="http://www.depaninfo67.com/" about="_blank">Dépan'Info 67</a></h4>
                                    <p>Heilmann Cyril<br>32, rue Principale<br>67870 Bischoffsheim</p>
                                    <p>06.87.47.00.91</p>
                                    <p>Utilisez le bouton "contactez-nous" en bas à droite de la page.</p>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- contact section Ends here -->
            <footer>

                <div class="copyright">
                    <div class="container">
                        <p>&copy; 2016 Dépan'Info 67</p>

                    </div>
                </div>
            </footer>
        </div>


    </div>
</div><!-- Wrapper End -->

<!-- Java Scripts -->
<script type="text/javascript" src="./landing/js/jquery-1.10.2.min.js"></script>

<script type="text/javascript" src="./landing/js/jquery.scrollTo.js"></script>
<script type="text/javascript" src="./landing/js/jquery.inview.js"></script>

<script type="text/javascript" src="./landing/js/jquery.nav.js"></script>
<script type="text/javascript" src="./landing/js/jquery-menu.js"></script>
<script type="text/javascript" src="./landing/js/jquery.meanmenu.min.js"></script>

<script type="text/javascript" src="./landing/js/jquery.quovolver.min.js"></script>

<script type="text/javascript" src="./landing/js/jquery.donutchart.js"></script>

<script type="text/javascript" src="./landing/js/jquery.isotope.min.js"></script>

<script type="text/javascript" src="./landing/js/jquery.prettyPhoto.js"></script>

<script type="text/javascript" src="./landing/js/jquery.validate.min.js"></script>

<script type="text/javascript" src="./landing/js/jquery.tabs.min.js"></script>

<script type="text/javascript" src="./landing/js/jquery.nicescroll.min.js"></script>

<!-- Layer Slider Starts -->
<script src="./landing/js/layerslider/jquery-easing-1.3.js" type="text/javascript"></script>
<script src="./landing/js/layerslider/jquery-transit-modified.js" type="text/javascript"></script>
<script src="./landing/js/layerslider/layerslider.transitions.js" type="text/javascript"></script>
<script src="./landing/js/layerslider/layerslider.kreaturamedia.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
    jQuery(document).ready(function($){
        $('#layerslider').layerSlider({
            skinsPath : 'js/layerslider/skins/',
            skin : 'borderlessdark3d',
            width : '940px',
            height : '500px',
            responsive : true,
            thumbnailNavigation : 'hover',
            showCircleTimer : false,
            navPrevNext  : true,
            navButtons   : true,
            hoverPrevNext: true
        });
    });
</script>
<!-- Layer Slider Ends -->

<!-- Revolution Slider Starts -->
<script src="/landing/js/revolution/jquery.themepunch.revolution.min.js" type="text/javascript"></script>
<script src="/landing/js/inscription.js" type="text/javascript"></script>
<div style="background: #FF0000"></div>

<script type="text/javascript">
    jQuery(document).ready(function($){

       /* $('#inscription_form').submit(function(e){
            inscription();
           e.preventDefault();
        });*/



        if($.fn.cssOriginal != undefined)
            $.fn.css = $.fn.cssOriginal;

        var api = $('.fullwidthbanner').revolution(
                {
                    delay:9000,
                    startwidth:940,
                    startheight:570,

                    onHoverStop:"on",                       // Stop Banner Timet at Hover on Slide on/off

                    thumbWidth:100,                         // Thumb With and Height and Amount (only if navigation Tyope set to thumb !)
                    thumbHeight:50,
                    thumbAmount:3,

                    hideThumbs:200,
                    navigationType:"none",              // bullet, thumb, none
                    navigationArrows:"solo",                // nexttobullets, solo (old name verticalcentered), none

                    navigationStyle:"round",                // round,square,navbar,round-old,square-old,navbar-old, or any from the list in the docu (choose between 50+ different item), custom

                    navigationHAlign:"center",              // Vertical Align top,center,bottom
                    navigationVAlign:"bottom",                  // Horizontal Align left,center,right
                    navigationHOffset:30,
                    navigationVOffset:-40,

                    soloArrowLeftHalign:"left",
                    soloArrowLeftValign:"center",
                    soloArrowLeftHOffset:20,
                    soloArrowLeftVOffset:0,

                    soloArrowRightHalign:"right",
                    soloArrowRightValign:"center",
                    soloArrowRightHOffset:20,
                    soloArrowRightVOffset:0,

                    touchenabled:"on",                      // Enable Swipe Function : on/off

                    stopAtSlide:-1,                         // Stop Timer if Slide "x" has been Reached. If stopAfterLoops set to 0, then it stops already in the first Loop at slide X which defined. -1 means do not stop at any slide. stopAfterLoops has no sinn in this case.
                    stopAfterLoops:-1,                      // Stop Timer if All slides has been played "x" times. IT will stop at THe slide which is defined via stopAtSlide:x, if set to -1 slide never stop automatic

                    hideCaptionAtLimit:0,                   // It Defines if a caption should be shown under a Screen Resolution ( Basod on The Width of Browser)
                    hideAllCaptionAtLilmit:0,               // Hide all The Captions if Width of Browser is less then this value
                    hideSliderAtLimit:0,                    // Hide the whole slider, and stop also functions if Width of Browser is less than this value

                    fullWidth:"on",

                    shadow:0                                //0 = no Shadow, 1,2,3 = 3 Different Art of Shadows -  (No Shadow in Fullwidth Version !)
                });
    });
</script>
<!-- Revolution Slider Ends -->

<!-- **To Top** -->
<script src="./landing/js/jquery.ui.totop.min.js"></script>

<!-- **Contact Map** -->
<script src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script src="./landing/js/jquery.gmap.min.js"></script>

<script type="text/javascript" src="./landing/js/custom.js"></script>

<script type="application/javascript" src="https://www.sellsy.com/?_f=snippet&hash=JUY0JThFJTg0LW0lMDIlQzUlOEIlOTBEJTExJUU1JUMyJTg4JTg5JTI4SCVFMSVEMSU5QyU4QXlyJTI1JUYwJUY5JUIxayVGMiUxNSUxQSUyNyVDMyVGNSU4QyU5NSUwQyVFRlElQTMlRTMlQkQ2JTFFJUM3JUYzZSU2MCU4MCUwM0pCJUE3JTg1JTlGJTYwbyVGNCUwOCVGMTclRTZaJTlE"></script>

</body>
</html>