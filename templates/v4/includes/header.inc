<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 26/09/2016
 * Time: 11:13
 */
require_once('./templates/v4/classes/includes.php');
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="fr">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title><?php echo $page_title;?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="Managy.fr le premier logiciel de gestion d'intervention réellement adapté pour les informaticiens" name="description" />
    <meta content="Cyril Heilmann" name="author" />

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="./templates/v4/assets/global/css/components.min.css" rel="stylesheet" id="style_components" type="text/css" />
    <link href="./templates/v4/assets/global/css/plugins.min.css" rel="stylesheet" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->

    <link href="./templates/v4/assets/global/plugins/bootstrap-toastr/toastr.min.css" rel="stylesheet" type="text/css" />

    <link href="./templates/v4/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/clockface/css/clockface.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/typeahead/typeahead.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/apps/css/perso.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/bootstrap-markdown/css/bootstrap-markdown.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/bootstrap-summernote/summernote.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/global/plugins/pattern-lock/pattern-lock.css" rel="stylesheet" type="text/css" />

    <!-- BEGIN THEME LAYOUT STYLES -->
    <link href="./templates/v4/assets/layouts/layout/css/layout.css" rel="stylesheet" type="text/css" />
    <link href="./templates/v4/assets/layouts/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color" />
    <link href="./templates/v4/assets/layouts/layout/css/custom.css" rel="stylesheet" type="text/css" />
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


<script type="text/javascript" src="./templates/v4/assets/js/libs/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="./templates/v4/assets/plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js"></script>

    <script src="./templates/v4/assets/global/plugins/pattern-lock/pattern-lock.js" type="text/javascript"></script>
    <script src="./templates/v4/assets/global/plugins/pattern-lock/patternLockScript.js" type="text/javascript"></script>



<!-- END HEAD -->

<body class="page-header-fixed  page-content-white <?php if(SIDEBAR_CLOSED != 'false') echo 'page-sidebar-closed'?> page-sidebar-fixed">
    <div class="page-wrapper">