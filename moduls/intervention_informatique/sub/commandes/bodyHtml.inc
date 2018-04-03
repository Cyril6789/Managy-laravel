<?php session_start();

    if(right('commandes', 6))
    {
        global $db;
        $db->Query('SELECT commandes.id, commandes.id_inter, num_cde, bdc, fournisseur, colis, date_cde, date_reception, clients.titre, clients.nom, clients.prenom
                    FROM commandes
                    INNER JOIN interventions
                    ON (interventions.id_inter = commandes.id_inter)
                    AND (interventions.compte_principal="'.$_SESSION['compte_principal'].'")
                    INNER JOIN clients
                    ON (clients.id = interventions.id_client)
                    AND (clients.compte_principal = "'.$_SESSION['compte_principal'].'")
                    WHERE cde_recue="0" AND commandes.compte_principal="'.$_SESSION['compte_principal'].'" 
                    ORDER BY date_reception ASC');
        
        
        
        $tab_cdes = Array();
        $i=0;
        while($row = $db->Row())
        {
            $tab_cdes[$i]['id'] = $row->id;
            $tab_cdes[$i]['id_inter'] = $row->id_inter;
            $tab_cdes[$i]['bdc'] = $row->bdc;
            $tab_cdes[$i]['fournisseur'] = $row->fournisseur;
            $tab_cdes[$i]['num_cde'] = $row->num_cde;
            $tab_cdes[$i]['colis'] = $row->colis;
            $tab_cdes[$i]['date_cde'] = date('d/m/Y', $row->date_cde);
            $tab_cdes[$i]['date_reception'] = date('d/m/Y', $row->date_reception);
            $tab_cdes[$i]['date_reception_t'] = $row->date_reception;
            $tab_cdes[$i]['titre'] = $row->titre;
            $tab_cdes[$i]['nom'] = $row->nom;
            $tab_cdes[$i]['prenom'] = $row->prenom;
            $i++;
        }
        
        
        
        
        
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/commandes.php'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/sub/commandes.php');
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