<?php session_start();

    if(right('soustraitances', 6) AND (AccesActivableModul('intervention_informatique')))
    {
        global $db;
        $db->Query('SELECT st.id, st.id_inter, st.devis, st.num_cde, st.nom AS nom_st, colis_alle, colis_retour, date_sst, date_retour, clients.titre, clients.nom, clients.prenom
                    FROM sous_traitances AS st
                    INNER JOIN interventions
                    ON (interventions.id_inter = st.id_inter)
                    AND (interventions.compte_principal="'.$_SESSION['compte_principal'].'")
                    INNER JOIN clients
                    ON (clients.id = interventions.id_client)
                    AND (clients.compte_principal = "'.$_SESSION['compte_principal'].'")
                    WHERE retour="0" AND st.compte_principal="'.$_SESSION['compte_principal'].'" 
                    ORDER BY date_retour ASC');
        
        
        $tab_ssts = Array();
        $i=0;
        while($row = $db->Row())
        {
            $tab_ssts[$i]['id'] = $row->id;
            $tab_ssts[$i]['id_inter'] = $row->id_inter;
            $tab_ssts[$i]['devis'] = $row->devis;
            $tab_ssts[$i]['nom_st'] = $row->nom_st;
            $tab_ssts[$i]['num_cde'] = $row->num_cde;
            $tab_ssts[$i]['colis_alle'] = $row->colis_alle;
            $tab_ssts[$i]['colis_retour'] = $row->colis_retour;
            $tab_ssts[$i]['date_sst'] = date('d/m/Y', $row->date_sst);
            $tab_ssts[$i]['date_retour'] = date('d/m/Y', $row->date_retour);
            $tab_ssts[$i]['date_retour_t'] = $row->date_retour;
            $tab_ssts[$i]['titre'] = $row->titre;
            $tab_ssts[$i]['nom'] = $row->nom;
            $tab_ssts[$i]['prenom'] = $row->prenom;
            $i++;
        }
        
        
        
        
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/sous_traitances.php'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/sub/sous_traitances.php');
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