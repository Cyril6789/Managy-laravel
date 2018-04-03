<?php session_start();

    if(right('sms', 1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/sms_types.php'))
        {
            global  $db;
            $db->Query('SELECT * FROM sms_types  WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id'); //toutes les matières
            $i=0;
            $tab_sms = Array();
            while ($row = $db->Row()) 
            {
                $tab_sms[$i]['id'] = $row->id;
                $tab_sms[$i]['titre'] = $row->titre;
                $tab_sms[$i]['message'] = $row->message;
                $i++;
            }
                    
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/pages/sms_types.php');
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