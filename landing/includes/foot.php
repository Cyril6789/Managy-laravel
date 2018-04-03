

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
                        <li><i class="fa fa-envelope"></i><a href="mailto:contact@managy.fr?subject=Questions concernant votre logiciel Managy.fr&body=Bonjour, je viens de voir votre logiciel Managy et augmenter ma productivité m'interesse beaucoup, mais j'ai encore une ou l'autre question à vous poser avec de passer à l'action : ">contact@managy.fr</a></li>
                        <li><i class="fa fa-map-marker"></i>32 rue principale 67870 Bischoffsheim</li>
                    </ul>
                    <div class="payment">
                        <h4>Paiement</h4>
                        <ul>
                            <li><i class="fa fa-cc-paypal"></i></li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-3">
                    <h4>Vie du site</h4>
                    <ul class="footer-links nopadding mt30">
                        <li><a href="./cgu.html">CGU</a></li>
                    </ul>
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
            <form class="cd-form" id="form_login" method="post" action="./login.html">
                <div class="alert fade in alert-danger" style="display: none;" id="error_text">
                </div>
                <p class="fieldset">
                    <label class="image-replace cd-email" for="signin-email">Identifiant</label>
                    <input class="full-width has-padding has-border" id="username" name="username" type="text" placeholder="Identifiant">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-password" for="signin-password">Mot de passe</label>
                    <input class="full-width has-padding has-border" id="password" name="password" type="password" placeholder="Mot de passe">
                </p>
                <p class="fieldset" style="text-align: center;">
                    <a href="./forgot.html">Mot de passe oublié ?</a>
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
                <p class="fieldset">
                    En vous inscrivant, bénéficiez d'un mois d'essai gratuit !
                </p>
                <p>Informations société</p>
                <p class="fieldset">
                    <label class="image-replace cd-username" for="societe">Societé</label>
                    <input class="full-width has-padding has-border" id="societe" name="societe" type="text" placeholder="Nom de votre société">
                </p>
                <p class="fieldset">
                    <label class="image-replace cd-email" for="mail_societe">E-mail société</label>
                    <input class="full-width has-padding has-border" id="mail_societe" name="mail_societe" type="email" placeholder="E-mail de la société">
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
                    <button class="btn btn-border btn-blue" type="submit" value="Create account">Inscrivez-vous  et bénéficiez d'un mois d'essai gratuit !</button>
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

   /* $('#form_login').submit(function(e){

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
*/


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
                if($('#nom').val() == '')
                {
                    $('#nom').css('border-color', 'red');
                    $('.error_register').html("Merci de saisir votre nom");
                    $('.error_register').show('slow');
                }
                else {
                    if ($('#prenom').val() == '') {
                        $('#prenom').css('border-color', 'red');
                        $('.error_register').html("Merci de saisir votre prénom");
                        $('.error_register').show('slow');
                    }
                    else {
                        if ($('#pass1').val() == '') {
                            $('#pass1').css('border-color', 'red');
                            $('.error_register').html("Merci de saisir votre mot de passe");
                            $('.error_register').show('slow');
                        }
                        else {
                            if ($('#pass2').val() == '') {
                                $('#pass2').css('border-color', 'red');
                                $('.error_register').html("Merci de resaisir votre mot de passe");
                                $('.error_register').show('slow');
                            }
                            else {
                                if ($('#pass1').val() != $('#pass2').val()) {
                                    $('#pass1').css('border-color', 'red');
                                    $('#pass2').css('border-color', 'red');
                                    $('.error_register').html("Les deux mots de passe ne sont pas identiques");
                                    $('.error_register').show('slow');
                                }
                                else {
                                    $('.error_register').hide('slow');
                                    $('#register_form').submit();

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