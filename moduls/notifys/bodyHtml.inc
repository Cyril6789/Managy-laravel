<?php session_start();

    if(right('notify', -1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/notifys.php'))
        {
            global  $db;
            $db->Query('SELECT * FROM notifications  ORDER BY id');
            $i=0;
            $tab_notifys = Array();
            while ($row = $db->Row()) 
            {
                $tab_notifys[$i]['id'] = $row->id;
                $tab_notifys[$i]['alias'] = $row->alias;
                $tab_notifys[$i]['texte'] = $row->text;
                $tab_notifys[$i]['type'] = $row->type;
                $tab_notifys[$i]['timeout'] = $row->timeout;
                $i++;
            }


                    
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/pages/notifys.php');
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