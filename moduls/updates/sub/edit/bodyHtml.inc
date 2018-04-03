<?php session_start();

    if(right('updates', -1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/edit_update.php'))
        {
            if(is_numeric($_GET['id']))
            {
                global $db;
                $id_maj = $db->SQLfix($_GET['id']);
                
                $db->Query('SELECT * FROM maj WHERE id = "'.$id_maj.'" ORDER BY timestamp DESC');
                $row = $db->Row();
                        
                $tab_update['id'] = $row->id;
                $tab_update['timestamp'] = $row->timestamp;
                $tab_update['title'] = $row->titre;
                $tab_update['sub_title'] = $row->sous_titre;
                $tab_update['text'] = $row->texte;
                
                if($tab_update['id'] > 0)
                {
                    $nouveau = false;
                    ob_start();
                    include('./templates/'.TEMPLATE_NAME.'/sub/edit_update.php');
                    $ModulContent = ob_get_contents();
                    ob_end_clean();
                }
                else
                {
                    ob_start();
                    include('./templates/'.TEMPLATE_NAME.'/sub/no_update.php');
                    $ModulContent = ob_get_contents();
                    ob_end_clean();
                }
                
                
            }
            else
            {
                $nouveau = false;
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/sub/edit_update.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
            }
        }
    }
    else
    {
        if(is_dir('./templates/'.TEMPLATE_NAME.'/pages/'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/no_right.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
        }
    }
?>