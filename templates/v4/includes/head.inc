<!-- BEGIN HEADER -->
<div class="page-header navbar navbar-fixed-top">
    <!-- BEGIN HEADER INNER -->
    <div class="page-header-inner ">
        <!-- BEGIN LOGO -->
        <div class="page-logo">
            <a href="./dashboard">
                <img src="./templates/v4/img/logo-light.png" width="100px" alt="logo" class="logo-default" />  </a>
            <div class="menu-toggler sidebar-toggler">
                <span></span>
            </div>
        </div>


        <!-- END LOGO -->
        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
            <span></span>
        </a>
        <!-- END RESPONSIVE MENU TOGGLER -->
        <!-- BEGIN TOP NAVIGATION MENU -->
        <div class="top-menu">
            <ul class="nav navbar-nav pull-left">
                <?php
                $modal_create_inter = new modalAjax('intervention_informatique', 'create');


                $inter_create = new DropdownMenu('plus-circle', '', '', $modal_create_inter->getHref());
                echo $inter_create;

                /*
                 * Commandes en cours
                 */
                if(right('commandes', 6) AND (AccesActivableModul('intervention_informatique')))
                {
                    $commandes_menu = new DropdownMenu('truck', $nombre_inter_cde_retard);

                    $commandes_menu->setTitle('<span class="bold">'.(!($nb_cdes)? '0': $nb_cdes).' commande'. ($nb_cdes > 1 ? 's' : '').'</span> en cours', './intervention-commandes');

                    foreach($tab_cdes AS $cde)
                    {
                        if($cde['date_reception_t'] < time())
                        {
                            $label = 'danger';
                            $icon = 'exclamation-triangle';
                        }
                        else
                        {
                            $label = 'success';
                            $icon = 'truck';
                        }

                        $commandes_menu->addLine($cde['fournisseur'].'<br />'.$cde['titre'].' '.$cde['nom'].' '.$cde['prenom'], $icon, 'N°'.$cde['prefix'].$cde['id_inter'], './i'.$cde['id_inter'], $label);

                    }


                    echo $commandes_menu;
                }

                /*
                 * sous-traitance en cours
                 */
                if(right('soustraitances', 6) AND (AccesActivableModul('intervention_informatique')))
                {
                    $sst = new DropdownMenu('wrench', $nombre_inter_sst_retard);

                    $sst->setTitle('<span class="bold">'.(!($nombre_inter_sst_retard)? '0': $nb_cdes).' sous-traitance'. ($nb_cdes > 1 ? 's' : '').'</span> en cours', './intervention-sous_traitances');

                    foreach($tab_sstts AS $sstt)
                    {
                        if($sstt['date_retour_t'] < time())
                        {
                            $label = 'danger';
                            $icon = 'exclamation-triangle';
                        }
                        else
                        {
                            $label = 'success';
                            $icon = 'wrench';
                        }

                        $sst->addLine($sstt['nom_st'].'<br />'.$sstt['titre'].' '.$sstt['nom'].' '.$sstt['prenom'], $icon, 'N°'.$sstt['prefix'].$sstt['id_inter'], './i'.$sstt['id_inter'], $label);

                    }


                    echo $sst;
                }

                /*
                 * Status
                 *
                 */
                if(AccesActivableModul('intervention_informatique')) {
                    $statut = new DropdownMenu('bookmark', $nb_inter_busy, 'default');
                    $statut->setTitle('Statut des interventions');
                    foreach ($tab_inter_occupees AS $tao)
                        $statut->addLine($tao['titre'] . ' ' . $tao['nom'] . ' ' . $tao['prenom'], 'bookmark', $tao['statut'] . '<br />Intervention N°' . $tao['id'], './i' . $tao['id']);

                    echo $statut;
                }


                /*
                 * Commandes rapides
                 */

                if(AccesActivableModul('intervention_informatique') AND $_SESSION['compte_principal'] == '15' OR $_SESSION['compte_principal'] == '8' OR $_SESSION['compte_principal'] == '1')
                {

                    $add_fast_order = new modalAjax('intervention_informatique', 'add_fast_order');
                    //$add_fast_order->setDebug();


                    $fast_order = new DropdownMenu('cart-arrow-down', '0', 'default');
                    $fast_order->setTitle('Commandes rapides', $add_fast_order->getHref(), new Font('cart-plus').' Ajouter une commande rapide');

                    $orders = new DataObject('commandes_rapides');
                    $orders->addWhere('recu',  0);
                    $tab_orders = $orders->findAll();


                    foreach ($tab_orders AS $or) {
                        $client = new GetInfosCustomer($or->id_client);
                        if($or->date_reception < time())
                            $label = 'danger';
                        else
                            $label = 'success';
                        $fast_order->addLine($or->titre.'<br />'.$client->getFullName(), 'shopping-cart', 'Réception prévue le '.date('d/m/Y', $or->date_reception), '#', $label);
                    }

                    echo $fast_order;
                }




                ?>





            </ul>




            <ul class="nav navbar-nav pull-right">

                <!-- BEGIN NOTIFICATION DROPDOWN -->
                <!-- DOC: Apply "dropdown-dark" class after "dropdown-extended" to change the dropdown styte -->
                <!-- DOC: Apply "dropdown-hoverable" class after below "dropdown" and remove data-toggle="dropdown" data-hover="dropdown" data-close-others="true" attributes to enable hover dropdown mode -->
                <!-- DOC: Remove "dropdown-hoverable" and add data-toggle="dropdown" data-hover="dropdown" data-close-others="true" attributes to the below A element with dropdown-toggle class -- ////>
                <li class="dropdown dropdown-extended dropdown-notification" id="header_notification_bar">

                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <i class="icon-bell"></i>
                        <span class="badge badge-default"> 7 </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="external">
                            <h3>
                                <span class="bold">12 pending</span> notifications</h3>
                            <a href="page_user_profile_1.html">view all</a>
                        </li>
                        <li>
                            <ul class="dropdown-menu-list scroller" style="height: 250px;" data-handle-color="#637283">
                                <li>
                                    <a href="javascript:;">
                                        <span class="time">just now</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-success">
                                                            <i class="fa fa-plus"></i>
                                                        </span> New user registered. </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                        <span class="time">3 mins</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-danger">
                                                            <i class="fa fa-bolt"></i>
                                                        </span> Server #12 overloaded. </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                        <span class="time">10 mins</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-warning">
                                                            <i class="fa fa-bell-o"></i>
                                                        </span> Server #2 not responding. </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                        <span class="time">14 hrs</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-info">
                                                            <i class="fa fa-bullhorn"></i>
                                                        </span> Application error. </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                        <span class="time">2 days</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-danger">
                                                            <i class="fa fa-bolt"></i>
                                                        </span> Database overloaded 68%. </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                        <span class="time">3 days</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-danger">
                                                            <i class="fa fa-bolt"></i>
                                                        </span> A user IP blocked. </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                        <span class="time">4 days</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-warning">
                                                            <i class="fa fa-bell-o"></i>
                                                        </span> Storage Server #4 not responding dfdfdfd. </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                        <span class="time">5 days</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-info">
                                                            <i class="fa fa-bullhorn"></i>
                                                        </span> System Error. </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                        <span class="time">9 days</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-danger">
                                                            <i class="fa fa-bolt"></i>
                                                        </span> Storage server failed. </span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li> -->
                <!-- END NOTIFICATION DROPDOWN -->
                <!-- BEGIN INBOX DROPDOWN -->
                <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -- //// >
                <li class="dropdown dropdown-extended dropdown-inbox" id="header_inbox_bar">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <i class="icon-envelope-open"></i>
                        <span class="badge badge-default"> 4 </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="external">
                            <h3>You have
                                <span class="bold">7 New</span> Messages</h3>
                            <a href="app_inbox.html">view all</a>
                        </li>
                        <li>
                            <ul class="dropdown-menu-list scroller" style="height: 275px;" data-handle-color="#637283">
                                <li>
                                    <a href="#">
                                                    <span class="photo">
                                                        <img src="./templates/v4/assets/layouts/layout3/img/avatar2.jpg" class="img-circle" alt=""> </span>
                                                    <span class="subject">
                                                        <span class="from"> Lisa Wong </span>
                                                        <span class="time">Just Now </span>
                                                    </span>
                                        <span class="message"> Vivamus sed auctor nibh congue nibh. auctor nibh auctor nibh... </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                                    <span class="photo">
                                                        <img src="./templates/v4/assets/layouts/layout3/img/avatar3.jpg" class="img-circle" alt=""> </span>
                                                    <span class="subject">
                                                        <span class="from"> Richard Doe </span>
                                                        <span class="time">16 mins </span>
                                                    </span>
                                        <span class="message"> Vivamus sed congue nibh auctor nibh congue nibh. auctor nibh auctor nibh... </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                                    <span class="photo">
                                                        <img src="./templates/v4/assets/layouts/layout3/img/avatar1.jpg" class="img-circle" alt=""> </span>
                                                    <span class="subject">
                                                        <span class="from"> Bob Nilson </span>
                                                        <span class="time">2 hrs </span>
                                                    </span>
                                        <span class="message"> Vivamus sed nibh auctor nibh congue nibh. auctor nibh auctor nibh... </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                                    <span class="photo">
                                                        <img src="./templates/v4/assets/layouts/layout3/img/avatar2.jpg" class="img-circle" alt=""> </span>
                                                    <span class="subject">
                                                        <span class="from"> Lisa Wong </span>
                                                        <span class="time">40 mins </span>
                                                    </span>
                                        <span class="message"> Vivamus sed auctor 40% nibh congue nibh... </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                                    <span class="photo">
                                                        <img src="./templates/v4/assets/layouts/layout3/img/avatar3.jpg" class="img-circle" alt=""> </span>
                                                    <span class="subject">
                                                        <span class="from"> Richard Doe </span>
                                                        <span class="time">46 mins </span>
                                                    </span>
                                        <span class="message"> Vivamus sed congue nibh auctor nibh congue nibh. auctor nibh auctor nibh... </span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li> -->
                <!-- END INBOX DROPDOWN -->
                <!-- BEGIN TODO DROPDOWN -->
                <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -- ///>
                <li class="dropdown dropdown-extended dropdown-tasks" id="header_task_bar">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <i class="icon-calendar"></i>
                        <span class="badge badge-default"> 3 </span>
                    </a>
                    <ul class="dropdown-menu extended tasks">
                        <li class="external">
                            <h3>You have
                                <span class="bold">12 pending</span> tasks</h3>
                            <a href="app_todo.html">view all</a>
                        </li>
                        <li>
                            <ul class="dropdown-menu-list scroller" style="height: 275px;" data-handle-color="#637283">
                                <li>
                                    <a href="javascript:;">
                                                    <span class="task">
                                                        <span class="desc">New release v1.2 </span>
                                                        <span class="percent">30%</span>
                                                    </span>
                                                    <span class="progress">
                                                        <span style="width: 40%;" class="progress-bar progress-bar-success" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                                                            <span class="sr-only">40% Complete</span>
                                                        </span>
                                                    </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                                    <span class="task">
                                                        <span class="desc">Application deployment</span>
                                                        <span class="percent">65%</span>
                                                    </span>
                                                    <span class="progress">
                                                        <span style="width: 65%;" class="progress-bar progress-bar-danger" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">
                                                            <span class="sr-only">65% Complete</span>
                                                        </span>
                                                    </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                                    <span class="task">
                                                        <span class="desc">Mobile app release</span>
                                                        <span class="percent">98%</span>
                                                    </span>
                                                    <span class="progress">
                                                        <span style="width: 98%;" class="progress-bar progress-bar-success" aria-valuenow="98" aria-valuemin="0" aria-valuemax="100">
                                                            <span class="sr-only">98% Complete</span>
                                                        </span>
                                                    </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                                    <span class="task">
                                                        <span class="desc">Database migration</span>
                                                        <span class="percent">10%</span>
                                                    </span>
                                                    <span class="progress">
                                                        <span style="width: 10%;" class="progress-bar progress-bar-warning" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">
                                                            <span class="sr-only">10% Complete</span>
                                                        </span>
                                                    </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                                    <span class="task">
                                                        <span class="desc">Web server upgrade</span>
                                                        <span class="percent">58%</span>
                                                    </span>
                                                    <span class="progress">
                                                        <span style="width: 58%;" class="progress-bar progress-bar-info" aria-valuenow="58" aria-valuemin="0" aria-valuemax="100">
                                                            <span class="sr-only">58% Complete</span>
                                                        </span>
                                                    </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                                    <span class="task">
                                                        <span class="desc">Mobile development</span>
                                                        <span class="percent">85%</span>
                                                    </span>
                                                    <span class="progress">
                                                        <span style="width: 85%;" class="progress-bar progress-bar-success" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">
                                                            <span class="sr-only">85% Complete</span>
                                                        </span>
                                                    </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                                    <span class="task">
                                                        <span class="desc">New UI release</span>
                                                        <span class="percent">38%</span>
                                                    </span>
                                                    <span class="progress progress-striped">
                                                        <span style="width: 38%;" class="progress-bar progress-bar-important" aria-valuenow="18" aria-valuemin="0" aria-valuemax="100">
                                                            <span class="sr-only">38% Complete</span>
                                                        </span>
                                                    </span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li> -->
                <?php
                /*
                 * Gerant
                 */

                if($_SESSION['gerant'] AND !FREE_ACCESS)
                {

                    // PROLONGEMENT ABO

                    $prix_t = PRICE_SUBSCRIPTION * 10;
                    foreach ($tab_mods_active AS $mod)
                        $prix_t += $mod['prix'] * 10;

                    if($prix_t)
                    {

                        ?>
                        <script>
                            function changeDuration(duration) {
                                $('#paypal_btn').attr("href", './Paypal/preparePayment.php?mois=' + duration)
                            }

                        </script>
                        <?php
                        if ((END_SUBSCRIPTION - time()) < 2592000)
                            $badge = '!';
                        else
                            $badge = '';

                        $prolongement = new DropdownMenu('refresh', $badge);
                        $prolongement->setHidden();





                        //$extend_modal->setContent($gauche.$droite.$paiement);


                        $modal_subscribe = new modalAjax('general', 'subscribe');

                        $prolongement->setTitle('Abonné jusqu\'au '.date('d/m/Y', END_SUBSCRIPTION), $modal_subscribe->getHref(), new Font('shopping-cart').' Prolonger');

                        echo $prolongement;
                    }

                    // FIN PROLONGEMENT ABO


                    //SMS
                    if (AccesActivableModul('sms')) {
                        if ($commande > 0)
                            $pourcentage = round($conso * 100 / $commande, 1);
                        else
                            $pourcentage = 100;


                        $progress = new ProgressBar($conso, $commande, 'sms');
                        $progress->display_text();
                        $progress->small();


                        $sms_modal = new modalAjax('general', 'sms');

                        if($commande-$conso < 10)
                            $badge = '!';
                        else
                            $badge = '';

                        $solde = $commande - $conso;

                        $commandes_sms = new DropdownMenu('comment', $badge);
                        $commandes_sms->setHidden();
                        $commandes_sms->setTitle('Il vous reste '. $solde .' SMS', $sms_modal->getHref(), new Font('shopping-cart').' Commander');

                        echo $commandes_sms;



                    }
                    //FIN SMS
                }



                /*
                 * Fin gérant
                 */





                /*
                 * News
                 */
                $jours = 3;
                $fichier = 'http://faq.managy.fr/feed/';
                $xml = simplexml_load_file($fichier);

                $xml = json_decode(json_encode($xml), TRUE);

                //var_dump($xml);

                $i=0;
                $nb_recent = 0;
                foreach($xml['channel']['item'] AS $post)
                {
                    if (++$i > 10)
                        break;
                    if(strtotime($post['pubDate']) > time() - 60*60*24*$jours) //7 jour
                        $nb_recent++;
                }
                $news = new DropdownMenu('newspaper-o', $nb_recent);
                $news->setHidden();
                $news->setLarge();
                $news->setTitle('Les dernierès news du blog', 'http://faq.managy.fr/blog" target="_blank"', 'Tout voir');

                $i=0;
                foreach($xml['channel']['item'] AS $post)
                {
                    //echo 'ok';
                    if(++$i > 10)
                        break;
                    if(strtotime($post['pubDate']) > time() - 60*60*24*$jours)
                        $label = 'warning';
                    else
                        $label = 'default';

                    if(strlen($post['title']) > 60)
                        $title = substr($post['title'], 0, 60).'...';
                    else
                        $title = $post['title'];

                    $news->addLine($title, 'newspaper-o', parse_date(strtotime($post['pubDate'])), $post['link'].'" target="_blank"', $label);


                }

                echo $news;

                /*
                 * Fin news
                 */

                // Lien vers la FAQ
                $faq = new DropdownMenu('question-circle', '', '', 'http://faq.managy.fr/', '', '', true);
                $faq->setHidden('xs');
                echo $faq;

                ?>
                <!-- END TODO DROPDOWN -->
                <!-- BEGIN USER LOGIN DROPDOWN -->
                <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
                <li class="dropdown dropdown-user">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <img alt="" class="img-circle" src="https://placehold.it/50/55C1E7/fff&text=<?php echo strtoupper($_SESSION['prenom'][0].$_SESSION['nom'][0]);?>" />
                        <span class="username username-hide-on-mobile"> <?php echo $_SESSION['prenom'];?> </span>
                        <?php echo new Font('angle-down');?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-large">
                        <li><a href="./s<?php echo $_SESSION['id'];?>"><i class="icon-user"></i> Mon Profile </a></li>
                        <li class="divider"> </li>
                        <?php
                        if($_SESSION['gerant']) {
                            ?>
                            <li><a href="./admin-notifys"><?php echo new Font('bullhorn');?>Notifications mail</a></li>
                            <li><a href="./admin"><?php echo new Font('cog');?>Configuration du compte</a></li>
                            <li><a href="./modules" title=""><?php echo new Font('sitemap');?>Modules complémentaires</a></li>
                            <li><a href="./staffs" title=""><?php echo new Font('user');?>Collaborateurs</a> </li>
                            <li class="divider"></li>
                            <?php
                        }
                        ?>
                        <li><a href="./login.html"><?php echo new Font('key');?> Déconnexion </a></li>
                    </ul>
                </li>
                <!-- END USER LOGIN DROPDOWN -->
                <!-- BEGIN QUICK SIDEBAR TOGGLER -->
                <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
                <li class="dropdown dropdown-quick-sidebar-toggler">
                    <a href="javascript:;" class="dropdown-toggle">
                        <i class="icon-logout"></i>
                        <?php
                            $badge_unread_chat = new Badge('');
                            $badge_unread_chat->setClass('unread_chat');
                            echo $badge_unread_chat;
                        ?>
                    </a>
                </li>
                <!-- END QUICK SIDEBAR TOGGLER -->
            </ul>
        </div>
        <!-- END TOP NAVIGATION MENU -->
    </div>
    <!-- END HEADER INNER -->
