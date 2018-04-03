<?php session_start();

    
    if(right('customers', 1))
    {
        if($_GET['id_customer'] AND !empty($_GET['id_customer']) AND is_numeric($_GET['id_customer'])) //Un client séléctionné
        {
            global $db;
            $id_customer = $db->SQLfix($_GET['id_customer']);
            
            
            $db->Query('SELECT id FROM clients WHERE id > "'.$id_customer.'" ORDER BY id ASC LIMIT 1');
            $row = $db->Row();
            $suivant = $row->id;
            
            
            $db->QUERY('SELECT COUNT(*) AS nb FROM clients WHERE id ="'.$id_customer.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            
            if($row->nb) //client existant
            {
                if(is_file('./templates/'.TEMPLATE_NAME.'/sub/customer.php'))
                {
                    //GOOGLE MAP //
                    $db->QUERY('SELECT * FROM clients WHERE id="'.$id_customer.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                    $row = $db->Row();
                    $archive = $row->archive;


                    $adress = $row->adresse.' '.$row->cp.' '.$row->ville;
                    require('./classes/GoogleMapAPI.class.php');
                    $gmap = new GoogleMapAPI();
                    $gmap->setDivId('map');
                    //$gmap->setDirectionDivId('route');
                    $gmap->setCenter($adress);
                    $gmap->setEnableWindowZoom(true);
                    //$gmap->setEnableAutomaticCenterZoom(true);
                    $gmap->setDisplayDirectionFields(true);
                    $gmap->setClusterer(true);
                    $gmap->setSize('100%','250px');
                    $gmap->setZoom(14);
                    $gmap->setLang('fr');
                    $gmap->setDefaultHideMarker(false);


                    $gmap->addMarkerByAddress($adress, $row->nom.' '.$row->prenom, $row->nom.' '.$row->prenom);
                    $gmap->generate();

                    //FIN GOOGLE MAP //


                    //contacts
                    if($row->pro_part == 1)
                    {
                        $pro_part = 1;
                        $db->Query('SELECT * FROM clients WHERE id_parent = "'.$row->id.'" AND pro_part = 2 AND compte_principal="'.$_SESSION['compte_principal'].'"  ORDER BY  id ASC');
                        $tab_contacts = Array();
                        $i = 0;
                        while($row2 = $db->Row())
                        {

                            
                            $tab_contacts[$i]['pro_part']  =$row2->pro_part;
                            $tab_contacts[$i]['id']  =$row2->id;
                            $tab_contacts[$i]['nom']  =$row2->nom;
                            $tab_contacts[$i]['prenom'] =$row2->prenom;
                            $tab_contacts[$i]['mail'] =$row2->mail;
                            $tab_contacts[$i]['adresse'] =$row2->adresse;
                            $tab_contacts[$i]['adresse_suite'] =$row2->adresse_suite;
                            $tab_contacts[$i]['cp'] =$row2->cp;
                            $tab_contacts[$i]['ville'] =$row2->ville;
                            $tab_contacts[$i]['fixe'] =$row2->fixe;
                            $tab_contacts[$i]['portable'] =$row2->portable;

                            $i++;

                        }
                    }
                    //fin contacts

                    //nom de la société parente
                    if($row->pro_part == 2 AND $row->id_parent)
                    {
                        $db->Query('SELECT id, nom FROM clients WHERE id="'.$row->id_parent.'" AND pro_part = 1  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                        $row3 = $db->Row();
                        $parent_society_name = $row3->nom;
                        $parent_society_id = $row3->id;
                    }

                    if(AccesActivableModul('intervention_holtzmann'))
                    {
                        //Interventions du client
                        $sql = 'SELECT inter.id_inter, clients.nom, inter.projet, inter.time_cloture, inter.time_ouverture, (SELECT COUNT(*) FROM prise_en_charge AS pec WHERE pec.id_intervention=inter.id_inter AND  pec.compte_principal="' . $_SESSION['compte_principal'] . '" ) AS nb_pec, couleurs.nom AS nom_c, couleurs.ral, aspects.nom AS nom_a, matieres.nom as nom_m
                            FROM interventions AS inter
                            INNER JOIN clients
                            ON (inter.id_client = clients.id)
                            LEFT JOIN couleurs
                            ON (inter.id_couleur = couleurs.id)
                            AND (couleurs.compte_principal="' . $_SESSION['compte_principal'] . '" )
                            LEFT JOIN aspects
                            ON (inter.id_finition = aspects.id)
                            AND (aspects.compte_principal="' . $_SESSION['compte_principal'] . '" )
                            LEFT JOIN matieres
                            ON (inter.id_matiere = matieres.id)
                            AND (matieres.compte_principal="' . $_SESSION['compte_principal'] . '")
                            WHERE clients.id = "' . $id_customer . '"
                            AND inter.compte_principal="' . $_SESSION['compte_principal'] . '"
                            AND clients.compte_principal="' . $_SESSION['compte_principal'] . '"
                            ORDER BY inter.id_inter';
                    }

                    if(AccesActivableModul('intervention_informatique'))
                    {
                        //Interventions du client
                        $sql = 'SELECT inter.id_inter, inter.prefix, clients.nom, inter.type_atelier_rdv, inter.rdv_debut, inter.rdv_annule, inter.panne, inter.resolution, inter.time_cloture, inter.time_ouverture, m.nom AS nom_m, (SELECT COUNT(*) FROM prise_en_charge AS pec WHERE pec.id_intervention=inter.id_inter AND  pec.compte_principal="' . $_SESSION['compte_principal'] . '" ) AS nb_pec
                            FROM interventions AS inter
                            INNER JOIN clients
                            ON (inter.id_client = clients.id)
                            LEFT JOIN materiels AS m
                            ON (inter.id_materiel = m.id)
                            AND (m.compte_principal = "'.$_SESSION['compte_principal'].'")
                            WHERE clients.id = "' . $id_customer . '"
                            AND inter.compte_principal="' . $_SESSION['compte_principal'] . '"
                            AND clients.compte_principal="' . $_SESSION['compte_principal'] . '"
                            ORDER BY inter.id_inter';
                    }
                    $db->Query($sql);
                    echo $db->Error();
                    $i=0;
                    $tab_inters = Array();
                    while ($row2 = $db->Row()) 
                    {
                        $tab_inters[$i]['id'] = $row2->id_inter;
                        $tab_inters[$i]['prefix'] = $row2->prefix;
                        $tab_inters[$i]['name'] = $row2->nom;
                        $tab_inters[$i]['projet'] = $row2->projet;
                        $tab_inters[$i]['panne'] = $row2->panne;
                        $tab_inters[$i]['resolution'] = $row2->resolution;
                        $tab_inters[$i]['pec'] = $row2->nb_pec;
                        $tab_inters[$i]['couleur'] = $row2->nom_c;
                        $tab_inters[$i]['ral'] = $row2->ral;
                        $tab_inters[$i]['aspect'] = $row2->nom_a;
                        $tab_inters[$i]['matiere'] = $row2->nom_m;
                        $tab_inters[$i]['materiel'] = $row2->nom_m;
                        $tab_inters[$i]['ouverture'] = $row2->time_ouverture;
                        $tab_inters[$i]['cloture'] = $row2->time_cloture;
                        $tab_inters[$i]['type_a_r'] = $row2->type_atelier_rdv;
                        $tab_inters[$i]['rdv_debut'] = $row2->rdv_debut;
                        $tab_inters[$i]['rdv_annule'] = $row2->rdv_annule;
                        $i++;
                    }
                        
                    if(AccesActivableModul('pack_maintenance') and right('pack-maintenance', 1))
                    {

                        if(is_file('./moduls/pack_maintenance/blocs/tableau.inc'))
                            include('./moduls/pack_maintenance/blocs/tableau.inc');
                    }




                    /*GRAPHIQUE*/

                   // $sql = 'SELECT ';
                    $sql = 'SELECT COUNT(*) AS nb, YEAR(FROM_UNIXTIME(time_cloture)) AS annee, MONTH(FROM_UNIXTIME(time_cloture)) AS mois, type_atelier_rdv
                    FROM interventions
                    WHERE ( ';
                    $y = date('Y');

                    $i=1;
                    for($m = date('n') - 11; $m<=date('n'); $m++)
                    {
                        if ($m < 1)
                        {
                            $mois = $m + 12;
                            $annee = $y - 1;

                        }
                        else
                        {
                            $mois = $m;
                            $annee = $y;
                        }
                        $timestamp_debut = mktime(0,0,0,$mois,1, $annee);
                        $timestamp_fin = mktime(23,59,59,$mois,date('t', $timestamp_debut), $annee);



                        if($i == 1)
                            $sql .= ' (time_cloture BETWEEN "'.$timestamp_debut.'" AND "'.$timestamp_fin.'" )';
                        else
                            $sql .= '  OR (time_cloture BETWEEN "'.$timestamp_debut.'" AND "'.$timestamp_fin.'" )';



                        $i++;
                    }

                    $sql .= ')
                    AND compte_principal = "'.$_SESSION['compte_principal'].'" AND id_client = "'.$id_customer.'"
                    GROUP BY annee, mois, type_atelier_rdv ';

                    //echo $sql;

                    $db->query($sql);
                    //echo $db->GetHTML();

                    $i=0;
                    $tab_graph = Array();
                    while($rowg = $db->Row())
                    {
                        $tab_graph[$i]['nb'] = $rowg->nb;
                        $tab_graph[$i]['annee'] = $rowg->annee;
                        $tab_graph[$i]['mois'] = $rowg->mois;
                        $tab_graph[$i]['type'] = $rowg->type_atelier_rdv;
                        $i++;
                    }

                    /*
                     * Sms envoyés
                     */

                    $tab_sms = Array();
                    $i=0;
                    $db->Query('SELECT * FROM sms_histo WHERE id_client = "'.$id_customer.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ORDER BY timestamp DESC');
                    while($rowsms = $db->Row())
                    {
                        $tab_sms[$i]['id_inter'] = $rowsms->id_inter;
                        $tab_sms[$i]['message'] = $rowsms->message;
                        $tab_sms[$i]['numero'] = $rowsms->destinataire;
                        $tab_sms[$i]['staff'] = $rowsms->id_staff;
                        $tab_sms[$i]['date'] = $rowsms->timestamp;
                        $i++;
                    }

                    //mot de passe
                    $db->Query('SELECT id, service FROM mdp WHERE id_client = "'.$id_customer.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ORDER BY id ASC');
                    $tab_pass = Array();
                    $i = 0;
                    while($row1 = $db->Row())
                    {
                        $tab_pass[$i]['id'] = $row1->id;
                        $tab_pass[$i]['service'] = $row1->service;
                        $i++;
                    }


                    //logs de l'intervention

                    $db->Query('SELECT * FROM logs WHERE id_client="'.$id_customer.'" AND compte_principal="'.$_SESSION['compte_principal'].'"  ORDER BY time DESC '); //LIMIT 15');
                    $i=0;

                    $tab_logs = Array();
                    while($rowlog = $db->Row())
                    {
                        $tab_logs[$i]['id_staff'] = $rowlog->id_staff;
                        $tab_logs[$i]['texte'] = $rowlog->texte;
                        $tab_logs[$i]['time'] = $rowlog->time;
                        $i++;
                    }


                    /*
                     * Commentaires
                     */

                    $sql = 'SELECT id, id_staff, texte, timestamp FROM commentaires WHERE id_client = "'.$id_customer.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY timestamp DESC';
                    $db->Query($sql);

                    $tab_commentaires = Array();
                    $i=0;
                    while($row_comm = $db->Row())
                    {
                        $tab_commentaires[$i]['id'] = $row_comm->id;
                        $tab_commentaires[$i]['id_staff'] = $row_comm->id_staff;
                        $tab_commentaires[$i]['texte'] = $row_comm->texte;
                        $tab_commentaires[$i]['timestamp'] = $row_comm->timestamp;
                        $i++;
                    }
                    $nb_commentaires = $i;


                    /*
                     * Notes
                     */
                    $sql = 'SELECT id, titre, texte, crypte, timestamp FROM notes WHERE id_client="'.$id_customer.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id DESC';
                    $tab_notes = Array();
                    $i=0;
                    $db->Query($sql);
                    while($row2 = $db->Row())
                    {
                        $tab_notes[$i]['id'] = $row2->id;
                        $tab_notes[$i]['titre'] = $row2->titre;
                        $tab_notes[$i]['texte'] = $row2->texte;
                        $tab_notes[$i]['crypte'] = $row2->crypte;
                        $tab_notes[$i]['timestamp'] = $row2->timestamp;
                        $i++;
                    }

                    $no_title = true;
                    ob_start();
                    include('./templates/'.TEMPLATE_NAME.'/sub/customer.php');
                    $ModulContent = ob_get_contents();
                    ob_end_clean();
                }
                
            }
            else //client innexistant
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
        else // Aucun client sélectionné, on dégage
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
        if(is_dir('./templates/'.TEMPLATE_NAME.'/pages/'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/no_right.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
        }
    }
?>