<?php session_start();

    if(right('automatismes', 1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/automatismes.php'))
        {

            /*
             * Liste des automatismes
             */

            $sql = 'SELECT automatismes.id, id_sms, id_mail, type_atelier_rdv, action, j, h, m, heurefixe, htime, mtime, immediat, dest_client_staff, mails_types.nom, sms_types.titre
                    FROM automatismes
                    LEFT JOIN sms_types
                    ON (id_sms = sms_types.id)
                    AND (sms_types.compte_principal = "'.$_SESSION['compte_principal'].'")
                    LEFT JOIN mails_types 
                    ON (id_mail = mails_types.id)
                    AND (mails_types.compte_principal = "'.$_SESSION['compte_principal'].'")
                    WHERE automatismes.compte_principal = "'.$_SESSION['compte_principal'].'"
                    ORDER BY id ASC
            ';

            $db->Query($sql);

            $i=0;
            $tab_auto = Array();
            while($row = $db->Row())
            {
                $tab_auto[$i]['id'] = $row->id;
                $tab_auto[$i]['id_sms'] = $row->id_sms;
                $tab_auto[$i]['id_mail'] = $row->id_mail;
                $tab_auto[$i]['type_atelier_rdv'] = $row->type_atelier_rdv;
                $tab_auto[$i]['action'] = $row->action;
                $tab_auto[$i]['j'] = $row->j;
                $tab_auto[$i]['h'] = $row->h;
                $tab_auto[$i]['m'] = $row->m;
                $tab_auto[$i]['heurefixe'] = $row->heurefixe;
                $tab_auto[$i]['htime'] = $row->htime;
                $tab_auto[$i]['mtime'] = $row->mtime;
                $tab_auto[$i]['immediat'] = $row->immediat;
                $tab_auto[$i]['dest'] = $row->dest_client_staff;
                $tab_auto[$i]['nom_mail'] = $row->nom;
                $tab_auto[$i]['nom_sms'] = $row->titre;
                $i++;
            }







            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/pages/automatismes.php');
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