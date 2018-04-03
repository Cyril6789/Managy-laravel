<?php session_start();

	Class End{
		private $html;

		public function __construct()
		{
			$template_name = TEMPLATE_NAME;
			if(is_file('./templates/'.TEMPLATE_NAME.'/includes/end.inc'))
				include('./templates/'.TEMPLATE_NAME.'/includes/end.inc');
		}

	}

?>