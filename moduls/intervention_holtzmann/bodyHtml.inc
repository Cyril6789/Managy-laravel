<?php session_start();
     //if(!$multi_inter)
        
if(right('inter', 1))
{
        if(!is_numeric($id_inter))
        {
            if(is_numeric($_GET['id_inter']) AND $_GET['id_inter'] > 0)
            {
                global  $db;
                
                $id_inter = (int) $db->SQLfix($_GET['id_inter']);
            }
            else
                $id_inter = 0;
        
        }
            
        
        
        if(!$id_inter)
        {
            
            //Aucun numéro, on redirife vers intervention_nul
            
            if(is_file('./templates/'.TEMPLATE_NAME.'/pages/intervention_nul.php'))
            {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/intervention_nul.php');
                $ModulContent .= ob_get_contents();
                ob_end_clean();
            }
        }
        else //numero ok
        {
            
            global $db;
            
            $db->Query('SELECT id_inter, projet FROM interventions WHERE id_inter="'.$id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            
            if($row->id_inter) //intervention existance
            {
               


                if(is_file('./templates/'.TEMPLATE_NAME.'/pages/intervention.php'))
                {

                    $db->Query('SELECT inter.id_inter, inter.deconditionne, inter.reception_materiel AS rm, inter.forfait, inter.ref_chantier, inter.time_cloture, inter.time_ouverture, inter.id_matiere, inter.temps_accroche_decroche, inter.nombre_balancelle, inter.temps_emballage, inter.temps_application, inter.id_couleur, inter.projet, inter.id_finition, inter.quantite_couleur, inter.commentaires, clients.id AS id_c, clients.pro_part, clients.titre, clients.nom, clients.prenom, clients.adresse, clients.adresse_suite, clients.cp, clients.ville, clients.fixe, clients.portable, clients.mail
                                FROM interventions AS inter 
                                INNER JOIN clients
                                ON (inter.id_client = clients.id)
                                WHERE inter.id_inter="'.$id_inter.'"
                                AND inter.compte_principal="'.$_SESSION['compte_principal'].'"
                                AND clients.compte_principal="'.$_SESSION['compte_principal'].'" ');
                    
                    echo $db->Error();
                    $row = $db->Row();
                    
                    $ref_chantier = $row->ref_chantier;
                    $rm = $row->rm;
                    $forfait = $row->forfait;
                    $id_couleur = $row->id_couleur;
                    $id_aspect = $row->id_finition;
                    $deconditonne = $row->deconditionne;
                    $deconditionne = $row->deconditionne;
                    $id_matiere = $row->id_matiere;
                    $nom_projet = $row->projet;
                    $quantite_couleur = $row->quantite_couleur;
                    $temps_ad = $row->temps_accroche_decroche;
                    $temps_application = $row->temps_application;
                    $temps_emballage = $row->temps_emballage;
                    $commentaires = $row->commentaires;
                    $nb_balancelle = $row->nombre_balancelle;
                    $time_ouverture = $row->time_ouverture;
                    $time_cloture = $row->time_cloture;
                    if($time_cloture)
                        $cloture = 1;
                    else
                        $cloture = 0;
                    
                     $page_title .= ' - '.$row->projet .' - Ouvert '.parse_date($time_ouverture);
                    
                    $db->Query('SELECT * FROM couleurs WHERE id="'.$id_couleur.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                    $row2 = $db->Row();
                    $nom_couleur = $row2->nom;
                    $ral_couleur = $row2->ral;
                    
                    $db->Query('SELECT * FROM aspects WHERE id="'.$id_aspect.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                    $row2 = $db->Row();
                    $nom_aspect = $row2->nom;
                    
                    $db->Query('SELECT stock FROM stocks_couleurs WHERE id_couleur="'.$id_couleur.'" AND id_aspect="'.$id_aspect.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                    $row2 = $db->Row();
                    $stock_couleur = $row2->stock;
                    $max_couleur = $stock_couleur + $quantite_couleur;
                    
                    
                    
                    
                    $id_c = $row->id_c;
                    $titre = $row->titre;
                    $name = $row->nom;
                    $lname = $row->prenom;
                    $cpy_adress = $row->adresse;
                    $cpy_adress_suite = $row->adresse_suite;
                    $cpy_cp = $row->cp;
                    $cp_city = $row->ville;
                    $cpy_tel1 = $row->fixe;
                    $cpy_tel2 = $row->portable;
                    $cpy_mail = $row->mail;
                    $pro_part = $row->pro_part;
                    
                    $liste_mails = Array();
                    $liste_mails[0]['id'] = $id_c;
                    $liste_mails[0]['nom'] = $titre.' '.$name.' '.$lname;
                    $liste_mails[0]['mail'] = $cpy_mail;
                    
                    $liste_smss = Array();
                    $liste_smss[0]['id'] = $id_c;
                    $liste_smss[0]['titre'] = $titre;
                    $liste_smss[0]['nom'] = $name;
                    $liste_smss[0]['lname'] = $lname;
                    $liste_smss[0]['sms'] = $cpy_tel2;

                    if($pro_part == 1) // contacts
                    {
                        
                        $db->Query('SELECT * FROM clients WHERE id_parent = "'.$id_c.'" AND pro_part = 2  AND compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY  id ASC');
                        $tab_contacts = Array();
                        $i = 0;
                        while($row2 = $db->Row())
                        {
                            $tab_contacts[$i]['titre']  =$row2->titre;
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
                            $liste_mails[$i]['id'] = $row2->id;
                            $liste_mails[$i]['nom'] = $row2->titre.' '.$row2->nom.' '.$row2->prenom;
                            $liste_mails[$i]['mail'] = $row2->mail;
                            $liste_smss[$i]['id'] = $row2->id;
                            $liste_smss[$i]['nom'] = $row2->titre.' '.$row2->nom.' '.$row2->prenom;
                            $liste_smss[$i]['sms'] = $row2->portable;

                        }
                    }

                    //Traitements
                    $db->Query('SELECT t.nom, t_i.fait, t.id, t_i.duree
                                FROM traitements AS t
                                INNER JOIN traitements_interventions AS t_i
                                ON (t_i.id_traitement = t.id)
                                WHERE t_i.id_inter = "'.$id_inter.'" 
                                AND t.compte_principal="'.$_SESSION['compte_principal'].'" 
                                AND t_i.compte_principal="'.$_SESSION['compte_principal'].'" 
                                ORDER BY t.id
                                ');
                    $i = 0;
                    $tab_traitements = array();
                    while($row = $db->Row())
                    {
                        $tab_traitements[$i]['name'] = $row->nom;
                        $tab_traitements[$i]['done'] = $row->fait;
                        $tab_traitements[$i]['id'] = $row->id;
                        $tab_traitements[$i]['time'] = $row->duree;
                        $i++;
                    }
                        
                    //Fin traitement
                    
                    //etapes
                    $db->Query('SELECT e.nom, e_i.fait, e.id, e.temps AS is_time, e_i.duree, e_i.type
                                FROM etapes AS e
                                INNER JOIN etapes_interventions AS e_i
                                ON (e_i.id_etape = e.id)
                                WHERE e_i.id_inter = "'.$id_inter.'"
                                AND e.compte_principal="'.$_SESSION['compte_principal'].'" 
                                AND e_i.compte_principal="'.$_SESSION['compte_principal'].'" 
                                ORDER BY e.id
                             ');
                    $i = 0;
                    $tab_etapes = array();
                    while($row = $db->Row())
                    {
                        $tab_etapes[$i]['name'] = $row->nom;
                        $tab_etapes[$i]['done'] = $row->fait;
                        $tab_etapes[$i]['id'] = $row->id;
                        $tab_etapes[$i]['is_time'] = $row->is_time;
                        $tab_etapes[$i]['time'] = $row->duree;
                        $tab_etapes[$i]['type'] = $row->type;
                        $i++;
                    }
                    //fin etapes
                    
                    
                    $db->Query('SELECT * FROM couleurs  WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id'); //toutes les couleurs pour la liste déroulante
                    $i=0;
                    $tab_colors = Array();
                    while ($row = $db->Row()) 
                    {
                        $tab_colors[$i]['id'] = $row->id;
                        $tab_colors[$i]['name'] = $row->nom;
                        $tab_colors[$i]['ral'] = $row->ral;
                        $i++;
                    }

                    $db->Query('SELECT * FROM aspects  WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY nom'); //tous les aspects, pour la liste déroulante
                    $i=0;
                    $tab_aspects = Array();
                    while ($row = $db->Row()) 
                    {
                        $tab_aspects[$i]['id'] = $row->id;
                        $tab_aspects[$i]['name'] = $row->nom;
                        $i++;
                    }
                    
                    $db->Query('SELECT * FROM traitements  WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id'); //tous les traitements pour les checkbox
                    $i=0;
                    $traitements = Array();
                    while ($row = $db->Row()) 
                    {
                        $traitements[$i]['id'] = $row->id;
                        $traitements[$i]['name'] = $row->nom;
                        $i++;
                    }
                    
                    $db->Query('SELECT * FROM etapes  WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id'); //toutes les étapes pour les checkbox
                    $i=0;
                    $etapes = Array();
                    while ($row = $db->Row()) 
                    {
                        $etapes[$i]['id'] = $row->id;
                        $etapes[$i]['name'] = $row->nom;
                        $i++;
                    }
                    
                    $db->Query('SELECT * FROM matieres  WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id'); //toutes les matières
                    $i=0;
                    $tab_matieres = Array();
                    while ($row = $db->Row()) 
                    {
                        $tab_matieres[$i]['id'] = $row->id;
                        $tab_matieres[$i]['name'] = $row->nom;
                        $i++;
                    }
                    
                    $db->Query('SELECT COUNT(*) AS nb FROM prise_en_charge WHERE id_intervention = "'.$id_inter.'" AND id_staff = "'.$_SESSION['id'].'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                    $row = $db->Row();
                    $pris_en_charge = $row->nb; //true; //false;
                    
                    if(!$pris_en_charge OR $cloture)
                    {
                        $disabled = 'disabled';
                        $link = FALSE;
                    }
                    else
                    {
                        $disabled = '';
                        $link = TRUE;
                    }
                    
                    
                    
                    //logs de l'intervention
                    $db->Query('SELECT * FROM logs WHERE id_inter="'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'"  ORDER BY time DESC '); //LIMIT 15');
                    $i=0;
                    $tab_logs = Array();
                    while($row = $db->Row())
                    {
                        $tab_logs[$i]['id_staff'] = $row->id_staff;
                        $tab_logs[$i]['texte'] = $row->texte;
                        $tab_logs[$i]['time'] = $row->time;
                        $i++;
                    }
                    
                    
                    //Elements pour le BL
                    
                    $sql = '';
                    
                    
                    $db->Query('SELECT e.id, e.description, u.nom, u.id AS id_u, e.qte
                                FROM elements_bls AS e
                                INNER JOIN unites_bls AS u
                                ON (u.id = e.id_unite)
                                AND (u.compte_principal = "'.$_SESSION['compte_principal'].'" )
                                WHERE e.id_inter= "'.$id_inter.'"
                                AND e.compte_principal="'.$_SESSION['compte_principal'].'" 
                                ORDER BY e.id  ');
                    $i=0;
                    $tab_elements_bl = array();
                    while($row = $db->Row())
                    {
                        $tab_elements_bl[$i]['id'] = $row->id;
                        $tab_elements_bl[$i]['description'] = $row->description;
                        $tab_elements_bl[$i]['unite'] = $row->nom;
                        $tab_elements_bl[$i]['id_unite'] = $row->id_u;
                        $tab_elements_bl[$i]['qte'] = $row->qte;
                        $i++;
                    }
                    
                    
                    //toutes les unités des BL
                    $db->Query('SELECT * FROM unites_bls WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');
                    $i=0;
                    $tab_unites = Array();
                    while($row = $db->Row())
                    {
                        $tab_unites[$i]['id'] = $row->id;
                        $tab_unites[$i]['nom'] = $row->nom;
                        $i++;
                    }

                    /*
                     * Commentaires
                     */

                    $sql = 'SELECT id, id_staff, texte, timestamp FROM commentaires WHERE id_inter = "'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY timestamp DESC';
                    $db->Query($sql);

                    $tab_commentaires = Array();
                    $i=0;
                    while($row = $db->Row())
                    {
                        $tab_commentaires[$i]['id'] = $row->id;
                        $tab_commentaires[$i]['id_staff'] = $row->id_staff;
                        $tab_commentaires[$i]['texte'] = $row->texte;
                        $tab_commentaires[$i]['timestamp'] = $row->timestamp;
                        $i++;
                    }
                    $nb_commentaires = $i;
                    
                    ob_start();
                    include('./templates/'.TEMPLATE_NAME.'/pages/intervention.php');
                    $ModulContent .= ob_get_contents();
                    ob_end_clean();
                }
            }
            else
            {
                ob_start();
                if(is_file('./templates/'.TEMPLATE_NAME.'/pages/no_intervention.php'))
                    include('./templates/'.TEMPLATE_NAME.'/pages/no_intervention.php');
                $ModulContent .= ob_get_contents();
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