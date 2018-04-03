<?php session_start();

    if(right('admin', -1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/cgu.php'))
        {
            $sql = 'SELECT texte FROM cgu ORDER BY id DESC LIMIT 1';
            $db->Query($sql);

            $row = $db->Row();
            $texte = $row->texte;

            
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/cgu.php');
            $ModulContent = ob_get_contents();
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