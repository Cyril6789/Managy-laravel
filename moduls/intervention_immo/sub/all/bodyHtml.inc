<?php session_start();

    if(right('inter', 7))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/all_interventions.php'))
        {
            global $db;
            $sql = 'SELECT inter.id_inter, clients.nom, clients.prenom, inter.projet, inter.time_cloture, inter.time_ouverture, (SELECT COUNT(*) FROM prise_en_charge AS pec WHERE pec.id_intervention=inter.id_inter AND  pec.compte_principal="'.$_SESSION['compte_principal'].'" ) AS nb_pec, ta.nom AS nom_ta, inter.adresse, inter.cp, inter.ville, prix
                    FROM interventions AS inter
                    INNER JOIN clients
                    ON (inter.id_client = clients.id)
                    LEFT JOIN types_affaires AS ta
                    ON (inter.id_type_affaire = ta.id)
                    AND (ta.compte_principal = "'.$_SESSION['compte_principal'].'")
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
                $tab_inters[$i]['type_affaire'] = $row->nom_ta;
                $tab_inters[$i]['adresse'] = $row->adresse;
                $tab_inters[$i]['cp'] = $row->cp;
                $tab_inters[$i]['ville'] = $row->ville;
                $tab_inters[$i]['prix'] = $row->prix;
                
                if($row->time_cloture)
                    $tab_inters[$i]['cloture'] = 1;
                $i++;
            }
            
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/all_interventions_immo.php');
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