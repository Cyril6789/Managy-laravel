<?php session_start();

    if(right('inter', 7))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/all_interventions_informatiques.php'))
        {
            global $db;



                $sql = 'SELECT clients.titre, clients.nom AS nom_c, clients.prenom AS prenom_c, inter.id_inter, inter.prefix, clients.nom, inter.type_atelier_rdv, inter.rdv_debut, inter.rdv_annule, inter.panne, inter.resolution, inter.time_cloture, inter.time_ouverture, m.nom AS nom_m, (SELECT COUNT(*) FROM prise_en_charge AS pec WHERE pec.id_intervention=inter.id_inter AND  pec.compte_principal="' . $_SESSION['compte_principal'] . '" ) AS nb_pec
                            FROM interventions AS inter
                            INNER JOIN clients
                            ON (inter.id_client = clients.id)
                            LEFT JOIN materiels AS m
                            ON (inter.id_materiel = m.id)
                            AND (m.compte_principal = "'.$_SESSION['compte_principal'].'")
                            WHERE inter.compte_principal="' . $_SESSION['compte_principal'] . '"
                            AND clients.compte_principal="' . $_SESSION['compte_principal'] . '"
                            ORDER BY inter.id_inter';

            $db->Query($sql);
            echo $db->Error();
            $i=0;
            $tab_inters = Array();
            while ($row = $db->Row()) 
            {
                $tab_inters[$i]['id'] = $row->id_inter;
                $tab_inters[$i]['prefix'] = $row->prefix;
                $tab_inters[$i]['titre'] = $row->titre;
                $tab_inters[$i]['name'] = $row->nom_c;
                $tab_inters[$i]['lname'] = $row->prenom_c;
                $tab_inters[$i]['ouverture'] = $row->time_ouverture;
                $tab_inters[$i]['pec'] = $row->nb_pec;
                $tab_inters[$i]['materiel'] = $row->nom_m;
                $tab_inters[$i]['cloture'] = $row->time_cloture;
                $tab_inters[$i]['type_a_r'] = $row->type_atelier_rdv;
                $tab_inters[$i]['panne'] = $row->panne;
                $tab_inters[$i]['resolution'] = $row->resolution;
                $tab_inters[$i]['rdv_debut'] = $row->rdv_debut;
                $tab_inters[$i]['rdv_annule'] = $row->rdv_annule;
                $i++;
            }
            
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/all_interventions_informatiques.php');
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