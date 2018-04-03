<?php session_start();

    if(right('admin', 1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/notifys.php'))
        {
            
            $sql = 'SELECT id, nom, id_categorie FROM notifications_mail ORDER by id ASC';
            $db->Query($sql);


            $i=0;
            $tab_notifys = Array();
            while($row = $db->Row())
            {
                $tab_notifys[$i]['id'] = $row->id;
                $tab_notifys[$i]['nom'] = $row->nom;
                $tab_notifys[$i]['id_categorie'] = $row->id_categorie;
                $i++;
            }

            $sql = 'SELECT id_notification FROM notifications_desactivees WHERE compte_principal="'.$_SESSION['compte_principal'].'"';

            $db->Query($sql);

            $tab_disabled_notifys = Array();
            while ($row = $db->Row())
            {
                $tab_disabled_notifys[] = $row->id_notification;
            }

            $sql = 'SELECT id, nom FROM categories_notifications_mail';
            $db->Query($sql);
            $i=0;
            $tab_cat = array();
            while($row = $db->Row())
            {
                $tab_cat[$i]['id'] = $row->id;
                $tab_cat[$i]['nom'] = $row->nom;
                $i++;
            }

            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/notifys.php');
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