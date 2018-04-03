<?php session_start();

    if(right('updates', -1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/list_updates.php'))
        {
                 global $db;
                $db->Query('SELECT * FROM maj ORDER BY timestamp DESC');
                $i = 0;
                $tab_updates = Array();
                while($row = $db->Row())
                {
                    $tab_updates[$i]['id'] = $row->id;
                    $tab_updates[$i]['timestamp'] = $row->timestamp;
                    $tab_updates[$i]['title'] = $row->titre;
                    $tab_updates[$i]['sub_title'] = $row->sous_titre;
                    $tab_updates[$i]['text'] = nl2br($row->texte);
                    $i++;
                }
                
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/sub/list_updates.php');
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