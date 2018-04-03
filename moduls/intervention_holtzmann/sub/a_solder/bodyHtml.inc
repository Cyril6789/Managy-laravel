<?php session_start();

    if(right('inter', 10))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/interventions_a_solder.php'))
        {
            global $db;

            $db->Query('SELECT i.id_inter AS id_inter, i.forfait, i.time_ouverture, i.ref_chantier, i.deconditionne, i.quantite_couleur, i.projet, c.nom AS nom_c, c.prenom, c.titre, coul.nom AS nom_coul, coul.ral AS nom_ral, a.nom AS nom_a, i.id_matiere, i.nombre_balancelle, i.temps_accroche_decroche, i.temps_application, i.temps_emballage, i.commentaires
                        FROM interventions AS i
                        INNER JOIN clients AS c
                        ON (i.id_client = c.id)
                        LEFT JOIN couleurs AS coul
                        ON (i.id_couleur = coul.id)
                        AND (coul.compte_principal = "'.$_SESSION['compte_principal'].'" )
                        LEFT JOIN aspects AS a
                        ON (i.id_finition = a.id)
                        AND (a.compte_principal = "'.$_SESSION['compte_principal'].'")
                        WHERE i.compte_principal="'.$_SESSION['compte_principal'].'"
                        AND c.compte_principal="'.$_SESSION['compte_principal'].'"
                        AND i.time_cloture > "0"
                        AND i.solde = "0"
                        ORDER BY id_inter ASC
                        ');

            $i = 0;
            $tab_inters = Array();
            while($data = $db->Row())
            {
                $tab_inters[$i]['id_inter'] = $data->id_inter;
                $tab_inters[$i]['nom'] = $data->nom_c;
                $tab_inters[$i]['prenom'] = $data->prenom;
                $tab_inters[$i]['titre'] = $data->titre;
                $tab_inters[$i]['forfait'] = $data->forfait;
                $tab_inters[$i]['nom_coul'] = $data->nom_coul;
                $tab_inters[$i]['nom_ral'] = $data->nom_ral;
                $tab_inters[$i]['aspect'] = $data->nom_a;
                $tab_inters[$i]['qte_coul'] = $data->quantite_couleur;
                $tab_inters[$i]['nb_balancelle'] = $data->nombre_balancelle;
                $tab_inters[$i]['ref_chantier'] = $data->ref_chantier;
                $tab_inters[$i]['decon'] = $data->deconditionne;
                $tab_inters[$i]['tps_ac'] = $data->temps_accroche_decroche;
                $tab_inters[$i]['appli'] = $data->temps_application;
                $tab_inters[$i]['emballage'] = $data->temps_emballage;



                $db2 = new MySQL();
                $db2->Query('   SELECT duree, nom
                                FROM traitements_interventions
                                INNER JOIN traitements
                                ON (traitements.id = traitements_interventions.id_traitement)
                                AND (traitements.compte_principal = "'.$_SESSION['compte_principal'].'")
                                WHERE traitements_interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                                AND traitements_interventions.id_inter ="'.$data->id_inter.'"
                                AND traitements_interventions.duree != "NULL"
                                AND traitements_interventions.duree != "00:00"
                                AND traitements_interventions.duree != ""
                                ');
                echo $db2->Error();

                $j = 0;
                $tab_inters[$i]['traitements'] = Array();
                while($data2 = $db2->Row())
                {
                    $tab_inters[$i]['traitements'][$j]['nom'] = $data2->nom;
                    $tab_inters[$i]['traitements'][$j]['duree'] = $data2->duree;
                    $j++;
                }


                $db2->Query('   SELECT duree, nom
                                FROM etapes_interventions
                                INNER JOIN etapes
                                ON (etapes.id = etapes_interventions.id_etape)
                                AND (etapes.compte_principal = "'.$_SESSION['compte_principal'].'")
                                AND (etapes.temps= "1")
                                WHERE etapes_interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                                AND etapes_interventions.id_inter ="'.$data->id_inter.'"
                                AND etapes_interventions.duree != "NULL"
                                AND etapes_interventions.duree != "00:00"
                                AND etapes_interventions.duree != ""
                                ');
                echo $db2->Error();

                $j = 0;
                $tab_inters[$i]['etapes'] = Array();
                while($data2 = $db2->Row())
                {
                    $tab_inters[$i]['etapes'][$j]['nom'] = $data2->nom;
                    $tab_inters[$i]['etapes'][$j]['duree'] = $data2->duree;
                    $j++;
                }



                $i++;
            }


            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/interventions_a_solder.php');
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