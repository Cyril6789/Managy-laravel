<?php session_start();

	Class ClassModul{
		private $html;

		public function __construct()
		{

		}

		public function display()
		{
			return $this->html;
		}
	}

?>