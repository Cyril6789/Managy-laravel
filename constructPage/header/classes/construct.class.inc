<?php session_start();
	Class Header{
		private $html;

		public function __construct($page_title)
		{
                    
			
			$template_name = TEMPLATE_NAME;
			if(is_file('./templates/'.TEMPLATE_NAME.'/includes/header.inc'))
				include('./templates/'.TEMPLATE_NAME.'/includes/header.inc');
		}

	}

?>