<?php session_start();

    if(right('bl', 3))
    {
        if(isset($_GET['id_bl']) AND !(empty($_GET['id_bl'])) AND is_numeric($_GET['id_bl']))
        {
            $db = new MySQL();
            $id_bl = $db->SQLfix($_GET['id_bl']);
            
            $db->Query('SELECT COUNT(*) AS nb FROM bls WHERE id_bl="'.$id_bl.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            if($row->nb > 0)
            {
                $db->Query('SELECT id_client FROM bls WHERE id_bl="'.$id_bl.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                $row = $db->Row();
                $id_client = $row->id_client;
                
                $db->Query('SELECT i.id_inter, i.projet, i.ref_chantier, c.ral, c.nom AS nom_c, a.nom AS nom_a, m.nom AS nom_m
                                FROM interventions AS i
                                LEFT JOIN couleurs AS c
                                ON (i.id_couleur=c.id)
                                AND (c.compte_principal = "'.$_SESSION['compte_principal'].'")
                                LEFT JOIN aspects AS a
                                ON (i.id_finition=a.id)
                                AND (a.compte_principal = "'.$_SESSION['compte_principal'].'")
                                LEFT JOIN matieres AS m
                                ON (i.id_matiere=m.id)
                                AND (m.compte_principal = "'.$_SESSION['compte_principal'].'")
                                WHERE i.compte_principal = "'.$_SESSION['compte_principal'].'"
                                AND (
                                        NOT EXISTS (SELECT NULL 
                                                            FROM bls_interventions 
                                                            WHERE id_inter = i.id_inter
                                                            AND bls_interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                                                            )
                                        
                                        OR EXISTS   (SELECT NULL 
                                                    FROM bls_interventions
                                                    WHERE id_inter = i.id_inter
                                                    AND bls_interventions.id_bl = "'.$id_bl.'"
                                                    AND bls_interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                                                    ) 
                                    )
                                AND i.time_cloture > 0
                            ');
                    $tab_inters = Array();
                    $i=0;
                    while($row = $db->Row())
                    {
                       
                        $db2 = New MySQL();
                        
                        $db2->Query('SELECT COUNT(*) AS nb FROM interventions WHERE id_inter="'.$row->id_inter.'" AND id_client="'.$id_client.'" AND compte_principal = "'.$_SESSION['compte_principal'].'"');
                        $row2 = $db2->Row();
                        if($row2->nb > 0)
                        {
                        
                            $db2->Query('SELECT COUNT(*) AS nb FROM bls_interventions AS b_i WHERE b_i.id_inter="'.$row->id_inter.'" AND b_i.compte_principal="'.$_SESSION['compte_principal'].'" ');      
                            $row2 = $db2->Row();


                            $tab_inters[$i]['checked'] =   $row2->nb;     
                            $tab_inters[$i]['id_inter'] = $row->id_inter;
                            $tab_inters[$i]['projet'] = $row->projet;
                            $tab_inters[$i]['ref_chantier'] = $row->ref_chantier;
                            $tab_inters[$i]['ral'] = $row->ral;
                            $tab_inters[$i]['couleur'] = $row->nom_c;
                            $tab_inters[$i]['aspect'] = $row->nom_a;
                            $tab_inters[$i]['matiere'] = $row->nom_m;
                            
                            $db2->Query('SELECT non_ref_chantier FROM bls_interventions AS b_i WHERE b_i.id_inter="'.$row->id_inter.'" AND b_i.compte_principal="'.$_SESSION['compte_principal'].'" ');      
                                                        
                            $row2 = $db2->Row();
                            $tab_inters[$i]['masquer_ref_chantier'] = $row2->non_ref_chantier;
                            
                            
                            
                            $db2->Query('SELECT e_b.description, u.nom, e_b.qte
                                         FROM elements_bls AS e_b
                                         INNER JOIN unites_bls AS u
                                         ON (u.id = e_b.id_unite)
                                         AND (u.compte_principal = "'.$_SESSION['compte_principal'].'")
                                         WHERE e_b.id_inter = "'.$row->id_inter.'"
                                         AND e_b.compte_principal = "'.$_SESSION['compte_principal'].'"
                                         ORDER BY e_b.id ASC
                            ');
                            $j=0;
                            while($row2 = $db2->Row())
                            {
                                $tab_elements[$row->id_inter][$j]['description'] = $row2->description;
                                $tab_elements[$row->id_inter][$j]['unite'] = $row2->nom;
                                $tab_elements[$row->id_inter][$j]['qte'] = $row2->qte;
                                $j++;
                            }
                            if(!$j)
                                $tab_inters[$i]['no_element'] = true;
                            $i++;
                        }
                    }
                    
                    
                    $db->Query('SELECT descriptif FROM bls WHERE id_bl ="'.$id_bl.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                    $row = $db->Row();
                    $descriptif = $row->descriptif;
                    
                    
                    if(is_file('./templates/'.TEMPLATE_NAME.'/sub/bl_edit.php'))
                    {
                        ob_start();
                        include('./templates/'.TEMPLATE_NAME.'/sub/bl_edit.php');
                        $ModulContent = ob_get_contents();
                        ob_end_clean();
                    }
                    
                
            }
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