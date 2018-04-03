<?php session_start();
if(is_file('./moduls/login/langs/fr.inc'))
    include ('./moduls/login/langs/fr.inc');
    $_SESSION['id'] = '';
    $_SESSION['prenom'] = '';
    $_SESSION['nom'] = '';
    $_SESSION['mail'] = '';
    $_SESSION['compte_principale'] = '';
    $_SESSION['template'] = '';
    $_SESSION['gerant'] = '';
    ?>
<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->

<!-- Consider adding a manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="fr"> <!--<![endif]-->
<head>
    <meta charset="utf-8">

    <link rel="dns-prefetch" href="http://fonts.googleapis.com" />
    <link rel="dns-prefetch" href="http://themes.googleusercontent.com" />

    <link rel="dns-prefetch" href="http://ajax.googleapis.com" />
    <link rel="dns-prefetch" href="http://cdnjs.cloudflare.com" />
    <link rel="dns-prefetch" href="http://agorbatchev.typepad.com" />

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
       More info: h5bp.com/b/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title><?php echo LOGIN_TITLE;?></title>
    <meta name="description" content="Managy.fr est un ERP gestionnaire d'interventions adapté pour tout corps de métier nécessitant une gestion performantes des interventions internes ou externe. Il est tout à faire approprié pour une boutique de réparation informatique.">
    <meta name="author" content="Cyril HEILMANN - Dépan'Info 67">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">
    <!-- iPhone: Don't render numbers as call links -->
    <meta name="format-detection" content="telephone=no">

    <link rel="shortcut icon" href="favicon.ico" />
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->
    <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->







    <!-- The Styles -->
    <!-- ---------- -->

    <!-- Layout Styles -->
    <link rel="stylesheet" href="./templates/mango/css/style.css">
    <link rel="stylesheet" href="./templates/mango/css/grid.css">
    <link rel="stylesheet" href="./templates/mango/css/layout.css">

    <!-- Icon Styles -->
    <link rel="stylesheet" href="./templates/mango/css/icons.css">
    <link rel="stylesheet" href="./templates/mango/css/fonts/font-awesome.css">
    <!--[if IE 8]><link rel="stylesheet" href="css/fonts/font-awesome-ie7.css"><![endif]-->

    <!-- External Styles -->
    <link rel="stylesheet" href="./templates/mango/css/external/jquery-ui-1.9.1.custom.css">
    <link rel="stylesheet" href="./templates/mango/css/external/jquery.chosen.css">
    <link rel="stylesheet" href="./templates/mango/css/external/jquery.cleditor.css">
    <link rel="stylesheet" href="./templates/mango/css/external/jquery.colorpicker.css">
    <link rel="stylesheet" href="./templates/mango/css/external/jquery.elfinder.css">
    <link rel="stylesheet" href="./templates/mango/css/external/jquery.fancybox.css">
    <link rel="stylesheet" href="./templates/mango/css/external/jquery.jgrowl.css">
    <link rel="stylesheet" href="./templates/mango/css/external/jquery.plupload.queue.css">
    <link rel="stylesheet" href="./templates/mango/css/external/syntaxhighlighter/shCore.css" />
    <link rel="stylesheet" href="./templates/mango/css/external/syntaxhighlighter/shThemeDefault.css" />

    <!-- Elements -->
    <link rel="stylesheet" href="./templates/mango/css/elements.css">
    <link rel="stylesheet" href="./templates/mango/css/forms.css">

    <!-- OPTIONAL: Print Stylesheet for Invoice -->
    <link rel="stylesheet" href="./templates/mango/css/print-invoice.css">

    <!-- Typographics -->
    <link rel="stylesheet" href="./templates/mango/css/typographics.css">

    <!-- Responsive Design -->
    <link rel="stylesheet" href="./templates/mango/css/media-queries.css">

    <!-- Bad IE Styles -->
    <link rel="stylesheet" href="./templates/mango/css/ie-fixes.css">







    <!-- The Scripts -->
    <!-- ----------- -->

    <!-- JavaScript at the top (will be cached by browser) -->


    <!-- Grab frameworks from CDNs -->
        <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.js"></script>
    <script>window.jQuery || document.write('<script src="js/libs/jquery-1.8.2.js"><\/script>')</script>

        <!-- Do the same with jQuery UI -->
    <script src="http://code.jquery.com/ui/1.9.1/jquery-ui.js"></script>
    <script>window.jQuery.ui || document.write('<script src="js/libs/jquery-ui-1.9.1.js"><\/script>')</script>

        <!-- Do the same with Lo-Dash.js -->
    <!--[if gt IE 8]><!-->
    <script src="http://cdnjs.cloudflare.com/ajax/libs/lodash.js/0.8.2/lodash.js"></script>
    <script>window._ || document.write('<script src="js/libs/lo-dash.js"><\/script>')</script>
    <!--<![endif]-->
    <!-- IE8 doesn't like lodash -->
    <!--[if lt IE 9]><script src="http://documentcloud.github.com/underscore/underscore.js"></script><![endif]-->

    <!-- Do the same with require.js -->
    <script src="http://cdnjs.cloudflare.com/ajax/libs/require.js/2.0.6/require.js"></script>
    <script>window.require || document.write('<script src="js/libs/require-2.0.6.min.js"><\/script>')</script>


    <!-- Load Webfont loader -->
    <script type="text/javascript">
        window.WebFontConfig = {
            google: { families: [ 'PT Sans:400,700' ] },
            active: function(){ $(window).trigger('fontsloaded') }
        };
    </script>
    <script defer async src="https://ajax.googleapis.com/ajax/libs/webfont/1.0.28/webfont.js"></script>

    <!-- Essential polyfills -->
    <script src="./templates/mango/js/mylibs/polyfills/modernizr-2.6.1.min.js"></script>
    <script src="./templates/mango/js/mylibs/polyfills/respond.js"></script>
    <script src="./templates/mango/js/mylibs/polyfills/matchmedia.js"></script>
    <!--[if lt IE 9]><script src="./templates/mango/js/mylibs/polyfills/selectivizr.js"></script><![endif]-->
    <!--[if lt IE 10]><script src="./templates/mango/js/mylibs/polyfills/excanvas.js"></script><![endif]-->
    <!--[if lt IE 10]><script src="./templates/mango/js/mylibs/polyfills/classlist.js"></script><![endif]-->


    <!-- scripts concatenated and minified via build script -->

    <!-- Scripts required everywhere -->
    <script src="./templates/mango/js/mylibs/jquery.hashchange.js"></script>
    <script src="./templates/mango/js/mylibs/jquery.idle-timer.js"></script>
    <script src="./templates/mango/js/mylibs/jquery.plusplus.js"></script>
    <script src="./templates/mango/js/mylibs/jquery.scrollTo.js"></script>
    <script src="./templates/mango/js/mylibs/jquery.ui.touch-punch.js"></script>
    <script src="./templates/mango/js/mylibs/jquery.ui.multiaccordion.js"></script>
    <script src="./templates/mango/js/mylibs/number-functions.js"></script>
    <script src="./templates/mango/js/mylibs/fullstats/jquery.css-transform.js"></script>
    <script src="./templates/mango/js/mylibs/fullstats/jquery.animate-css-rotate-scale.js"></script>
    <script src="./templates/mango/js/mylibs/forms/jquery.validate.js"></script>

    <!-- Do not touch! -->
    <script src="./templates/mango/js/mango.js"></script>
    <script src="./templates/mango/js/plugins.js"></script>
    <script src="./templates/mango/js/script.js"></script>

    <!-- Your custom JS goes here -->
    <script src="./templates/mango/js/app.js"></script>

    <!-- end scripts -->

