<?php session_start();

    
    if(is_file('./templates/'.TEMPLATE_NAME.'/pages/no_active_modul.php'))
    {
        ob_start();
        include('./templates/'.TEMPLATE_NAME.'/pages/no_active_modul.php');
        $ModulContent = ob_get_contents();
        ob_end_clean();
    }
    
?>