<?php session_start();

$notification_maintenance = '';
$site_ferme = '';

	Class Page{
		private $html;

		public function __construct()
		{



            if($_SESSION['id'])
                    {

                        $fermeture_site = false;

                        global $db;

                        $sql = 'SELECT date, texte_prevention FROM maintenance_site WHERE active = "1"';
                        $db->Query($sql);

                        $row = $db->Row();
                        $maintenance_date = $row->date;
                        if($maintenance_date > 0)
                        {
                            global $notification_maintenance;
                            global $site_ferme;
                            if($maintenance_date > time())
                                $notification_maintenance = $row->texte_prevention;
                            else
                            {
                                $site_ferme = 'LE SITE EST ACTUELLEMENT FERMÉ !';
                                $fermeture_site= true;
                            }

                        }

                        global $tab_messages_site;
                        $tab_messages_site = Array();
                        $sql = 'SELECT * FROM messages_site WHERE date_debut<"'.time().'" AND date_fin >  "'.time().'" ';
                        //echo $sql;
                        $db->Query($sql);
                        $i = 0;
                       while($row = $db->Row())
                       {
                           $tab_messages_site[$i]['type'] = $row->type;
                           $tab_messages_site[$i]['message'] = $row->message;
                           $i++;
                       }

                        //print_r($tab_messages_site);


                        if($fermeture_site)
                        {

                            $sql = 'SELECT count(*) AS nb FROM acces_staff_maintenance WHERE id_staff = "'.$_SESSION['id'].'" ';

                            $db->Query($sql);
                            $row = $db->Row();

                            $acces = $row->nb;
                        }

                        if($acces OR !$fermeture_site) {
                            $db->Query('SELECT session_id FROM staffs WHERE id="' . $_SESSION['id'] . '"');
                            $row = $db->Row();
                            $session_id = $row->session_id;
                            if ($session_id == session_id() OR $_SESSION['id'] == 1) //Même PC, même ip, ou moi
                            {

                                if ($_GET['content'] != '') //Module parent présent
                                {

                                    $mod = $db->SQLfix($_GET['content']);
                                    $db->Query('SELECT nom FROM modules_comptes WHERE alias="' . $mod . '" AND type!="0" AND compte_principal="' . $_SESSION['compte_principal'] . '" ');
                                    $row = $db->Row();

                                    if (empty($row->nom))
                                        $modul = $_GET['content'];
                                    else
                                        $modul = $row->nom;
                                    if (AccesActivableModul($modul)) {


                                        if ($_GET['scontent'] != '') //Sous-Module
                                        {
                                            if (is_dir('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/')) {


                                                if (is_file('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/langs/' . HL . '.inc'))
                                                    include('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/langs/' . HL . '.inc');//On charge la langue
                                                if (is_file('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/langs/fr.inc'))
                                                    include('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/langs/fr.inc');//on complete les blancs par la version française
                                                if (is_file('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/langs/' . HL . '.inc'))
                                                    include('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/langs/' . HL . '.inc'); //on recharge la langue courante pour remplacer le $page_title
                                                if (is_file('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/ressources/parse_title.inc'))
                                                    include('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/ressources/parse_title.inc');
                                                if (is_file('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/ressources/post.inc'))
                                                    include('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/ressources/post.inc');
                                                if (is_file('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/ressources/get.inc'))
                                                    include('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/ressources/get.inc');
                                                if (is_file('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/ressources/routines.inc'))
                                                    include('./moduls/' . $modul . '/sub/' . $_GET['scontent'] . '/ressources/routines.inc');
                                            } else {
                                                echo '404';
                                                if (is_file('./moduls/404/langs/' . HL . '.inc'))
                                                    include('./moduls/404/langs/' . HL . '.inc'); //On charge la langue
                                                if (is_file('./moduls/404/langs/fr.inc'))
                                                    include('./moduls/404/langs/fr.inc'); //on complete les blancs par la version française
                                                if (is_file('./moduls/404/langs/' . HL . '.inc'))
                                                    include('./moduls/404/langs/' . HL . '.inc'); //on recharge la langue courante pour remplacer le $page_title
                                                if (is_file('./moduls/404/ressources/parse_title.inc'))
                                                    include('./moduls/404/ressources/parse_title.inc');

                                            }
                                        } else //Module
                                        {
                                            if (is_dir('./moduls/' . $modul . '/')) {
                                                if (is_file('./moduls/' . $modul . '/langs/' . HL . '.inc'))
                                                    include('./moduls/' . $modul . '/langs/' . HL . '.inc');
                                                if (is_file('./moduls/' . $modul . '/langs/fr.inc'))
                                                    include('./moduls/' . $modul . '/langs/fr.inc');
                                                if (is_file('./moduls/' . $modul . '/langs/' . HL . '.inc'))
                                                    include('./moduls/' . $modul . '/langs/' . HL . '.inc');
                                                if (is_file('./moduls/' . $modul . '/ressources/parse_title.inc'))
                                                    include('./moduls/' . $modul . '/ressources/parse_title.inc');
                                                if (is_file('./moduls/' . $modul . '/ressources/post.inc'))
                                                    include('./moduls/' . $modul . '/ressources/post.inc');
                                                if (is_file('./moduls/' . $modul . '/ressources/get.inc'))
                                                    include('./moduls/' . $modul . '/ressources/get.inc');
                                                if (is_file('./moduls/' . $modul . '/ressources/routines.inc'))
                                                    include('./moduls/' . $modul . '/ressources/routines.inc');
                                            } else {
                                                if (is_file('./moduls/404/langs/' . HL . '.inc'))
                                                    include('./moduls/404/langs/' . HL . '.inc');
                                                if (is_file('./moduls/404/langs/fr.inc'))
                                                    include('./moduls/404/langs/fr.inc');
                                                if (is_file('./moduls/404/langs/' . HL . '.inc'))
                                                    include('./moduls/404/langs/' . HL . '.inc');
                                                if (is_file('./moduls/404/ressources/parse_title.inc'))
                                                    include('./moduls/404/ressources/parse_title.inc');

                                            }

                                        }
                                    } else // Pas activé on affiche la page d'erreur
                                    {
                                        if (is_dir('./moduls/' . $modul . '/')) {
                                            if (is_file('./moduls/no_active_modul/langs/' . HL . '.inc'))
                                                include('./moduls/no_active_modul/langs/' . HL . '.inc');
                                            if (is_file('./moduls/no_active_modul/langs/fr.inc'))
                                                include('./moduls/no_active_modul/langs/fr.inc');
                                        } else {

                                            //Peut-etre lien externe
                                            $db = new MySQL();
                                            $ext = $db->SQLFix($_GET['content']);
                                            $sql = 'SELECT id FROM interventions WHERE external_link="'.$ext.'" ';
                                            $db->Query($sql);
                                            $row = $db->Row();
                                            $id_base_inter = $row->id;



                                            if($row->id) {
                                                include('./external_link.php'); //?id='.$ext);
                                                die();
                                            }
                                            else {

                                                if (is_file('./moduls/404/langs/' . HL . '.inc'))
                                                    include('./moduls/404/langs/' . HL . '.inc');
                                                if (is_file('./moduls/404/langs/fr.inc'))
                                                    include('./moduls/404/langs/fr.inc');
                                                if (is_file('./moduls/404/langs/' . HL . '.inc'))
                                                    include('./moduls/404/langs/' . HL . '.inc');
                                                if (is_file('./moduls/404/ressources/parse_title.inc'))
                                                    include('./moduls/404/ressources/parse_title.inc');
                                            }

                                        }
                                    }


                                } else {
                                    global $db;
                                    $db->Query('SELECT nom FROM modules_comptes WHERE alias="dashboard" AND type!="0" AND compte_principal="' . $_SESSION['compte_principal'] . '" ');
                                    $row = $db->Row();


                                    if (empty($row->nom)) {
                                        //die();
                                        include('./login.php');
                                        die();
                                    } else {
                                        $modul = $row->nom;

                                        if (is_file('./moduls/' . $modul . '/langs/' . HL . '.inc'))
                                            include('./moduls/' . $modul . '/langs/' . HL . '.inc');
                                        if (is_file('./moduls/' . $modul . '/ressources/parse_title.inc'))
                                            include('./moduls/' . $modul . '/ressources/parse_title.inc');
                                        if (is_file('./moduls/' . $modul . '/ressources/post.inc'))
                                            include('./moduls/' . $modul . '/ressources/post.inc');
                                        if (is_file('./moduls/' . $modul . '/ressources/get.inc'))
                                            include('./moduls/' . $modul . '/ressources/get.inc');
                                        if (is_file('./moduls/' . $modul . '/ressources/routines.inc'))
                                            include('./moduls/' . $modul . '/ressources/routines.inc');

                                    }
                                }


                                if (TEMPLATE_NAME == 'mango') {
                                    $header = new Header($page_title);
                                    $arianne = new Arianne($page_title);
                                    $head = new Head();
                                    $toolbar = new Toolbar();
                                    $sidebar = new Sidebar();
                                    $body = new Body($page_title);
                                    $footer = new Footer();
                                }
                                else
                                {
                                    if(TEMPLATE_NAME == 'melon') {
                                        ob_start(); //pour eviter les problème de headers déjà envoyés
                                        $header = new Header($page_title);
                                        $head = new Head();
                                        $sidebar = new Sidebar();
                                        $arianne = new Arianne($page_title);
                                        $body = new Body($page_title);
                                        $footer = new Footer();
                                        $content = ob_get_contents();


                                        ob_end_clean();
                                        echo $content;
                                    }
                                    else{ //metronic
                                        ob_start(); //pour eviter les problème de headers déjà envoyés
                                        $header = new Header($page_title);
                                        $head = new Head();
                                        $sidebar = new Sidebar();
                                        $arianne = new Arianne($page_title);
                                        $body = new Body($page_title);
                                        $rightsidebar = new Rightsidebar();
                                        $footer = new Footer();
                                        $end = new End();

                                        $content = ob_get_contents();
                                        ob_end_clean();
                                        echo $content;

                                    }
                                }


                            } else {
                                include('./login.php');
                            }
                        }
                        else
                            include('./maintenance.php');

                    }
                    else
                    {
                        //Peut-etre lien externe
                        $db = new MySQL();
                        $ext = $db->SQLFix($_GET['content']);
                        $sql = 'SELECT id FROM interventions WHERE external_link="'.$ext.'" ';
                        $db->Query($sql);
                        $row = $db->Row();
                        $id_base_inter = $row->id;



                        if($row->id AND !(empty($_GET['content'])))
                            include('./external_link.php'); //?id='.$ext);
                        else
                            if(empty($_GET['content']))
                                include('./login.php');
                            else
                                include('./login.php');

                    }
		}
		
		
	}
	?>