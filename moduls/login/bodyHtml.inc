<?php session_start();

$page_title = 'Error';
  
if(is_dir('./templates/'.TEMPLATE_NAME.'/pages/'))
{
        ob_start();
        include('./templates/'.TEMPLATE_NAME.'/pages/no_right.php');
        $ModulContent = ob_get_contents();
        ob_end_clean();
}
    
?>