<?php session_start();

    if(right('stats', 1))
    {
        global $db;
        
        
        if(isset($_GET['mois']) AND $_GET['mois'] > 0 AND $_GET['mois'] < 12)
            $mois = $db->SQLfix($_GET['mois']);
        else
            $mois = date('m');
        
        if(isset($_GET['annee']) AND $_GET['annee'] <= date('Y'))
            $annee = $db->SQLfix($_GET['annee']);
        else
            $annee = date('Y');
        
        
        
        $date_debut = mktime(0, 0, 0, $mois, 1, $annee);
        $nb_jour = date('t', $date_debut);
        $data_fin = mktime(23, 59, 59, $mois, $nb_jour, $annee);
        
        $db->Query('SELECT prenom, id FROM staffs 
                    WHERE EXISTS (
                                    SELECT null 
                                    FROM prise_en_charge AS pec
                                    INNER JOIN interventions AS i
                                    ON (pec.id_intervention = i.id_inter)
                                    AND (i.compte_principal="'.$_SESSION['compte_principal'].'")
                                    WHERE pec.id_staff = staffs.id
                                    AND (i.time_cloture BETWEEN "'.$date_debut.'" AND "'.$data_fin.'")
                                    AND pec.compte_principal="'.$_SESSION['compte_principal'].'"
                                  )
                    AND compte_principal="'.$_SESSION['compte_principal'].'" ');
       $tab_inters = Array();
        $tab_staffs = Array();
        $i=0;
        
        while($row = $db->Row())
        {
            $tab_staffs[$i]['prenom'] = $row->prenom;
            $tab_staffs[$i]['id'] = $row->id;
            
            
            for($jour = 1; $jour<=$nb_jour; $jour++) //on parcour chaque jours du mois
            {
                $debut_jour = mktime(0, 0, 0, $mois, $jour, $annee);
                $fin_jour = $debut_jour + (24 * 60 * 60);
                
                $db2 = new MySQL();
                $sql = '   SELECT id_inter, temps_accroche_decroche, temps_application, temps_emballage
                                FROM interventions AS i
                                INNER JOIN prise_en_charge AS pec
                                ON (pec.id_intervention = i.id_inter)
                                AND (pec.compte_principal="'.$_SESSION['compte_principal'].'")
                                WHERE pec.id_staff = "'.$row->id.'"
                                AND i.compte_principal="'.$_SESSION['compte_principal'].'"
                                AND i.time_cloture BETWEEN "'.$debut_jour.'" AND "'.$fin_jour.'"
                                
                               ';
                $db2->Query($sql);
                $j=0;
                $duree_jour[$row->id][$jour] = 0;
                while($row2 = $db2->Row())
                {
                    $tab_inters[$j][$row->id][$jour]['id_inter'] = $row2->id_inter;
                    $duree = 0;
                    $db3 = new Mysql();
                    $db3->Query('   SELECT duree 
                                    FROM traitements_interventions
                                    WHERE id_inter = "'.$row2->id_inter.'"
                                    AND compte_principal="'.$_SESSION['compte_principal'].'" ');                  
                    while($row3 = $db3->Row())
                    {
                        if($row3->duree != '')
                        {
                            $tab_d = explode(':', $row3->duree);
                            $duree += $tab_d[0] + $tab_d[1] / 60;
                        }
                    }
                    
                    $db3->Query('   SELECT duree 
                                    FROM etapes_interventions 
                                    WHERE id_inter = "'.$row2->id_inter.'"
                                    AND compte_principal="'.$_SESSION['compte_principal'].'" ');                  
                    while($row3 = $db3->Row())
                    {
                        if($row3->duree != '')
                        {
                            $tab_d = explode(':', $row3->duree);
                            $duree += $tab_d[0] + $tab_d[1] / 60;
                        }
                    }
                    
                    $t1 = explode(':', $row2->temps_accroche_decroche);
                    $duree += $t1[0] + $t1[1]/60;
                    
                    $t2 = explode(':', $row2->temps_application);
                    $duree += $t2[0] + $t2[1]/60;
                    
                    $t3 = explode(':', $row2->temps_emballage);
                    $duree += $t3[0] + $t3[1]/60;
                    
                    
                    
                    $db3->Query('SELECT COUNT(*) AS nb_staff FROM prise_en_charge WHERE id_intervention = "'.$row2->id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                    $row3 = $db3->Row();
                    
                    $duree = $duree / $row3->nb_staff;
                    $tab_inters[$j][$row->id][$jour]['duree'] = $duree;
                    
                    $duree_jour[$row->id][$jour] += $duree;
                    
                    
                    
                    $j++;
                }        
                
                
            }
            
            
            
            
            
            $i++;
        }
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/stats.php'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/stats.php');
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