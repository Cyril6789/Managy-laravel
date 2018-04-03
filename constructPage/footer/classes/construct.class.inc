<?php session_start();

	Class Footer{
		private $html;

		public function __construct()
		{
			if(isset($_SESSION['notify']))
			{

				global $db;
				$noti = $db->SQLFix($_SESSION['notify']);
				$db->Query('SELECT text, type, timeout FROM notifications WHERE alias = "'.$noti.'" ');
				$row = $db->row();

				$text_n = $row->text;
				$type_n = $row->type;
				$timeout_n = $row->timeout;
				$_SESSION['notify'] = '';
			}
			$template_name = TEMPLATE_NAME;
			if(is_file('./templates/'.TEMPLATE_NAME.'/includes/footer.inc'))
				include('./templates/'.TEMPLATE_NAME.'/includes/footer.inc');
		}

	}

?>