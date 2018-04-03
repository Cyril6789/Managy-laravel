<?php session_start();

//include('./classes/mysql.class.php');
require_once ('./templates/melon/classes/includes.php');
//include('./getFile.php');

$id_base_inter = $db->SQLFix($id_base_inter);

$db->Query('SELECT id_inter, i.compte_principal, matos, panne, resolution, materiel_ajoute, message_client, c.id AS id_client, prenom, nom, time_cloture, time_ouverture, cp.cal, type_atelier_rdv
            FROM interventions AS i 
            INNER JOIN clients AS c
            ON (i.id_client = c.id)
            AND (c.compte_principal = i.compte_principal)
            INNER JOIN comptes_principaux AS cp
            ON (i.compte_principal = cp.id)
            WHERE i.id ="'.$id_base_inter.'" ');

$row = $db->Row();
$id_inter = $row->id_inter;
$nom = $row->nom;
$prenom = $row->prenom;
$id_client = $row->id_client;
$time_cloture = $row->time_cloture;
$time_ouverture = $row->time_ouverture;
$matos = $row->matos;
$panne = $row->panne;
$resolution = $row->resolution;
$message_client = $row->message_client;
$materiel_ajoute = $row->materiel_ajoute;
$compte_principal = $row->compte_principal;
$cal = $row->cal;
$tis = $row->type_atelier_rdv;

$dir = './../files_managy/'.sha1($cal).'/i/'.$id_inter.'/public';




$db->Query('SELECT id_presta, designation, duree FROM prestations_effectuees WHERE id_inter = "'.$id_inter.'" AND compte_principal="'.$compte_principal.'"');

$i=0;
$tab_prestations_effectuees = array();
while($row = $db->Row())
{
    $tab_prestations_effectuees[$i]['id'] = $row->id_presta;
    $tab_prestations_effectuees[$i]['designation'] = $row->designation;
    $tab_prestations_effectuees[$i]['duree'] = $row->duree;
    $i++;
}

//Commandes
$db->Query('SELECT date_reception FROM commandes WHERE compte_principal="'.$compte_principal.'" AND id_inter="'.$id_inter.'" AND cde_recue = "0" ORDER BY date_reception DESC LIMIT 1');

$row=$db->Row();
if($row->date_reception)
    $cde_date_reception = date('d/m/Y', $row->date_reception);
else
    $cde_date_reception = '';

//Sous-traitance
$db->Query('SELECT date_retour FROM sous_traitances WHERE compte_principal="'.$compte_principal.'" AND id_inter="'.$id_inter.'" AND retour = "0" ORDER BY date_retour DESC LIMIT 1');
$row=$db->Row();
if($row->date_retour)
    $sst_date_reception = date('d/m/Y', $row->date_retour);
else
    $sst_date_reception = '';




/*
 * notification
 */

if(!$_SESSION['id']) {
    
    $notif = new notificationMail(1, $compte_principal);
    $notif->tab_parse(Array('%id_inter%' => $id_inter, '%client%' => $id_client));
    $notif->sendMail();
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title>Intervention N°<?php echo $id_inter;?></title>

    <!--=== CSS ===-->

    <!-- Bootstrap -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

    <!-- jQuery UI -->
    <!--<link href="plugins/jquery-ui/jquery-ui-1.10.2.custom.css" rel="stylesheet" type="text/css" />-->
    <!--[if lt IE 9]>
    <link rel="stylesheet" type="text/css" href="plugins/jquery-ui/jquery.ui.1.10.2.ie.css"/>
    <![endif]-->

    <!-- Theme -->
    <link href="assets/css/main.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/plugins.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/responsive.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.css" rel="stylesheet" type="text/css" />

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
    <script type="text/javascript" src="plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js"></script>

    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="assets/js/libs/lodash.compat.min.js"></script>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="assets/js/libs/html5shiv.js"></script>
    <![endif]-->

    <!-- Smartphone Touch Events -->
    <script type="text/javascript" src="plugins/touchpunch/jquery.ui.touch-punch.min.js"></script>
    <script type="text/javascript" src="plugins/event.swipe/jquery.event.move.js"></script>
    <script type="text/javascript" src="plugins/event.swipe/jquery.event.swipe.js"></script>

    <!-- General -->
    <script type="text/javascript" src="assets/js/libs/breakpoints.js"></script>
    <script type="text/javascript" src="plugins/respond/respond.min.js"></script> <!-- Polyfill for min/max-width CSS3 Media Queries (only for IE8) -->
    <script type="text/javascript" src="plugins/cookie/jquery.cookie.min.js"></script>
    <script type="text/javascript" src="plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script type="text/javascript" src="plugins/slimscroll/jquery.slimscroll.horizontal.min.js"></script>

    <!-- Page specific plugins -->
    <!-- Charts -->
    <!--[if lt IE 9]>
    <script type="text/javascript" src="plugins/flot/excanvas.min.js"></script>
    <![endif]-->
    <script type="text/javascript" src="plugins/sparkline/jquery.sparkline.min.js"></script>
    <script type="text/javascript" src="plugins/flot/jquery.flot.min.js"></script>
    <script type="text/javascript" src="plugins/flot/jquery.flot.tooltip.min.js"></script>
    <script type="text/javascript" src="plugins/flot/jquery.flot.resize.min.js"></script>
    <script type="text/javascript" src="plugins/flot/jquery.flot.time.min.js"></script>
    <script type="text/javascript" src="plugins/flot/jquery.flot.growraf.min.js"></script>
    <script type="text/javascript" src="plugins/easy-pie-chart/jquery.easy-pie-chart.min.js"></script>

    <script type="text/javascript" src="plugins/daterangepicker/moment.min.js"></script>
    <script type="text/javascript" src="plugins/daterangepicker/daterangepicker.js"></script>
    <script type="text/javascript" src="plugins/blockui/jquery.blockUI.min.js"></script>

    <script type="text/javascript" src="plugins/fullcalendar/fullcalendar.min.js"></script>

    <!-- Noty -->
    <script type="text/javascript" src="plugins/noty/jquery.noty.js"></script>
    <script type="text/javascript" src="plugins/noty/layouts/top.js"></script>
    <script type="text/javascript" src="plugins/noty/themes/default.js"></script>

    <!-- Forms -->
    <script type="text/javascript" src="plugins/uniform/jquery.uniform.min.js"></script>
    <script type="text/javascript" src="plugins/select2/select2.min.js"></script>

    <!-- App -->
    <script type="text/javascript" src="assets/js/app.js"></script>
    <script type="text/javascript" src="assets/js/plugins.js"></script>
    <script type="text/javascript" src="assets/js/plugins.form-components.js"></script>

    <script>
        $(document).ready(function(){
            "use strict";

            App.init(); // Init layout and core plugins
            Plugins.init(); // Init all plugins
            FormComponents.init(); // Init all form-specific plugins
        });
    </script>

    <!-- Demo JS -->
    <script type="text/javascript" src="assets/js/custom.js"></script>
    <script type="text/javascript" src="assets/js/demo/pages_calendar.js"></script>
    <script type="text/javascript" src="assets/js/demo/charts/chart_filled_blue.js"></script>
    <script type="text/javascript" src="assets/js/demo/charts/chart_simple.js"></script>
</head>

<body class="theme-dark">

<!-- Header -->
<header class="header navbar navbar-fixed-top" role="banner">
    <!-- Top Navigation Bar -->
    <div class="container">

        <!-- Only visible on smartphones, menu toggle -->
        <ul class="nav navbar-nav">
            <li class="nav-toggle"><a href="javascript:void(0);" title=""><i class="fa fa-reorder"></i></a></li>
        </ul>

        <!-- Logo -->
        <a class="navbar-brand" href="">
            MANAGY
        </a>
        <!-- /logo -->

        <!-- Sidebar Toggler -->
        <a href="#" class="toggle-sidebar bs-tooltip" data-placement="bottom" data-original-title="Toggle navigation">
            <i class="fa fa-reorder"></i>
        </a>
        <!-- /Sidebar Toggler -->

        <!-- Top Left Menu -->
        <ul class="nav navbar-nav navbar-left hidden-xs hidden-sm">

        </ul>
        <!-- /Top Left Menu -->

        <!-- Top Right Menu -->
        <ul class="nav navbar-nav navbar-right">
            <!-- Notifications -->

            <!-- User Login Dropdown -->
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <span><?php echo $prenom.' '.$nom;?></span>
                    <i class="fa fa-caret-down small"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="./dashboard"><i class="fa fa-key"></i> Déconnexion</a></li>
                </ul>
            </li>
            <!-- /user login dropdown -->
        </ul>
        <!-- /Top Right Menu -->
    </div>
    <!-- /top navigation bar -->

    <!--=== Project Switcher ===-->

</header> <!-- /.header -->

<div id="container">
    <div id="sidebar" class="sidebar-fixed">
        <div id="sidebar-content">

            <!-- Search Input -->




            <!--=== Navigation ===-->
            <ul id="nav">
                <li class="current">
                    <a href="">
                        <i class="fa fa-dashboard"></i>
                        Accueil
                    </a>
                </li>

            </ul>

            <!-- /Navigation -->

        </div>
        <div id="divider" class=""></div>
    </div>
    <!-- /Sidebar -->

    <div id="content">
        <div class="container">
            <!-- Breadcrumbs line -->
            <div class="crumbs">
                <ul id="breadcrumbs" class="breadcrumb">
                    <li>
                        <i class="fa fa-home"></i>
                        <a href="index.html">Accueil</a>
                    </li>
                    <li class="current">
                        <a href="" title="">Intervention N°<?php echo $id_inter;?></a>
                    </li>
                </ul>


            </div>
            <!-- /Breadcrumbs line -->

            <!--=== Page Header ===-->
            <div class="page-header">
                <div class="page-title">
                    <h3>Intervention N°<?php echo $id_inter;?></h3>
                    <span>Bonjour <?php if($prenom) echo $prenom; else echo $nom;?> !</span>
                </div>

                <ul class="page-stats">
                    <li>
                        <div class="summary">
                            <span>Fiche intervention</span>
                            <h3><a href="javascript:void();" onclick="window.open('./fiche-<?php echo $_GET['content'];?>', 'inscr','width=700,height=680,left=100,top=10,scrollbars=yes');" >Télécharger <img src="./img/icons/packs/fugue/24x24/blue-document-export.png"/></a></h3>
                        </div>
                        <!-- Use instead of sparkline e.g. this:
                        <div class="graph circular-chart" data-percent="73">73%</div>
                        -->
                    </li>
                </ul>

            </div>
            <!-- /Page Header -->

            <!--=== Page Content ===-->
            <!--=== Statboxes ===-->



            <?php

            if($time_cloture)
                $alert = new Success('Votre intervention est clôturée depuis le '.date('d/m/Y', $time_cloture).' !', false);
            else
                $alert = new Warning('Votre intervention est ouverte depuis le '.date('d/m/Y H\hi', $time_ouverture), false);

            echo $alert;

            if($cde_date_reception)
            {
                $commande = new Info('Une ou plusieurs commandes sont en attente de réception pour votre intervention. Date de réception estimée : le ' . $cde_date_reception, false);
                echo $commande;
            }

            if($sst_date_reception)
            {
                $sous_traitance = new Info('Une ou plusieurs sous-traitances sont en cours pour votre intervention. Date de retour estimée : le '.  $sst_date_reception, false);
                echo $sous_traitance;
            }

            $widget_materiel = new WidgetBox('Le matériel que vous nous avez déposé');
            $widget_materiel->setContent(nl2br($matos));

            $widget_panne = new WidgetBox('Panne signalée, travaux demandés');
            $widget_panne->setContent(nl2br($panne));

            $widget_reso = new WidgetBox('Rapport technicien');
            $widget_reso->setContent(nl2br($resolution));

            $widget_message = new WidgetBox('Message à votre attention');
            $widget_message->setContent(nl2br($message_client));

            $widget_donnees_inter = new WidgetBox('Données de l\'intervention');


            $col_gauche = new Col(6, 'md');
            $col_gauche->setContent($widget_panne.$widget_reso);

            $col_droite = new Col(6, 'md');
            $col_droite->setContent($widget_materiel.$widget_message);


            $widget_donnees_inter->setContent($col_gauche.$col_droite, false);

            $widget_donnees_facturables = new WidgetBox('Données facturables', 12);

            $table_presta = new HtmlTable();
            $table_presta->addTSection('thead');
            $table_presta->addRow();
            $table_presta->addCell('Prestations effectuées', '', 'thead');
            $table_presta->addCell('Durée', '', 'thead');
            $table_presta->addTSection('tbody');

            foreach($tab_prestations_effectuees as $presta)
            {
                $table_presta->addRow();
                $table_presta->addCell($presta['designation']);
                $table_presta->addCell($presta['duree'].' h');
            }

            $col_tab_prestas = new Col(6, 'md');
            $col_tab_prestas->setContent($table_presta);

            $widget_materiel_ajoute = new WidgetBox('Matériel ajouté ou remplacé', 6);
            $widget_materiel_ajoute->setContent($materiel_ajoute);


            $widget_donnees_facturables->setContent($col_tab_prestas.$widget_materiel_ajoute, false);

            $largeur = 12;

            if(is_dir($dir))
            {
                $widget_files = new WidgetBox('Fichiers à votre disposition');

                $files = scandir($dir);

                $table_files = new HtmlTable();
                $table_files->addTSection('thead');
                $table_files->addRow();
                $table_files->addCell('Nom du fichier');
                $table_files->addCell('Taille');
                $table_files->addTSection('tbody');

                foreach ($files AS $f)
                {
                    if($f != '.' AND $f != '..' AND !is_dir($dir.'/'.$f))
                    {
                        $table_files->addRow();
                        $table_files->addCell('<a href="./getFile.php?f='.$f.'&i='.$id_inter.'&c='.sha1($cal).'&p='.filesize($dir.'/'.$f).'&b='.$_GET['content'].'">'.$f.'</a>');
                        $table_files->addCell(round(filesize($dir.'/'.$f) / 1024) .' Ko');
                    }
                }

                $widget_files->setContent($table_files, false);

                $largeur = 8;

            }



            $col_gauche = new Col($largeur);
            $col_gauche->setContent($widget_donnees_inter.$widget_donnees_facturables);

            echo $col_gauche;

            if(is_object($widget_files)) {
                $col_droite = new Col(4);
                $col_droite->setContent($widget_files);
                echo $col_droite;
            }


            ?>

            <!-- /Page Content -->
        </div>
        <!-- /.container -->

    </div>
</div>

</body>
</html>