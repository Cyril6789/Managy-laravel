<?php session_start();

    if(right('inter', 22))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/restitution.php'))
        {
            global $db;
            $sql = 'SELECT id_inter, prefix, id_client, matos, panne, resolution, materiel_ajoute, time_cloture 
                    FROM interventions 
                    WHERE time_restitution = "0" 
                    AND type_atelier_rdv="1" 
                    AND time_cloture>"0" 
                    AND compte_principal="'.$_SESSION['compte_principal'].'" 
                    ORDER BY id ASC';
            $db->Query($sql);

            $tab_inters = Array();
            $i=0;
            while($row = $db->Row())
            {
                $tab_inters[$i]['id_inter'] = $row->id_inter;
                $tab_inters[$i]['prefix'] = $row->prefix;
                $tab_inters[$i]['id_client'] = $row->id_client;
                $tab_inters[$i]['matos'] = $row->matos;
                $tab_inters[$i]['panne'] = $row->panne;
                $tab_inters[$i]['resolution'] = $row->resolution;
                $tab_inters[$i]['materiel_ajoute'] = $row->materiel_ajoute;
                $tab_inters[$i]['time_cloture'] = $row->time_cloture;
                $i++;
            }

            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/restitution.php');
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