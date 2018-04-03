<?php session_start();

if(is_dir('./templates/'.TEMPLATE_NAME.'/pages/'))
{
        ob_start();
        include('./templates/'.TEMPLATE_NAME.'/pages/404.php');
        $ModulContent = ob_get_contents();
        ob_end_clean();
}
	

?>