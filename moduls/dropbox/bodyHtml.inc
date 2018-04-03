<?php session_start();

    if(right('dropbox', 1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/dropbox.php'))
        {



            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/pages/dropbox.php');
            $ModulContent .= ob_get_contents();
            ob_end_clean();
        }
    }
    else
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/no_right.php'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/no_right.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
        }
    }
?>