<?php session_start();

    if(right('bl', 3))
    {
        global $db;
        $db->Query('SELECT clients.id, clients.titre, clients.nom, clients.prenom
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
                                )');
        $tab_clients = Array();
        $i = 0;
        while ($row = $db->Row())
        {
            $tab_clients[$i]['id'] = $row->id;
            $tab_clients[$i]['tire'] = $row->titre;
            $tab_clients[$i]['nom'] = $row->nom;
            $tab_clients[$i]['prenom'] = $row->prenom;
            $i++;
        }
        
        
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/new_bl.php'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/sub/new_bl.php');
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