
<!-- BEGIN SIDEBAR -->
<div class="page-sidebar-wrapper">
    <!-- BEGIN SIDEBAR -->
    <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
    <!-- DOC: Change data-auto-speed="200" to adjust the sub menu slide up/down speed -->
    <div class="page-sidebar navbar-collapse collapse">
        <!-- BEGIN SIDEBAR MENU -->
        <!-- DOC: Apply "page-sidebar-menu-light" class right after "page-sidebar-menu" to enable light sidebar menu style(without borders) -->
        <!-- DOC: Apply "page-sidebar-menu-hover-submenu" class right after "page-sidebar-menu" to enable hoverable(hover vs accordion) sub menu mode -->
        <!-- DOC: Apply "page-sidebar-menu-closed" class right after "page-sidebar-menu" to collapse("page-sidebar-closed" class must be applied to the body element) the sidebar sub menu mode -->
        <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
        <!-- DOC: Set data-keep-expand="true" to keep the submenues expanded -->
        <!-- DOC: Set data-auto-speed="200" to adjust the sub menu slide up/down speed -->

        <ul id="home_sidebar" class="page-sidebar-menu  page-header-fixed <?php if(SIDEBAR_CLOSED != 'false') echo 'page-sidebar-menu-closed'?>" data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200" style="padding-top: 20px">
            <!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
            <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
            <li class="sidebar-toggler-wrapper hide">
                <div class="sidebar-toggler">
                    <span></span>
                </div>
            </li>
            <!-- END SIDEBAR TOGGLER BUTTON -->
            <!-- DOC: To remove the search box from the sidebar you just need to completely remove the below "sidebar-search-wrapper" LI element -->
            <li class="sidebar-search-wrapper">
                <!-- BEGIN RESPONSIVE QUICK SEARCH FORM -->
                <!-- DOC: Apply "sidebar-search-bordered" class the below search form to have bordered search box -->
                <!-- DOC: Apply "sidebar-search-bordered sidebar-search-solid" class the below search form to have bordered & solid search box -->
                <form class="sidebar-search">
                    <a href="javascript:;" class="remove">
                        <i class="icon-close"></i>
                    </a>
                    <div class="input-group">
                        <input type="text" class="form-control" id="text-search" placeholder="Rechercher...">
                            <span class="input-group-btn">
                                <a href="javascript:;" class="btn submit">
                                    <i class="icon-magnifier"></i>
                                </a>
                            </span>
                    </div>
                </form>
                    <script  type='text/javascript'>

                        $(function(){
                           $('.sidebar-search').submit(function(e){
                               var search = $('#text-search').val();
                               if(!isNaN(search)) {
                                   $(location).attr("href", './i' + search);
                               }
                               e.preventDefault();
                           });
                        });

                        function set_end_sidebar()
                        {
                            var x = $("#date_heure_add_rdv").val();
                            var x = document.getElementById('date_heure_add_rdv').value;
                            var tab_x = x.split(' ');

                            var date = tab_x[0].split('/');

                            var jour = date[0];
                            var mois = date[1];
                            var annee = date[2];

                            var heures = tab_x[1].split(':');

                            var heure = heures[0];
                            var min = heures[1];

                            var timestamp = java_mktime(heure, min, mois, jour, annee);

                            var timestamp_fin = parseInt(timestamp) + parseInt(document.getElementById('ecart_add_rdv').value);

                            var fin = new Date(timestamp_fin * 1000);
                            jour = fin.getDate();
                            if(jour < 10)
                                jour = '0'+jour;

                            mois = fin.getMonth() + 1;

                            if(mois < 10)
                                mois = '0'+mois;

                            annee = fin.getFullYear();

                            heure = fin.getHours();
                            if(heure < 10)
                                heure = '0'+heure;

                            min = fin.getMinutes();
                            if(min < 10)
                                min = '0'+min;

                            var final = jour+'/'+mois+'/'+annee+' '+heure+':'+min;
                            //alert(final);

                            document.getElementById('date_heure_fin_add_rdv').value = final;
                        }

                        function set_ecart_sidebar()
                        {

                            var x = document.getElementById('date_heure_add_rdv').value;
                            var tab_x = x.split(' ');
                            var date = tab_x[0].split('/');

                            var jour = date[0];
                            var mois = date[1];
                            var annee = date[2];
                            var heures = tab_x[1].split(':');

                            var heure = heures[0];
                            var min = heures[1];
                            var timestamp_debut = java_mktime(heure, min, mois, jour, annee);
                            var x = document.getElementById('date_heure_fin_add_rdv').value;
                            var tab_x = x.split(' ');
                            var date = tab_x[0].split('/');

                            var jour = date[0];
                            var mois = date[1];
                            var annee = date[2];
                            var heures = tab_x[1].split(':');

                            var heure = heures[0];
                            var min = heures[1];
                            var timestamp_fin = java_mktime(heure, min, mois, jour, annee);

                            var ecart = parseInt(timestamp_fin) - parseInt(timestamp_debut);

                            if(ecart > 0)
                                document.getElementById('ecart_add_rdv').value = ecart;

                        }
                    </script>
                    <?php
                    $edit_rdv_modal = new Modal('Ajouter un Rendez-vous', 'modal_add_rdv');
                    $edit_rdv_modal->setSubmitButton('form_add_rdv', 'Créer le rendez-vous', 'btn-edit');
                    $edit_rdv_modal->setCancelButton('Annuler', 'btn-cancel');

                    $edit_rdv_form = new FormLayout('Saisie');
                    $edit_rdv_form->setFormControls('form_add_rdv', './rdv-create');

                    $edit_rdv_form->addLine('Client', '<div id="client_add_rdv"></div>');

                    $id_rdv = new Hidden('id_client', 'id_client_add_rdv');
                    $send = new Hidden('send', 'send_add_rdv');
                    $send->setValue(1);
                    $ecart = new Hidden('ecart', 'ecart_add_rdv');
                    $ecart->setValue(3600);

                    $date_heure = new Text('date_heure', 'date_heure_add_rdv');
                    $date_heure->dataMask('99/99/9999 99:99');
                    $date_heure->dateTimePicker('set_end_sidebar();');
                    $date_heure->onKeyUp("set_end_sidebar();");
                    $date_heure->setValue(date('d/m/Y').' 10:00');
                    $edit_rdv_form->addLine('Date heure début', $send.$id_rdv.$ecart.$date_heure);

                    $date_heure_fin = new Text('date_heure_fin', 'date_heure_fin_add_rdv');
                    $date_heure_fin->dataMask('99/99/9999 99:99');
                    $date_heure_fin->dateTimePicker('set_ecart_sidebar();');
                    $date_heure_fin->onKeyUp("set_ecart_sidebar();");
                    $date_heure_fin->setValue(date('d/m/Y').' 11:00');
                    $edit_rdv_form->addLine('Date heure fin', $date_heure_fin);

                    $lieu_1 = new Radio('lieu', '1', 'lieu_1_add_rdv');
                    //$lieu_1->checked();
                    $lieu_2 = new Radio('lieu', '2', 'lieu_2_add_rdv');
                    $lieu_2->checked();

                    $edit_rdv_form->addLine('Lieu', $lieu_1.' chez le client<br />'.$lieu_2.' à l\'atelier', false, 'lieux');


                    $edit_rdv_modal->setContent($edit_rdv_form, false);


                    ?>

                <!-- END RESPONSIVE QUICK SEARCH FORM -->
            </li>
            <li class="heading sidebar-search-results" style="margin-top:-20px; display: none;" >
                <h3 class="uppercase"><i class="icon-magnifier"></i> Résultats de Recherche</h3>
            </li>

            <li id="sidebar-search-results-editable" class="nav-item sidebar-search-results active open" style="display: none;">

            </li>

            <!-- Search Results --//>
                <span id="sidebar-search-results-editable">

                </span>-->
            <?php
            if($_SERVER['REQUEST_URI'] == '/dashboard') {
                $class = 'active';
                $span = '<span class="selected"></span>
                                    
';
            }
                else
                    $span = '';

            ?>
            <li class="nav-item start <?php echo $class;?>">
                <a href="./dashboard" class="nav-link nav-toggle">
                    <?php echo new Font('dashboard');?>
                    <span class="title">Dashboard </span>
                    <?php echo $span;?>
                </a>
            </li>

            <?php


            $sub = false;
            $count_badge = 0;
            for($i = 0; $i<$counter; $i++)
            {
                if($sidebar_final[$i]['title'][0] == '-' AND $sidebar_final[$i]['title'][1] == '>') // sous menu
                {
                    if(!$sub)
                        echo '                                                   
             <ul  class="sub-menu">
         ';

                    $sub = true;
                    $titre = str_replace('->', "", $sidebar_final[$i]['title'] );
                    echo '
            <li class="nav-item">
                <a href="'.$sidebar_final[$i]['link'].'">
                    <span class="title">'.$titre.'</span>';
                    if($sidebar_final[$i]['badge'] > 0)
                        echo '<span class="badge badge-danger">'.$sidebar_final[$i]['badge'].'</span>';

                    echo '
                </a>
            </li>
            ';
                }
                else //menu
                {
                    if($sub)
                    {
                        echo '
               </ul>
         </li>
              ';
                    }
                    else
                    {
                        if($first)
                        {
                            echo '</li>';
                        }
                    }
                    $sub = false;
                    if($sidebar_final[$i]['link'] == '')
                        $link = 'javascript:;';
                    else
                        $link = $sidebar_final[$i]['link'];

                    if($sidebar_final[$i]['title'][0] == '#')
                    {
                        $class = 'class="nav-item  active open"';

                        $span = '<span class="selected"></span>
                                    <span class="arrow open"></span>';
                    }
                    else
                    {
                        $class = 'class="nav-item"';
                        $span = '<span class="arrow"></span>';
                    }
                    echo '
         <li '.$class.'>
            <a href="'.$link.'" class="nav-link nav-toggle">
            '.new Font($sidebar_final[$i]['icon']).'
                <span class="title">
                    '.str_replace('#', '', $sidebar_final[$i]['title']).'
                ';

                    if($badge[$count_badge] > 0)
                        echo '<span class="badge badge-danger">'.$badge[$count_badge].'</span>
                                                       ';

                echo '</span>';
                    $count_badge++;
                echo $span;
                    echo '
               
        </a>';
                    $first = true;

                }

            }

            ?>

        </ul>

            <li class="nav-item active">
                <a href="javascript:void();" class="nav-link nav-toggle">
                    <?php echo new Font('sticky-note');?>
                    <span class="title">Pense-Bête</span>
                    <span class="arrow open"></span>
                </a>
                <ul  class="sub-menu">
                    <li class="nav-item">
                        <div style=" text-align: center;">
                            <textarea placeholder="Ecrivez-ici vos mémos !" id="postit" style="font-family: markers; font-weight: bolder; font-size: 12pt;  background-color: #FFF7B2; width: 80%;" rows="15"  onkeyup="post_it(this.value);"><?php echo $texte_postit;?></textarea>
                        </div>
                    </li>
                </ul>
            </li>





        <script>
            function post_it(text) {

                klient = new XMLHttpRequest();
                klient.open("POST", "./ajax/postit.php");
                klient.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                klient.send("text="+text);
            }


        </script>
        <style>
            @font-face {
                font-family: "markers";

            }
        </style>




        <!-- END SIDEBAR MENU -->
        <!-- END SIDEBAR MENU -->
    </div>
    <!-- END SIDEBAR -->
</div>
<!-- END SIDEBAR -->
<?php
    echo $edit_rdv_modal->getModalHtml();
    ?>




