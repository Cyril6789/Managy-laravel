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
               


                if(is_file('./templates/'.TEMPLATE_NAME.'/pages/intervention_serrurerie.php'))
                {

                    $db->Query('SELECT inter.id_inter, inter.time_cloture, inter.time_ouverture,  inter.projet,  clients.id AS id_c, clients.pro_part, clients.titre, clients.nom, clients.prenom, clients.adresse, clients.adresse_suite, clients.cp, clients.ville, clients.fixe, clients.portable, clients.mail
                                FROM interventions AS inter 
                                INNER JOIN clients
                                ON (inter.id_client = clients.id)
                                WHERE inter.id_inter="'.$id_inter.'"
                                AND inter.compte_principal="'.$_SESSION['compte_principal'].'"
                                AND clients.compte_principal="'.$_SESSION['compte_principal'].'" ');
                    
                    echo $db->Error();
                    $row = $db->Row();

                    $nom_chantier = $row->projet;
                    $time_ouverture = $row->time_ouverture;
                    $time_cloture = $row->time_cloture;
                    if($time_cloture)
                        $cloture = 1;
                    else
                        $cloture = 0;
                    
                     $page_title .= ' - '.$row->projet .' - Ouvert '.parse_date($time_ouverture);

                    
                    
                    
                    $id_c = $row->id_c;
                    $titre = $row->titre;
                    $name = $row->nom;
                    $lname = $row->prenom;




                    //Relevés des horaires de la personnes connectées
                    $sql = 'SELECT description, heures, minutes, date, type_atelier_site FROM releves_horaires
                                          WHERE compte_principal = "'.$_SESSION['compte_principal'].'"
                                        AND id_staff = "'.$_SESSION['id'].'"
                                        AND id_inter = "'.$id_inter.'"
                                        ORDER BY date DESC
                                          ';

                    $db->Query($sql);
                    $i=0;
                    $j=0;
                    $tab_atelier = Array();
                    $tab_site = Array();
                    while($row = $db->Row())
                    {
                        if($row->type_atelier_site == 1)
                        {
                            $tab_atelier[$i]['descr'] = $row->description;
                            $tab_atelier[$i]['heures'] = $row->heures;
                            $tab_atelier[$i]['minutes'] = $row->minutes;
                            $tab_atelier[$i]['date'] = $row->date;
                            $i++;
                        }
                        else
                        {
                            $tab_site[$j]['descr'] = $row->description;
                            $tab_site[$j]['heures'] = $row->heures;
                            $tab_site[$j]['minutes'] = $row->minutes;
                            $tab_site[$j]['date'] = $row->date;
                            $j++;
                        }
                    }



                    //Statistiques
                    if(right("inter", 5))
                    {
                        $db->Query('SELECT id_staff AS id_s,
                                    (SELECT sum(heures) FROM releves_horaires WHERE id_staff = id_s AND id_inter = "'.$id_inter.'" AND type_atelier_site = "1") AS heures_atelier,
                                    (SELECT sum(minutes)FROM releves_horaires WHERE id_staff = id_s AND id_inter = "'.$id_inter.'" AND type_atelier_site = "1") AS minutes_atelier,
                                    (SELECT sum(heures) FROM releves_horaires WHERE id_staff = id_s AND id_inter = "'.$id_inter.'" AND type_atelier_site = "2") AS heures_site,
                                    (SELECT sum(minutes)FROM releves_horaires WHERE id_staff = id_s AND id_inter = "'.$id_inter.'" AND type_atelier_site = "2") AS minutes_site
                                    FROM releves_horaires
                                    WHERE id_inter = "'.$id_inter.'"
                                    AND compte_principal = "'.$_SESSION['compte_principal'].'"
                                    GROUP BY id_staff
                                    ');

                        $tab_staffs = Array();
                        $i = 0;
                        //echo $db->Error();
                        echo $db->GetHTML();
                        while($row = $db->Row())
                        {
                            $tab_staffs[$i]['id_staff'] = $row->id_s;

                            $h_a = floor($row->minutes_atelier / 60);
                            $m_a = $row->minutes_atelier - $h_a * 60;


                            $heures_a = $row->heures_atelier + $h_a;

                            $tab_staffs[$i]['heures_atelier'] = $heures_a;
                            $tab_staffs[$i]['minutes_atelier'] = $m_a;

                            $h_s = floor($row->minutes_site / 60);
                            $m_s = $row->minutes_site - $h_s * 60;


                            $heures_s = $row->heures_site + $h_s;

                            $tab_staffs[$i]['heures_site'] = $heures_s;
                            $tab_staffs[$i]['minutes_site'] = $m_s;



                            $i++;
                        }

                        print_r($tab_staffs);
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
                    

                    
                    //Chat
                    $db->Query('SELECT * FROM chat_inter WHERE id_inter="'.$id_inter.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ORDER  BY id DESC');
                    $i=0;
                    $tab_chats = Array();
                    while($row = $db->Row())
                    {
                        $tab_chats[$i]['staff'] = $row->id_staff;
                        $tab_chats[$i]['message'] = $row->message;
                        $tab_chats[$i]['time'] = $row->timestamp;
                        $i++;
                    }
                    
                    
                    ob_start();
                    include('./templates/'.TEMPLATE_NAME.'/pages/intervention_serrurerie.php');
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