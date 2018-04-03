<?php session_start();
        if(is_file('./templates/'.TEMPLATE_NAME.'/blocs/changing_lang.php') AND AccesActivableModul('translate'))
            include('./templates/'.TEMPLATE_NAME.'/blocs/changing_lang.php');
   
  ?>