</head>

<body class=login onload="document.getElementById('login_name').focus()"> 

	<!-- Some dialogs etc. -->

	<!-- The loading box -->
	<div id="loading-overlay"></div>
	<div id="loading">
		<span><?php echo LOGIN_LOADING;?></span>
	</div>
	<!-- End of loading box -->

	<!--------------------------------->
	<!-- Now, the page itself begins -->
	<!--------------------------------->

	<!-- The toolbar at the top -->
	<section id="toolbar">
		<div class="container_12">

			<!-- Left side -->
			<div class="left">
				<ul class="breadcrumb">

					<li><a href="javascript:void(0);">Managy</a></li>
					<li><a href="javascript:void(0);"><?php echo LOGIN_LOGIN;?></a></li>

				</ul>
			</div>
			<!-- End of .left -->

			<!-- Right side --//
			<div class="right">
				<ul>

					<li><a href="./dashboard"><span class="icon i14_bended-arrow-left"></span>Back to Dashboard</a></li>

					<li class="red"><a href="#">Purchase</a></li>

				</ul>
			</div><!-- End of .right -->

			<!-- Phone only items --//
			<div class="phone">

				<!-- User Link --//
				<li><a href="#"><span class="icon icon-home"></span></a></li>
				<!-- Navigation --//
				<li><a href="#"><span class="icon icon-heart"></span></a></li>

			</div><!-- End of .phone -->

		</div><!-- End of .container_12 -->
	</section><!-- End of #toolbar -->

	<!-- The header containing the logo -->
	<header class="container_12">

		<div class="container">

			<!-- Your logos -->
			<a href="/tf-mango/"><img src="./templates/mango/img/logo-light.png" alt="Managy" width="260" height="67"></a>
			<a class="phone-title" href="login.html"><img src="./templates/mango/img/logo-mobile.png" alt="Managy" height="22" width="70" /></a>

			<!-- Right link --//
			<div class="right">
				<span>Got no account?</span>
				<a href="javascript:void(0);">Register</a>
			</div>

		</div><!-- End of .container -->

	</header><!-- End of header -->

	<!-- The container of the sidebar and content box -->
	<section id="login" class="container_12 clearfix">
                                <?php
                                    if($_GET['content'] != '')
                                        $action = './'.$_GET['content'];
                                    else
                                        $action = './dashboard';																			if($_GET['scontent'] != '')                                        $action .= '-'.$_GET['scontent'];
                                ?>
		<form action="<?php echo $action;?>" method="post" class="box validate">

			<div class="header">
				<h2><span class="icon icon-lock"></span><?php echo LOGIN_LOGIN;?></h2>
			</div>

			<div class="content">

				<!-- Login messages -->
				<div class="login-messages">
					<div class="message welcome"><?php echo LOGIN_WELCOME;?></div>
					<div class="message failure"><?php echo LOGIN_INVALIDS_CREDENTIALS;?></div>
				</div>
                                

				<!-- The form -->
				<div class="form-box">

					<div class="row">
						<label for="login_name">
							<strong><?php echo LOGIN_PSEUDO;?></strong>
						</label>
						<div>
							<input tabindex=1 type="text" class="required" name=login_name id=login_name />
						</div>
					</div>

					<div class="row">
						<label for="login_pw">
							<strong><?php echo LOGIN_PASSWORD;?></strong>
						</label>
						<div>
							<input tabindex=2 type="password" class="required" name=login_pw id=login_pw />
						</div>
					</div>

				</div><!-- End of .form-box -->

			</div><!-- End of .content -->

			<div class="actions">
				<div class="left">
					<div class="rememberme">
						<input tabindex=4 type="checkbox" name="login_remember" id="login_remember" checked /><label for="login_remember"><?php echo LOGIN_REMEMBER_ME;?></label>
					</div>
				</div>
				<div class="right">
					<input tabindex=3 type="submit" value="<?php echo LOGIN_LOGIN;?>" name="login_btn" />
				</div>
			</div><!-- End of .actions -->

		</form><!-- End of form -->

	</section>

	<!-- Spawn $$.loaded -->
	<script>
		$$.loaded();
	</script>
    <script>
        $('#login').find('form').validationOptions({
            rules: {
                "login_pw": {
                    remote: {
                        url: "ajax/login.php",  // ATTENTION: Credentials sent as plain text, if you're not using HTTPS!
                        type: "post",
                        data: {
                            login_name: function() {
                                return $('#login_name').val();
                            }
                        }
                    }
                }
            },
            messages: {
                "login_pw": {
                    remote: "Username/password are wrong."
                }
            }
        });
    </script>

	<!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
	   chromium.org/developers/how-tos/chrome-frame-getting-started -->
	<!--[if lt IE 7 ]>
	<script defer src="http://ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
	<script defer>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
	<![endif]-->

</body>
</html>
