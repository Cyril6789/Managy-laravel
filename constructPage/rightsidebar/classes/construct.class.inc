<?php session_start();

	Class Rightsidebar{
		private $html;

		public function __construct()
		{
			$template_name = TEMPLATE_NAME;
			if(is_file('./templates/'.TEMPLATE_NAME.'/includes/rightsidebar.inc')) {

			    if(right('logs', 1)) {
                    $db = new MySQL();
                    $sql = 'SELECT id_staff, id_inter, id_client, texte, login, time FROM logs WHERE compte_principal = "' . $_SESSION['compte_principal'] . '" ORDER BY id DESC LIMIT 200';
                    $tab_logs = array();
                    $i = 0;
                    $db->Query($sql);
                    while ($row = $db->Row()) {
                        $tab_logs[$i]['id_staff'] = $row->id_staff;
                        $tab_logs[$i]['id_inter'] = $row->id_inter;
                        $tab_logs[$i]['login'] = $row->login;
                        $tab_logs[$i]['id_client'] = $row->id_client;
                        $tab_logs[$i]['texte'] = $row->texte;
                        $tab_logs[$i]['time'] = $row->time;
                        $i++;
                    }
                }

                include('./templates/' . TEMPLATE_NAME . '/includes/rightsidebar.inc');
            }
		}

	}

?>