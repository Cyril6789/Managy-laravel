<?php session_start();

    if(right('bl', 3))
    {
        if(isset($_GET['id_customer']) AND !(empty($_GET['id_customer'])) AND is_numeric($_GET['id_customer']))
        {
            global $db;
            $id_customer = $db->SQLfix($_GET['id_customer']);
            
            $db->Query('SELECT COUNT(*) AS nb FROM clients WHERE id="'.$id_customer.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            
            if($row->nb) // client existant
            {
                $db->Query('SELECT COUNT(*) AS nb
                            FROM clients
                            WHERE clients.compte_principal = "'.$_SESSION['compte_principal'].'"
                            AND EXISTS (SELECT interventions.id AS id_inter 
                                        FROM interventions 
                                        WHERE interventions.id_client=clients.id
                                        AND interventions.time_cloture > 0
                                        AND interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                                        
                                        AND NOT EXISTS (SELECT NULL 
                                                        FROM bls_interventions 
                                                        WHERE id_inter = interventions.id_inter
                                                        AND bls_interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                                                        )
                                        )
                            AND clients.id="'.$id_customer.'" ');
                $row = $db->Row();
            
                if($row->nb) // client eligible
                {
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
                                
                                AND NOT EXISTS (SELECT NULL 
                                                FROM bls_interventions 
                                                WHERE id_inter = i.id_inter
                                                AND bls_interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                                                )
                                AND i.id_client="'.$id_customer.'"
                                AND i.time_cloture > 0
                            ');
                    echo $db->Error();
                    $tab_inters = Array();
                    $i=0;
                    while($row = $db->Row())
                    {
                        $tab_inters[$i]['id_inter'] = $row->id_inter;
                        $tab_inters[$i]['projet'] = $row->projet;
                        $tab_inters[$i]['ref_chantier'] = $row->ref_chantier;
                        $tab_inters[$i]['ral'] = $row->ral;
                        $tab_inters[$i]['couleur'] = $row->nom_c;
                        $tab_inters[$i]['aspect'] = $row->nom_a;
                        $tab_inters[$i]['matiere'] = $row->nom_m;
                        
                        $db2 = New MySQL();
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
                    
                    if(is_file('./templates/'.TEMPLATE_NAME.'/sub/bl_inter.php'))
                    {
                        ob_start();
                        include('./templates/'.TEMPLATE_NAME.'/sub/bl_inter.php');
                        $ModulContent = ob_get_contents();
                        ob_end_clean();
                    }
                    
                }
                else
                {
                    if(is_file('./templates/'.TEMPLATE_NAME.'/sub/no_eligible.php'))
                    {
                        ob_start();
                        include('./templates/'.TEMPLATE_NAME.'/sub/no_eligible.php');
                        $ModulContent = ob_get_contents();
                        ob_end_clean();
                    }
                }
                
            }
            else
            {
                if(is_file('./templates/'.TEMPLATE_NAME.'/sub/no_customer.php'))
                {
                    ob_start();
                    include('./templates/'.TEMPLATE_NAME.'/sub/no_customer.php');
                    $ModulContent = ob_get_contents();
                    ob_end_clean();
                }
            }
            
                    
        }
        else
        {
            if(is_file('./templates/'.TEMPLATE_NAME.'/sub/customer_nul.php'))
            {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/sub/customer_nul.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
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