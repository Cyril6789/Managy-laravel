<?php session_start();


	Class Body{
		private $html;

		public function __construct($page_title)
		{

			$template_name = TEMPLATE_NAME;


			if($_GET['content'] != '')
			{

                global $db;
                $mod = $db->SQLfix($_GET['content']);
                $db->Query('SELECT nom FROM modules_comptes WHERE alias="'.$mod.'" AND type!="0" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                $row = $db->Row();

                if(empty($row->nom))
                    $modul = $_GET['content'];
                else
                    $modul = $row->nom;
                if(AccesActivableModul($modul))
                {

                    if($_GET['scontent'] != '')
                    {
                        if(is_file('./moduls/'.$modul.'/sub/'.$_GET['scontent'].'/bodyHtml.inc'))
                        {
                                include('./moduls/'.$modul.'/sub/'.$_GET['scontent'].'/bodyHtml.inc');
                                $html_body = $ModulContent;
                        }
                        else
                        {
                                include('./moduls/404/bodyHtml.inc');
                                $html_body = $ModulContent;
                        }
                    }
                    else
                    {
                        if(is_file('./moduls/'.$modul.'/bodyHtml.inc'))
                        {


                            include('./moduls/'.$modul.'/bodyHtml.inc');
                                $html_body =  $ModulContent;
                        }
                        else
                        {
                                include('./moduls/404/bodyHtml.inc');
                                $html_body = $ModulContent;
                        }
                    }
                }
                else //pas activé, on dégage
                {
                    if(is_dir('./moduls/'.$modul.'/'))
                    {
                        if(is_file('./moduls/no_active_modul/bodyHtml.inc'))
                        {
                                include('./moduls/no_active_modul/bodyHtml.inc');
                                $html_body = $ModulContent;
                        }
                        else
                        {
                                include('./moduls/404/bodyHtml.inc');
                                $html_body = $ModulContent;
                        }
                    }
                    else
                    {
                        include('./moduls/404/bodyHtml.inc');
                                $html_body = $ModulContent;
                    }
                }
			}
			else
            {
                global $db;
                $db->Query('SELECT nom FROM modules_comptes WHERE alias="dashboard" AND type!="0" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                $row = $db->Row();

                if(empty($row->nom))
                    $modul = 'dashboard';
                else
                    $modul = $row->nom;

                include('./moduls/'.$modul.'/bodyHtml.inc');



                $html_body = $ModulContent;
            }

            $sql = 'SELECT visible_chat, last_chat FROM staffs WHERE id = "'.$_SESSION['id'].'" ';
            $db->Query($sql);
            $r = $db->Row();
            $chat_visible = $r->visible_chat;


            $last_chat = $r->last_chat;

			if(is_file('./templates/'.TEMPLATE_NAME.'/includes/body.inc'))
                include('./templates/'.TEMPLATE_NAME.'/includes/body.inc');
		}

	}

?>