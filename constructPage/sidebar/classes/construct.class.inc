<?php session_start();
	Class Sidebar{
		private $html;

		public function __construct()
		{


            $sidebar=Array();

            if(1) //empty($_SESSION[$_SESSION['compte_principal']]['sidebar_final']) OR empty($_SESSION[$_SESSION['compte_principal']]['counter_sidebar']))
            {

                $Directory = './moduls';
                $MyDirectory = opendir($Directory) or die('Modul access Error');
                while ($Entry = @readdir($MyDirectory))
                    if (is_dir($Directory . '/' . $Entry) && $Entry[0] != '_' && $Entry != '.' && $Entry != '..' && $Entry != 'virgin') //Modules
                        if (AccesActivableModul($Entry)) //Activé, on affiche (pas de else)
                        {
                            if (is_file($Directory . '/' . $Entry . '/sidebar.inc'))
                                include($Directory . '/' . $Entry . '/sidebar.inc');
                            $SubDirectory = $Directory . '/' . $Entry . '/sub';
                            if (is_dir($SubDirectory)) //sous modules
                            {
                                $MySubDirectory = opendir($SubDirectory) or die('Modul access Error');
                                while ($SubEntry = @readdir($MySubDirectory))
                                    if (is_dir($SubDirectory . '/' . $SubEntry) && $SubEntry[0] != '_' && $SubEntry != '.' && $SubEntry != '..' && $SubEntry != 'virgin')
                                        if (is_file($SubDirectory . '/' . $SubEntry . '/sidebar.inc'))
                                            include($SubDirectory . '/' . $SubEntry . '/sidebar.inc');
                            }
                        }

                $counter = 0;

                //TRI DE OUF pour remettre ce putin de tableau dans le bon ordre
                $sidebar_final = Array();
                $badge = Array();
                $count_badge = 0;
                foreach ($sidebar AS $key => $value) // Chaque menu
                {
                    $acces_menu = false;
                    //sub_menu
                    for ($j = 0; $j < count($value) - 1; $j++) {
                        $elmt = explode('|', $value[$j]);
                        if (right($elmt[0], $elmt[1]))
                            $acces_menu = true;
                        $badge[$count_badge] += $elmt[4];
                    }

                    if ($acces_menu) {
                        $count_badge++;
                        //menu
                        $elmt = explode('|', $value['first']);
                        $sidebar_final[$counter]['icon'] = $elmt[0];
                        if (OPEN_SIDEBAR == $key)
                            $sidebar_final[$counter]['title'] = '#' . $elmt[1];
                        else
                            $sidebar_final[$counter]['title'] = $elmt[1];
                        $sidebar_final[$counter]['link'] = $elmt[2];
                        $sidebar_final[$counter]['badge'] = $elmt[4];
                        $badge[$count_badge] += $sidebar_final[$counter]['badge'];
                        $counter++;

                        //re-submenu
                        for ($j = 0; $j < count($value) - 1; $j++) {
                            $elmt = explode('|', $value[$j]);
                            if (right($elmt[0], $elmt[1])) {
                                $sidebar_final[$counter]['title'] = '->' . $elmt[2];
                                $sidebar_final[$counter]['link'] = $elmt[3];
                                $sidebar_final[$counter]['badge'] = $elmt[4];
                                $counter++;
                            }
                        }
                    }
                }

                $_SESSION[$_SESSION['compte_principal']]['counter_sidebar'] = $counter;
                $_SESSION[$_SESSION['compte_principal']]['sidebar_final'] = $sidebar_final;

            }
            else
            {
                $sidebar_final = $_SESSION[$_SESSION['compte_principal']]['sidebar_final'];
                $counter = $_SESSION[$_SESSION['compte_principal']]['counter_sidebar'];
            }


            global $db;
            $db->Query('SELECT texte FROM postit WHERE id_staff="'.$_SESSION['id'].'" AND compte_principal ="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            $texte_postit = $row->texte;
                    
			$template_name = TEMPLATE_NAME;
			if(is_file('./templates/'.TEMPLATE_NAME.'/includes/sidebar.inc'))
				include('./templates/'.TEMPLATE_NAME.'/includes/sidebar.inc');
		}
	}
?>