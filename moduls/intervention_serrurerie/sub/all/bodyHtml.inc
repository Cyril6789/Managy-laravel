<?php session_start();

    if(right('inter', 7))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/all_interventions.php'))
        {
            global $db;
            $sql = 'SELECT inter.id_inter, clients.nom, clients.prenom, inter.projet, inter.time_cloture, inter.time_ouverture, (SELECT COUNT(*) FROM prise_en_charge AS pec WHERE pec.id_intervention=inter.id_inter AND  pec.compte_principal="'.$_SESSION['compte_principal'].'" ) AS nb_pec, couleurs.nom AS nom_c, couleurs.ral, aspects.nom AS nom_a, matieres.nom as nom_m
                    FROM interventions AS inter
                    INNER JOIN clients
                    ON (inter.id_client = clients.id)
                    LEFT JOIN couleurs
                    ON (inter.id_couleur = couleurs.id)
                    AND (couleurs.compte_principal = "'.$_SESSION['compte_principal'].'")
                    LEFT JOIN aspects
                    ON (inter.id_finition = aspects.id)
                    AND (aspects.compte_principal = "'.$_SESSION['compte_principal'].'")
                    LEFT JOIN matieres
                    ON (inter.id_matiere = matieres.id)
                    AND (matieres.compte_principal = "'.$_SESSION['compte_principal'].'")
                    WHERE  inter.compte_principal="'.$_SESSION['compte_principal'].'" 
                    AND clients.compte_principal="'.$_SESSION['compte_principal'].'" 
                    ORDER BY inter.id_inter DESC';
            
            $db->Query($sql);
            echo $db->Error();
            $i=0;
            $tab_inters = Array();
            while ($row = $db->Row()) 
            {
                $tab_inters[$i]['id'] = $row->id_inter;
                $tab_inters[$i]['name'] = $row->nom;
                $tab_inters[$i]['lname'] = $row->prenom;
                $tab_inters[$i]['projet'] = $row->projet;
                $tab_inters[$i]['pec'] = $row->nb_pec;
                $tab_inters[$i]['couleur'] = $row->nom_c;
                $tab_inters[$i]['ral'] = $row->ral;
                $tab_inters[$i]['aspect'] = $row->nom_a;
                $tab_inters[$i]['matiere'] = $row->nom_m;
                $tab_inters[$i]['cloture'] = $row->time_cloture;
                $i++;
            }
            
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/all_interventions.php');
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