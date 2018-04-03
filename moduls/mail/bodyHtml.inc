<?php session_start();

    if(right('mail', 1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/mails_types.php'))
        {
            global  $db;
            $db->Query('SELECT * FROM mails_types  WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id');
            $i=0;
            $tab_mails = Array();
            while ($row = $db->Row()) 
            {
                $tab_mails[$i]['id'] = $row->id;
                $tab_mails[$i]['nom'] = $row->nom;
                $tab_mails[$i]['titre'] = $row->titre;
                $tab_mails[$i]['sujet'] = $row->sujet;
                $tab_mails[$i]['message'] = $row->message;
                $i++;
            }
                    
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/pages/mails_types.php');
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