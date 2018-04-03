<?php session_start();

    
    if(right('staffs', 1))
    {
        if($_GET['id_staff'] AND !empty($_GET['id_staff']) AND is_numeric($_GET['id_staff'])) //Un emply séléctionné
        {
            global $db;
            $id_staff = $db->SQLfix($_GET['id_staff']);



            $db->QUERY('SELECT COUNT(*) AS nb FROM staffs WHERE id ="'.$id_staff.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            
            if($row->nb) //employé existant
            {

                //nombre de connexion à managy
                $db->Query('SELECT COUNT(*) AS nb_login FROM logs WHERE texte LIKE "%(IP%" AND id_staff = "'.$id_staff.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ');
                $r = $db->Row();
                $nb_login = $r->nb_login;

                //nombre intervention prise en charge et cloturées
                $db->Query('SELECT COUNT(*) AS nb_inter
                            FROM interventions AS i
                            INNER JOIN prise_en_charge AS pec
                            ON (i.id_inter = pec.id_intervention)
                            AND (pec.compte_principal = "'.$_SESSION['compte_principal'].'")
                            WHERE i.compte_principal = "'.$_SESSION['compte_principal'].'"
                            AND i.time_cloture > 0
                            AND pec.id_staff = "'.$id_staff.'" ');
                $r = $db->Row();
                $nb_inter = $r->nb_inter;

                $db->Query('SELECT * FROM staffs WHERE id="'.$id_staff.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                $row = $db->Row();

                $prenom = $row->prenom;
                $nom = $row->nom;
                $mail = $row->mail;
                $gerant = $row->gerant;




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
                    AND compte_principal = "'.$_SESSION['compte_principal'].'" 
                    AND EXISTS (
                                    SELECT NULL FROM prise_en_charge 
                                    WHERE id_intervention = interventions.id_inter
                                    AND id_staff = "'.$id_staff.'"
                                )
                    GROUP BY annee, mois, type_atelier_rdv ';

                //echo $sql;

                $db->query($sql);

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



                $derniere_connexion = 0;
                if(AccesActivableModul('logs') AND right('logs', 1))
                {
                    $db->Query('SELECT logs.id_inter, logs.id_client, logs.texte, logs.time, i.prefix
                                    FROM logs
                                    LEFT JOIN interventions AS i
                                    ON (logs.id_inter = i.id_inter)
                                    AND (i.compte_principal="'.$_SESSION['compte_principal'].'")
                                    WHERE logs.compte_principal="'.$_SESSION['compte_principal'].'"
                                    AND logs.id_staff = "'.$id_staff.'"
                                    ORDER BY time DESC LIMIT 100');

                    $tab_logs = Array();
                    $i = 0;
                    while($row2 = $db->Row())
                    {
                        if(!$derniere_connexion AND strpos($row2->texte, '(IP'))
                        {
                            $derniere_connexion = 1;
                            $date_derniere_connexion = $row2->time;
                        }
                        $tab_logs[$i]['id_inter'] = $row2->id_inter;
                        $tab_logs[$i]['prefix'] = $row2->prefix;
                        $tab_logs[$i]['id_client'] = $row2->id_client;
                        $tab_logs[$i]['texte'] = $row2->texte;
                        $tab_logs[$i]['time'] = $row2->time;
                        $i++;
                    }

                }

                if($_GET['id_staff'] == 1) //cyril
                {
                    if(right('empty', -1)) //C'est cyril, seul moi peux voir
                    {
                        
                        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/staff.php'))
                        {
                            $no_title = true;
                            ob_start();
                            include('./templates/'.TEMPLATE_NAME.'/pages/staff.php');
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

                }
                else
                {
                    if(is_file('./templates/'.TEMPLATE_NAME.'/pages/staff.php'))
                    {
                        $no_title = true;
                        ob_start();
                        include('./templates/'.TEMPLATE_NAME.'/pages/staff.php');
                        $ModulContent = ob_get_contents();
                        ob_end_clean();
                    }
                }
            }
            else //employé innexistant
            {
                if(is_file('./templates/'.TEMPLATE_NAME.'/pages/no_staff.php'))
                {
                    ob_start();
                    include('./templates/'.TEMPLATE_NAME.'/pages/no_staff.php');
                    $ModulContent = ob_get_contents();
                    ob_end_clean();
                }
            }
            
        }
        else // Aucun employéslectionné, on dégage
        {
            if(is_file('./templates/'.TEMPLATE_NAME.'/pages/staff_nul.php'))
            {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/staff_nul.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
            }

        }
    }
    else
    {
        die('ok');
        if(is_dir('./templates/'.TEMPLATE_NAME.'/pages/'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/no_right.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
        }
    }
?>