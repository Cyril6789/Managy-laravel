<?php session_start();


    if(right('calendar', 1))
    {
        
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/calendar.php'))
        {
            
            global $db;
            $db->Query('SELECT rdv.id, date, date_fin, titre, nom, prenom, adresse, cp, ville, lieu_client_atelier FROM rdv INNER JOIN clients ON (clients.id = rdv.id_client) AND (clients.compte_principal="'.$_SESSION['compte_principal'].'") WHERE rdv.compte_principal="'.$_SESSION['compte_principal'].'" ');
            $tab_rdv = Array();
            $i=0;
            while($row = $db->Row())
            {
                
                $tab_rdv[$i]['id'] = $row->id;
                $tab_rdv[$i]['title'] = $row->titre.' '.$row->nom.' '.$row->prenom;
                $tab_rdv[$i]['jour'] = date('d', $row->date);
                $tab_rdv[$i]['mois'] = date('m', $row->date) - 1;
                $tab_rdv[$i]['annee'] = date('Y', $row->date);
                $tab_rdv[$i]['heure']  = date('H', $row->date);
                $tab_rdv[$i]['minute']  = date('i', $row->date);
                $tab_rdv[$i]['jourf'] = date('d', $row->date_fin);
                $tab_rdv[$i]['moisf'] = date('m', $row->date_fin) - 1;
                $tab_rdv[$i]['anneef'] = date('Y', $row->date_fin);
                $tab_rdv[$i]['heuref']  = date('H', $row->date_fin);
                $tab_rdv[$i]['minutef']  = date('i', $row->date_fin );
                $tab_rdv[$i]['adresse']  = trim($row->adresse).' '.$row->cp.' '.$row->ville;
                $tab_rdv[$i]['sms_veille']  = ''; //$row->sms_veille;
                $tab_rdv[$i]['sms_15']  = ''; //$row->sms_15;
                $tab_rdv[$i]['sms_satisfaction']  = ''; //$row->sms_satisfaction;
                $tab_rdv[$i]['lieu_client_atelier']  = $row->lieu_client_atelier;
                $i++;
            }
            
            
            

            $sql = 'SELECT i.id_inter, i.prefix, rdv_debut, rdv_fin, titre, nom, prenom, clients.adresse, clients.cp, clients.ville
                        FROM interventions AS i 
                        INNER JOIN clients 
                        ON (clients.id = i.id_client) 
                        AND (clients.compte_principal="'.$_SESSION['compte_principal'].'") 
                        WHERE i.compte_principal="'.$_SESSION['compte_principal'].'" 
                        AND i.rdv_annule = "0" AND type_atelier_rdv = "2" ';


//Interventions sur site (les miennes uniquement
            $sql = 'SELECT i.id_inter, i.prefix, rdv_debut, rdv_fin, titre, clients.nom, clients.prenom, clients.adresse, clients.cp, clients.ville, s.cal_color
                        FROM interventions AS i 
                        INNER JOIN clients 
                        ON (clients.id = i.id_client) 
                        AND (clients.compte_principal="'.$_SESSION['compte_principal'].'") 
                        INNER JOIN prise_en_charge AS pec
                        ON (i.id_inter = pec.id_intervention)
                        INNER JOIN staffs AS s
                        ON (pec.id_staff = s.id)
                        AND s.compte_principal = "'.$_SESSION['compte_principal'].'"
                        WHERE i.compte_principal="'.$_SESSION['compte_principal'].'" 
                        AND i.rdv_annule = "0" AND type_atelier_rdv = "2"
                        AND pec.id_staff = "'.$_SESSION['id'].'" 
                        ';

            $db->Query($sql);
            echo $db->Error();
            $tab_inter_site = Array();
            $i=0;
            while($row = $db->Row())
            {
                
                $tab_inter_site[$i]['id'] = $row->id_inter;
                $tab_inter_site[$i]['prefix'] = $row->prefix;
                $tab_inter_site[$i]['title'] = $row->titre.' '.$row->nom.' '.$row->prenom;
                $tab_inter_site[$i]['jour'] = date('d', $row->rdv_debut);
                $tab_inter_site[$i]['mois'] = date('m', $row->rdv_debut) - 1;
                $tab_inter_site[$i]['annee'] = date('Y', $row->rdv_debut);
                $tab_inter_site[$i]['heure']  = date('H', $row->rdv_debut);
                $tab_inter_site[$i]['minute']  = date('i', $row->rdv_debut);
                $tab_inter_site[$i]['jourf'] = date('d', $row->rdv_fin);
                $tab_inter_site[$i]['moisf'] = date('m', $row->rdv_fin) - 1;
                $tab_inter_site[$i]['anneef'] = date('Y', $row->rdv_fin);
                $tab_inter_site[$i]['heuref']  = date('H', $row->rdv_fin);
                $tab_inter_site[$i]['minutef']  = date('i', $row->rdv_fin );
                if($row->cal_color)
                    $tab_inter_site[$i]['cal_color']  = $row->cal_color;
                else
                    $tab_inter_site[$i]['cal_color'] = '#4D7496';
                $tab_inter_site[$i]['adresse']  = str_replace('`', '', strip_tags(rtrim($row->adresse).' '.$row->cp.' '.$row->ville));
                $i++;
            }


//Interventions sur site non prises en charges
            $sql = 'SELECT i.id_inter, i.prefix, rdv_debut, rdv_fin, titre, clients.nom, clients.prenom, clients.adresse, clients.cp, clients.ville
                        FROM interventions AS i 
                        INNER JOIN clients 
                        ON (clients.id = i.id_client) 
                        AND (clients.compte_principal="'.$_SESSION['compte_principal'].'") 
                        WHERE i.compte_principal="'.$_SESSION['compte_principal'].'" 
                        AND i.rdv_annule = "0" AND type_atelier_rdv = "2"
                         AND NOT EXISTS (                    
                                    SELECT NULL                                    
                                    FROM prise_en_charge AS pec                                     
                                    WHERE pec.id_intervention = i.id_inter                                    
                                    AND compte_principal="'.$_SESSION['compte_principal'].'"
                                    )      
                        ';

            $db->Query($sql);
            echo $db->Error();
            while($row = $db->Row())
            {

                $tab_inter_site[$i]['id'] = $row->id_inter;
                $tab_inter_site[$i]['prefix'] = $row->prefix;
                $tab_inter_site[$i]['title'] = $row->titre.' '.$row->nom.' '.$row->prenom;
                $tab_inter_site[$i]['jour'] = date('d', $row->rdv_debut);
                $tab_inter_site[$i]['mois'] = date('m', $row->rdv_debut) - 1;
                $tab_inter_site[$i]['annee'] = date('Y', $row->rdv_debut);
                $tab_inter_site[$i]['heure']  = date('H', $row->rdv_debut);
                $tab_inter_site[$i]['minute']  = date('i', $row->rdv_debut);
                $tab_inter_site[$i]['jourf'] = date('d', $row->rdv_fin);
                $tab_inter_site[$i]['moisf'] = date('m', $row->rdv_fin) - 1;
                $tab_inter_site[$i]['anneef'] = date('Y', $row->rdv_fin);
                $tab_inter_site[$i]['heuref']  = date('H', $row->rdv_fin);
                $tab_inter_site[$i]['minutef']  = date('i', $row->rdv_fin );
                $tab_inter_site[$i]['cal_color'] = '#888888';
                $tab_inter_site[$i]['adresse']  = str_replace('`', '', strip_tags(rtrim($row->adresse).' '.$row->cp.' '.$row->ville));
                $i++;
            }

            /*
             * Inter autres techniciens
             */
            $sql = 'SELECT id FROM staffs WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND id != "'.$_SESSION['id'].'" ';
            $db2 = new MySQL();
            $db2->Query($sql);
            while($row2 = $db2->Row())
            {
                $sql = 'SELECT i.id_inter, i.prefix, rdv_debut, rdv_fin, titre, clients.nom, clients.prenom, clients.adresse, clients.cp, clients.ville, s.cal_color
                        FROM interventions AS i 
                        INNER JOIN clients 
                        ON (clients.id = i.id_client) 
                        AND (clients.compte_principal="'.$_SESSION['compte_principal'].'")
                        INNER JOIN prise_en_charge AS pec
                        ON (i.id_inter = pec.id_intervention)
                        INNER JOIN staffs AS s
                        ON (pec.id_staff = s.id)
                        AND s.compte_principal = "'.$_SESSION['compte_principal'].'" 
                        WHERE i.compte_principal="'.$_SESSION['compte_principal'].'" 
                        AND i.rdv_annule = "0" AND type_atelier_rdv = "2"
                        AND pec.id_staff = "'.$row2->id.'" 
                        ';

                $db->Query($sql);
                while($row = $db->Row())
                {
                    $tab_inter_site[$i]['id'] = $row->id_inter;
                    $tab_inter_site[$i]['prefix'] = $row->prefix;
                    $tab_inter_site[$i]['title'] = $row->titre.' '.$row->nom.' '.$row->prenom;
                    $tab_inter_site[$i]['jour'] = date('d', $row->rdv_debut);
                    $tab_inter_site[$i]['mois'] = date('m', $row->rdv_debut) - 1;
                    $tab_inter_site[$i]['annee'] = date('Y', $row->rdv_debut);
                    $tab_inter_site[$i]['heure']  = date('H', $row->rdv_debut);
                    $tab_inter_site[$i]['minute']  = date('i', $row->rdv_debut);
                    $tab_inter_site[$i]['jourf'] = date('d', $row->rdv_fin);
                    $tab_inter_site[$i]['moisf'] = date('m', $row->rdv_fin) - 1;
                    $tab_inter_site[$i]['anneef'] = date('Y', $row->rdv_fin);
                    $tab_inter_site[$i]['heuref']  = date('H', $row->rdv_fin);
                    $tab_inter_site[$i]['minutef']  = date('i', $row->rdv_fin );
                    if($row->cal_color)
                        $tab_inter_site[$i]['cal_color']  = $row->cal_color;
                    else
                        $tab_inter_site[$i]['cal_color'] = '#4D7496';
                    $tab_inter_site[$i]['adresse']  = str_replace('`', '', strip_tags(rtrim($row->adresse).' '.$row->cp.' '.$row->ville));
                    $i++;
                }
            }


            if(0) //AccesActivableModul('intervention_holtzmann'))
            {

                $sql = 'SELECT id_inter, time_ouverture, time_cloture, clients.nom, clients.titre, clients.prenom
                        FROM interventions
                        INNER JOIN clients
                        ON (clients.id = interventions.id_client)
                        AND (clients.compte_principal = "'.$_SESSION['compte_principal'].'")
                        WHERE interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                        AND time_cloture > 0
                        AND type_atelier_rdv = 1
                        ';
                $tab_inters = Array();
                $i=0;
                $db->Query($sql);
                while($row = $db->Row())
                {
                    $tab_inters[$i]['id_inter'] = $row->id_inter;
                    $tab_inters[$i]['titre'] = $row->titre;
                    $tab_inters[$i]['nom'] = $row->nom;
                    $tab_inters[$i]['prenom'] = $row->prenom;
                    $tab_inters[$i]['jour'] = date('d', $row->time_ouverture);
                    $tab_inters[$i]['mois'] = date('m', $row->time_ouverture);
                    $tab_inters[$i]['annee'] = date('Y', $row->time_ouverture);
                    $tab_inters[$i]['heure'] = date('H', $row->time_ouverture);
                    $tab_inters[$i]['minute'] = date('i', $row->time_ouverture);
                    $tab_inters[$i]['jourf'] = date('d', $row->time_cloture);
                    $tab_inters[$i]['moisf'] = date('m', $row->time_cloture);
                    $tab_inters[$i]['anneef'] = date('Y', $row->time_cloture);
                    $tab_inters[$i]['heuref'] = date('H', $row->time_cloture);
                    $tab_inters[$i]['minutef'] = date('i', $row->time_cloture);
                    $i++;
                }


            }


            /*
             * Interventions sur site sans rdv urgente
             */
            $sql = 'SELECT id_inter, clients.id AS id_client, interventions.panne, prefix
                    FROM interventions 
                    INNER JOIN clients
                    ON (interventions.id_client = clients.id)
                    AND (clients.compte_principal = "'.$_SESSION['compte_principal'].'")
                    WHERE rdv_debut = "0" 
                    AND interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                    AND type_atelier_rdv = 2 
                    AND rdv_annule = "0"
                    AND priorite="1"';
            $db->Query($sql);
            $tab_inters_sans_rdv = Array();
            $i = 0;
            while($row = $db->Row())
            {
                $tab_inters_sans_rdv[$i]['id_inter'] = 's'.$row->id_inter;
                $tab_inters_sans_rdv[$i]['id_client'] = $row->id_client;
                $tab_inters_sans_rdv[$i]['panne'] = $row->panne;
                $tab_inters_sans_rdv[$i]['priorite'] = 1;
                $tab_inters_sans_rdv[$i]['prefix'] = $row->prefix;
                $i++;
            }

            $sql = 'SELECT id_inter, clients.id AS id_client, interventions.panne, prefix
                    FROM interventions 
                    INNER JOIN clients
                    ON (interventions.id_client = clients.id)
                    AND (clients.compte_principal = "'.$_SESSION['compte_principal'].'")
                    WHERE rdv_debut = "0" 
                    AND interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                    AND type_atelier_rdv = 2 
                    AND rdv_annule = "0"
                    AND priorite="0"';
            $db->Query($sql);
            while($row = $db->Row())
            {
                $tab_inters_sans_rdv[$i]['id_inter'] = 's'.$row->id_inter;
                $tab_inters_sans_rdv[$i]['id_client'] = $row->id_client;
                $tab_inters_sans_rdv[$i]['panne'] = $row->panne;
                $tab_inters_sans_rdv[$i]['priorite'] = 0;
                $tab_inters_sans_rdv[$i]['prefix'] = $row->prefix;
                $i++;
            }

            $sql = 'SELECT id_inter, clients.id AS id_client, interventions.panne, prefix
                    FROM interventions 
                    INNER JOIN clients
                    ON (interventions.id_client = clients.id)
                    AND (clients.compte_principal = "'.$_SESSION['compte_principal'].'")
                    WHERE rdv_debut = "0" 
                    AND interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                    AND type_atelier_rdv = 2 
                    AND rdv_annule = "0"
                    AND priorite="-1"';
            $db->Query($sql);
            while($row = $db->Row())
            {
                $tab_inters_sans_rdv[$i]['id_inter'] = 's'.$row->id_inter;
                $tab_inters_sans_rdv[$i]['id_client'] = $row->id_client;
                $tab_inters_sans_rdv[$i]['panne'] = $row->panne;
                $tab_inters_sans_rdv[$i]['priorite'] = -1;
                $tab_inters_sans_rdv[$i]['prefix'] = $row->prefix;
                $i++;
            }

            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/pages/calendar.php');
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