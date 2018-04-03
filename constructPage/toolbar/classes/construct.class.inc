<?php session_start();
	Class Toolbar{
		private $html;

		public function __construct()
		{
			
			$template_name = TEMPLATE_NAME;
			if(is_file('./templates/'.TEMPLATE_NAME.'/includes/toolbar.inc'))
				include('./templates/'.TEMPLATE_NAME.'/includes/toolbar.inc');
		}

	}

?>