</div>
<!-- END HEADER -->
<!-- BEGIN HEADER & CONTENT DIVIDER -->
<div class="clearfix"> </div>
<!-- END HEADER & CONTENT DIVIDER -->
<!-- BEGIN CONTAINER -->
<div class="page-container">
<?php


if(is_object($sms_modal))
    echo $sms_modal->getModalHtml();

/*if(is_object($extend_modal))
    echo $extend_modal->getModalHtml();*/

if(is_object($modal_subscribe))
    echo $modal_subscribe->getModalHtml();


if(END_SUBSCRIPTION < time() AND $_SESSION['gerant'] AND $prix_t AND !$_SESSION['modal_subscribe']) {
    echo $modal_subscribe->openNow();
    $_SESSION['modal_subscribe'] = true;
}

if(is_object($modal_create_inter))
            echo $modal_create_inter->getModalHtml();

if(is_object($add_fast_order))
    echo $add_fast_order->getModalHtml();


if($_SESSION['gerant'] AND CGU_VERSION != $id_cgu AND $_SESSION['id'] > "1")
{
    ?>
    <script>
        function valider_cgu()
        {
            if( $('#chk_cgu').is(':checked') ){
                $(location).attr('href',"./dashboard?new_cgu=ok");
            }
        }
    </script>
    <?php
    $modal_cgu = new Modal('Les CGU ont changé', 'new_cgu');
    $modal_cgu->disableClose();
    $modal_cgu->setWidth('50%');
    $modal_cgu->openButton('Test');

    $div = '<div class="scroller" data-height="160px" data-always-visible="1" data-rail-visible="1" style="overflow: auto; border: 1px solid #000;">'.$texte_cgu.'</div>';
    $chk_cgu = new CheckBox('chk_cgu');
    $modal_cgu->setContent('Cher utilisateur de Managy, nos conditions générales d\'utilisation ont évolué :<br /><br />'.$div.'<br /><br />'.$chk_cgu.' Je reconnais avoir lu en intégralité ces nouvelles CGU et les accepte sans résèrve.');

    $modal_cgu->setOnclickButton('Valider mon choix', "valider_cgu();");

    echo $modal_cgu->openNow();
    echo $modal_cgu->getModalHtml();
}


/*
 * Changement interventions
 */

if(count($tab_changement))
{
    $modal_changement = new Modal('Changements sur interventions', 'modal_change_inter');
    $html_changement = '';
    foreach ($tab_changement AS $chg)
    {
        $staff = new GetInfosStaff($chg{"id_staff"});
        $html .= $staff->GetPrenom().' '.$staff->GetNom().' a apporté des modification à l\'intervention N°<a href="./i'.$chg['id_inter'].'">'.$chg['id_inter'].'</a><br />';
    }
    $modal_changement->setContent($html);
    $modal_changement->setSubmitButton('', 'Ok');
    echo $modal_changement->getModalHtml();
    echo $modal_changement->openNow();
}
