<?php session_start();

    class SelectCustomer
    {
        private $html;
        private $redirect_link;
        private $bad_redirect_link;
        private $pro_part='0';
        private $no_parent;
        private $no_change_page;
        private $reload_function;


        public function __construct($redirect_link, $bad_redirect_link)
        {
            $this->redirect_link = $redirect_link;
            $this->bad_redirect_link = $bad_redirect_link;
            $this->no_parent = 0;
            $this->no_change_page = 0;
        }

        /**
         * @param int $no_change_page
         */
        public function NoChangePage($reload_function_name)
        {
            $this->no_change_page = 1;
            $this->reload_function = $reload_function_name;
        }

        public function no_parent()
        {
            $this->no_parent = 1;
        }


        public function setType($type)
        {
            $this->pro_part = (int) $type;
        }

        public function getHTML() 
        {
            $redirect_link = $this->redirect_link;
            $bad_redirect_link = $this->bad_redirect_link;
            $type = $this->pro_part;
            $no_parent = $this->no_parent;
            $no_change_page = $this->no_change_page;
            $reload_function = $this->reload_function;
            if(is_file('./templates/'.TEMPLATE_NAME.'/classes/html/select_customer.php'))
            {
                ob_start();
                require_once('./templates/'.TEMPLATE_NAME.'/classes/html/select_customer.php');
                $this->html = ob_get_contents();
                ob_end_clean();
            }

            if(is_file('./../templates/'.TEMPLATE_NAME.'/classes/html/select_customer.php'))
            {
                ob_start();
                require_once('./../templates/'.TEMPLATE_NAME.'/classes/html/select_customer.php');
                $this->html = ob_get_contents();
                ob_end_clean();
            }
            return $this->html;
        }
    }

?>