<?php session_start();
    
    if(is_file('./templates/'.TEMPLATE_NAME.'/pages/brice.php'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/brice.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
        }
        
?>