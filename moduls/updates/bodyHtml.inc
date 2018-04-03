<?php session_start();

    if(right('updates', 1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/updates.php'))
        {
            global $db;
            $db->Query('SELECT * FROM maj ORDER BY timestamp DESC');
            $i = 0;
            $tab_updates = Array();
            while($row = $db->Row())
            {
                $tab_updates[$i]['timestamp'] = $row->timestamp;
                $tab_updates[$i]['title'] = $row->titre;
                $tab_updates[$i]['sub_title'] = $row->sous_titre;
                $tab_updates[$i]['text'] = nl2br($row->texte);
                $i++;
                
               
                $db2 = new MySQL();
                $db2->Query('SELECT COUNT(*) AS nb_maj FROM vu_maj WHERE id_maj = "'.$row->id.'" AND id_staff="'.$_SESSION['id'].'" ');
                $row2 = $db2->Row();
                
                if(!$row2->nb_maj)
                    $db2->Query('INSERT INTO vu_maj (id_maj, id_staff) VALUES ("'.$row->id.'", "'.$_SESSION['id'].'")');
            }




            ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/updates.php');
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