<?php session_start();
if($_SESSION['nb_erreur']) {
    for ($i = 1; $i <= $_SESSION['nb_erreur']; $i++)
        echo $_SESSION['erreur'][$i] . ' <br />';

    $_SESSION['nb_erreur'] = 0;
    $_SESSION['erreur'] = '';

    die();
}
include('./landing/includes/head.php');
?>



<!-- ===== Main Section ===== -->
<section class="main" id="home">

    <!-- Demo 3 -->
    <div class="demo3">

        <!-- Main Content -->
        <div class="demo-content container">
            <div class="col-md-12">
                <h2><span>Managy.fr</span><br />L'outil de gestion dédié aux <br>techniciens <span>informatiques</span></h2>
                <p>Conçu pour que vous ne perdiez plus une seconde dans la gestion de vos interventions.</p>
                <div>
                    <a href="javascript:void();" class="btn btn-border btn-blue" onclick="inscription();"><i class="fa fa-star"></i>Lancez-vous vite avec un mois d'essai gratuit !</a>
                    <a href="#pricing" class="btn btn-border-rev btn-green">Plus d'informations</a>
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
            <h2>Un nouveau départ avec Managy !</h2>
            <p>Managy est le premier gestionnaire d'interventions pour les réparateurs en informatique.<br />Développé par un informaticien depuis <?php echo date('Y') - 2010 ;?> ans, découvrez l'outil qui vous accompagnera dans toutes vos interventions, en atelier et sur site.</p>
            <a href="javascript:void();" class="btn btn-border btn-blue" onclick="inscription();">N'attendez plus et profitez maintenant d'un mois d'essai gratuit !</a>
        </div>

        <!-- About Image -->
        <div class="col-md-6 about-image">
            <img src="landing/images/landing.png" alt="">
            <br /><br />
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
            <h2>Les clés de votre réussite</h2>
            <p>10 miutes de prise en main maintenant suffisent à vous faciliter le futur de votre activité et vous faire gagner en productivité. Tout est prévu pour vous simplifier la vie. Profitez de notre interfcace intuitive pour organiser vos interventions, vos rendez-vous, vos commandes.</p>
        </div>

        <!-- 1st Row of Service Section -->
        <div class="row">
            <div class="col-sm-4 col-xs-12 service">
                <img src="landing/images/icons/cloud-server.svg" alt="">
                <h3>Stockage illimité</h3>
                <p>Une fois inscrit, vous n'avez plus aucune limite. Profitez de toute la puissance de Managy pour organiser vos interventions.</p>
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

    </div>

    <!-- Start of Info 1 -->
    <div class="container-fluid main-content info">
        <div class="container">
            <div class="col-md-6 info-text">
                <h3>Evolution permanente</h3>
                <p>Managy est mis à jour continuellement ! De nouvelles fonctionnalités fleurissent au fil du temps... Ces mises à jour sont gratuites !</p>
                <ul>
                    <li>Logiciel réellement adapté aux informaticiens</li>
                    <li>Nouvelles fonctionnalités régulières</li>
                    <li>Dévelloppeurs à l'écoute de vos suggestions</li>
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
                <h3>Avec vous jusque dans votre poche</h3>
                <p>Managy s'intègre parfaitement à chacune de vos plateformes : ordinateurs de bureau, tablettes ou smartphones, gardez l'oeil sur votre activité (voire vos équipes) à chaque instant.</p>
                <ul>
                    <li>100% responsive</li>
                    <li>Intuitif et ergonomique</li>
                    <li>Fonctionne partout</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- End of Info 2 -->

    <!-- Start of Info 3 -- /////>
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
        <div class="pricing-table1" style="text-align: center;">
            <!-- start of 1st price table -->
            <div class="col-sm-12 pricing-plan" id="basic">
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
                    <li><i class="fa fa-check"></i> 2 collaborateurs inclus, 5€ par utilisateur au-delà</li>
                    <li><i class="fa fa-check"></i> Interventions illimitées + gestion coordonnées clients + base de données d'interventions</li>
                    <li><i class="fa fa-check"></i> Gestion des commandes et des sous-traitances</li>
                    <li><i class="fa fa-check"></i> Calendrier des rendez-vous avec synchronisation iOs et Android</li>
                    <li><i class="fa fa-check"></i> Envoie de mails prédefinis de suivi pour le client</li>
                    <li><i class="fa fa-check"></i> Envoie de SMS prédefinis (à partir de 9ct/sms)</li>
                    <li><i class="fa fa-check"></i> Statistiques d'activité : interventions et techniciens</li>
                    <li><i class="fa fa-check"></i> Accès multi-utilisateurs avec gestion des comptes</li>
                </ul>
                <a href="javascript:void();" class="btn btn-border btn-blue" onclick="inscription();">Augmentez tout de suite votre productivité !</a>
                <!-- end of detail section -->
            </div>
            <!-- end of 1st price table -->


        </div>
    </div>
</section>
<!-- ===== End of Pricing ===== -->

    <!-- ===== Start of Testimonial ===== --///>
    <section class="pt80 pb40" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2> Testimonials </h2>
                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</p>
            </div>

            <!-- Start of Owl Slider --///>
            <div class="owl-carousel testimonial">

                <!-- Start of Slide item --///>
                <div class="item">
                    <div class="review">
                        <div class="review-inner text-center">
                            <blockquote>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text. </blockquote>
                            <img src="/landing/images/clouds/cloud-blue.svg" alt="">
                        </div>
                    </div>
                    <div class="customer">
                        <h3 class="uppercase">customer</h3>
                        <span>Web Developer</span>
                    </div>
                </div>
                <!-- End Slide item --//>


            </div>
            <!-- End of Owl Slider --//>
        </div>
    </section>
    <!-- ===== End of Testimonial ===== -->


<?php
include('./landing/includes/foot.php');
?>