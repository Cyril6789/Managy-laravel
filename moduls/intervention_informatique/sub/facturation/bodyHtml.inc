<?php session_start();

    if(right('inter', 20))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/facturation.php'))
        {
            global $db;
            $sql = 'SELECT interventions.id_inter, interventions.prefix, materiels.nom, rdv_debut, type_atelier_rdv, interventions.id_client, matos, panne, resolution, message_interne, materiel_ajoute, time_cloture, m.mouvements AS pmm, m.commentaires AS pmc 
                    FROM interventions 
                    LEFT JOIN materiels
                    ON (interventions.id_materiel = materiels.id)
                    AND (materiels.compte_principal = "'.$_SESSION['compte_principal'].'")
                    LEFT JOIN maintenance AS m
                    ON (m.id_inter = interventions.id_inter)
                    AND (m.id_client = interventions.id_client)
                    AND (m.compte_principal = "'.$_SESSION['compte_principal'].'")
                    WHERE facture = "0"
                    AND time_cloture>"0" 
                    AND interventions.compte_principal="'.$_SESSION['compte_principal'].'" 
                    AND rdv_annule = "0"
                    AND garantie = "0"
                    AND (
                            NOT EXISTS (
                                    SELECT NULL
                                    FROM maintenance
                                    WHERE maintenance.id_inter = interventions.id_inter
                                    AND compte_principal="' . $_SESSION['compte_principal'] . '"
                                    )
                            OR force_facturation = "1"
                        )
                    AND EXISTS (                    
                                SELECT NULL                                    
                                FROM prestations_effectuees                                                          
                                WHERE prestations_effectuees.id_inter = interventions.id_inter                                    
                                AND compte_principal="'.$_SESSION['compte_principal'].'"
                                )          
                    ORDER BY interventions.id ASC';
           //echo $sql;

            /*
             *
             */
            $db->Query($sql);

            echo $db->Error();
            $tab_inters = Array();

            $i=0;


            while($row = $db->Row())
            {

                $tab_inters[$i]['id_inter'] = $row->id_inter;
                $tab_inters[$i]['prefix'] = $row->prefix;
                $tab_inters[$i]['id_client'] = $row->id_client;
                $tab_inters[$i]['type_atelier_rdv'] = $row->type_atelier_rdv;
                $tab_inters[$i]['materiel'] = $row->nom;
                $tab_inters[$i]['rdv_debut'] = $row->rdv_debut;
                $tab_inters[$i]['matos'] = $row->matos;
                $tab_inters[$i]['panne'] = $row->panne;
                $tab_inters[$i]['resolution'] = $row->resolution;
                $tab_inters[$i]['materiel_ajoute'] = $row->materiel_ajoute;
                $tab_inters[$i]['message_interne'] = $row->message_interne;
                $tab_inters[$i]['time_cloture'] = $row->time_cloture;
                $tab_inters[$i]['pmm'] = $row->pmm;
                $tab_inters[$i]['pmc'] = $row->pmc;
                $i++;

                $db2 = new MySQL();
                $sql = 'SELECT designation, duree FROM prestations_effectuees WHERE id_inter="'.$row->id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ';
                $db2->Query($sql);
                $j = 0;
                $tab_prestas[$row->id_inter] = Array();
                while($row2 = $db2->Row())
                {
                    $tab_prestas[$row->id_inter][$j]['designation'] = $row2->designation;
                    $tab_prestas[$row->id_inter][$j]['duree'] = $row2->duree;
                    $j++;
                }
            }


            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/facturation.php');
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