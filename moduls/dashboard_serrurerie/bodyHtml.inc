<?php session_start();

    //include('./moduls/dashboard/sidebar.inc');
if(right('dash', 1))
{
    function nb_days($time)
    {
        $day = date(j, $time);
        $month = date(n, $time);
        $year = date(Y, $time);
        
        $day_today = date(j);
        $month_today = date(n);
        $year_today = date(Y);
        
        $timestamp = mktime(0, 0, 0, $month, $day, $year);
        $timestamp_today = mktime(0, 0, 0, $month_today, $day_today, $year_today);
        
        $diff = floor(($timestamp_today - $timestamp) / 86400);
        
        return $diff;
    }

    $template_name = TEMPLATE_NAME;
    if(is_file('./templates/'.TEMPLATE_NAME.'/pages/dashboard_serrurerie.php'))
    {
            global $db;
            //PEC

            //toutes sauf celles prises en charge par soi-même
            $sql = 'SELECT inter.id_inter, clients.nom, clients.prenom, inter.projet, (SELECT SUM(heures) AS heures FROM releves_horaires WHERE id_inter = inter.id_inter AND compte_principal = "'.$_SESSION['compte_principal'].'" ) AS heures, (SELECT SUM(minutes) AS minutes FROM releves_horaires WHERE id_inter = inter.id_inter AND compte_principal = "'.$_SESSION['compte_principal'].'" ) AS minutes
                    FROM interventions AS inter
                    INNER JOIN clients
                    ON (inter.id_client = clients.id)
                    WHERE inter.compte_principal="'.$_SESSION['compte_principal'].'" 
                    AND clients.compte_principal="'.$_SESSION['compte_principal'].'" 
                    AND inter.time_cloture = 0
                    ORDER BY id_inter';
            
            $db->Query($sql);
            echo $db->Error();
            $i=0;
            $tab_inters = Array();
            while ($row = $db->Row()) 
            {
                $tab_inters[$i]['id'] = $row->id_inter;
                $tab_inters[$i]['name'] = $row->nom;
                $tab_inters[$i]['lname'] = $row->prenom;
                $tab_inters[$i]['chantier'] = $row->projet;
                $tab_inters[$i]['heures'] = $row->heures;
                $tab_inters[$i]['minutes'] = $row->minutes;
                $i++;
            }
        
            
            
            
            
            /*
             * Statistiques
             * 
             */
            if(right('dash', 2))
            {
                $display_stats = True;
                
                $ajd_deb = mktime(0, 0, 0, date("n"), date("d"), date("Y"));
                $ajd_fin = $ajd_deb + ( 60 * 60 * 24);
                $hier_deb = $ajd_deb - (60*60*24);
                $hier_fin = $ajd_deb;
                
                $db->Query('SELECT COUNT(*) AS nb_ajd FROM interventions WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND time_cloture BETWEEN "'.$ajd_deb.'" AND "'.$ajd_fin.'" ');
                $row = $db->Row();
                $nb_ajd = $row->nb_ajd;
                
                $db->Query('SELECT COUNT(*) AS nb_hier FROM interventions WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND time_cloture BETWEEN "'.$hier_deb.'" AND "'.$hier_fin.'" ');
                $row = $db->Row();
                $nb_hier = $row->nb_hier; 
                
                if($nb_hier > 0)
                {
                    if($nb_ajd > 0)
                        $pourcent_jour = (($nb_ajd * 100) / $nb_hier ) - 100;
                    else
                        $pourcent_jour = ( $nb_hier * 100 )  - 2 * ($nb_hier * 100);
                }
                else
                {
                    if($nb_ajd > 0)
                        $pourcent_jour = $nb_ajd * 100;
                    else
                        $pourcent_jour = 0;
                            
                }
                if($pourcent_jour > 0)
                {
                    $signe_j = '+';
                    $class_j = 'class=green';
                }
                else
                {
                    if($pourcent_jour == 0)
                    {
                        $signe_j = '';
                        $class_j = '';
                    }
                    else
                    {
                        $class_j = 'class=red';
                    }
                }
                
                $pourcent_jour = round($pourcent_jour);
                
                
                
                $ajd_deb = mktime(0, 0, 0, date("n"), date("d"), date("Y"));
                $semaine_debut = $ajd_deb - ((date('N')- 1) * 60*60*24) ;
                $semaine_fin = $semaine_debut + ( 60 * 60 * 24 * 7);
                $semaine_derniere_debut = $semaine_debut - ( 60 * 60 * 24 * 7);
                $semaine_derniere_fin = $semaine_debut;
                
                $db->Query('SELECT COUNT(*) AS nb_semaine FROM interventions WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND time_cloture BETWEEN "'.$semaine_debut.'" AND "'.$semaine_fin.'" ');
                $row = $db->Row();
                $nb_semaine = $row->nb_semaine;
                $sql = 'SELECT COUNT(*) AS nb_semaine_derniere FROM interventions WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND time_cloture BETWEEN "'.$semaine_derniere_debut.'" AND "'.$semaine_derniere_fin.'" ';
                
                $db->Query($sql);
                $row = $db->Row();
                $nb_semaine_derniere_aff = $row->nb_semaine_derniere; 
                $num_jour_semaine = date('N');
                $nb_semaine_derniere = round($nb_semaine_derniere_aff / 7 * $num_jour_semaine); 
                
                
                
                if($nb_semaine_derniere > 0)
                {
                    if($nb_semaine > 0)
                        $pourcent_semaine = (($nb_semaine * 100) / $nb_semaine_derniere ) - 100;
                    else
                        $pourcent_semaine = ( $nb_semaine_derniere * 100 )  - 2 * ($nb_semaine_derniere * 100);
                }
                else
                {
                    if($nb_semaine > 0)
                        $pourcent_semaine = $nb_semaine * 100;
                    else
                        $pourcent_semaine = 0;
                            
                }
                if($pourcent_semaine > 0)
                {
                    $signe_s = '+';
                    $class_s = 'class=green';
                }
                else
                {
                    if($pourcent_semaine == 0)
                    {
                        $signe_s = '';
                        $class_s = '';
                    }
                    else
                    {
                        $class_s = 'class=red';
                    }
                }
                
                $pourcent_semaine = round($pourcent_semaine);
                
                
                
                $mois_deb = mktime(0, 0, 0, date("n"), 1, date("Y"));
                $mois_fin = $mois_deb + (60*60*24*date('t'));
                
                
                $time_mois_dernier  = $mois_deb - 1;
                $mois_dernier_debut = mktime(0, 0, 0, date("n", $time_mois_dernier), 1, date("Y", $time_mois_dernier));
                $mois_dernier_fin = $mois_deb;
                

                $db->Query('SELECT COUNT(*) AS nb_mois FROM interventions WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND time_cloture BETWEEN "'.$mois_deb.'" AND "'.$mois_fin.'" ');
                $row = $db->Row();
                $nb_mois = $row->nb_mois;
                
                $db->Query('SELECT COUNT(*) AS nb_mois_dernier FROM interventions WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND time_cloture BETWEEN "'.$mois_dernier_debut.'" AND "'.$mois_dernier_fin.'" ');
                $row = $db->Row();
                $nb_mois_dernier_aff = $row->nb_mois_dernier; 
                $num_jour_mois = date('j');
                $nb_mois_dernier = round($nb_mois_dernier_aff / date('t') * $num_jour_mois); 
                
                if($nb_mois_dernier > 0)
                {
                    if($nb_mois > 0)
                        $pourcent_mois = (($nb_mois * 100) / $nb_mois_dernier ) - 100;
                    else
                        $pourcent_mois = ( $nb_mois_dernier * 100 )  - 2 * ($nb_mois_dernier * 100);
                }
                else
                {
                    if($nb_mois > 0)
                        $pourcent_mois = $nb_mois * 100;
                    else
                        $pourcent_mois = 0;
                            
                }
                if($pourcent_mois > 0)
                {
                    $signe_m = '+';
                    $class_m = 'class=green';
                }
                else
                {
                    if($pourcent_mois == 0)
                    {
                        $signe_m = '';
                        $class_m = '';
                    }
                    else
                    {
                        $class_m = 'class=red';
                    }
                }
                
                $pourcent_mois = round($pourcent_mois);
                
                
                
                $annee_deb = mktime(0, 0, 0, 1, 1, date("Y"));
                $annee_fin = time();
                
                $annee_derniere = date('Y') - 1;
                $annee_derniere_debut = mktime(0, 0, 0, 1, 1, $annee_derniere);
                $annee_derniere_fin = $annee_deb;
                

                $db->Query('SELECT COUNT(*) AS nb_annee FROM interventions WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND time_cloture BETWEEN "'.$annee_deb.'" AND "'.$annee_fin.'" ');
                $row = $db->Row();
                $nb_annee = $row->nb_annee;
                
                $db->Query('SELECT COUNT(*) AS nb_annee_derniere FROM interventions WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND time_cloture BETWEEN "'.$annee_derniere_debut.'" AND "'.$annee_derniere_fin.'" ');
                $row = $db->Row();
                $nb_annee_derniere_aff = $row->nb_annee_derniere; 
                $num_jour_annee = date('z');
                $nb_annee_derniere = round($nb_annee_derniere_aff / 365 * $num_jour_annee);
                
                
                
                if($nb_annee_derniere > 0)
                {
                    if($nb_annee > 0)
                        $pourcent_annee = (($nb_annee * 100) / $nb_annee_derniere ) - 100;
                    else
                        $pourcent_annee = ( $nb_annee_derniere * 100 )  - 2 * ($nb_annee_derniere * 100);
                }
                else
                {
                    if($nb_annee > 0)
                        $pourcent_annee = $nb_annee * 100;
                    else
                        $pourcent_annee = 0;
                            
                }
                if($pourcent_annee > 0)
                {
                    $signe_a = '+';
                    $class_a = 'class=green';
                }
                else
                {
                    if($pourcent_annee == 0)
                    {
                        $signe_a = '';
                        $class_a = '';
                    }
                    else
                    {
                        $class_a = 'class=red';
                    }
                }
                
                $pourcent_annee = round($pourcent_annee);
                
                
                
                
                $db->Query('SELECT COUNT(*) AS nb_total FROM interventions WHERE compte_principal = "'.$_SESSION['compte_principal'].'" AND time_cloture > 0');
                $row = $db->Row();
                $nb_total = $row->nb_total;
                
            }
            /*
             * Fin statistiques
             * 
             */
            
            
            
            
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/pages/dashboard_serrurerie.php');
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