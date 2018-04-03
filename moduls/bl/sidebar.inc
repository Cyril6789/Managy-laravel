<?php session_start();
    if(!is_array($sidebar['bl']))
        $sidebar['bl'] = Array();
    $sidebar['bl']['first'] = 'file|'.BL_SB_BL;
    
    $sidebar['bl'][] = 'bl|3|'.BL_SB_BLS.'|bl';
    
    
    global $db;
    $db->Query('SELECT COUNT(*) AS nb_bl
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
    $row = $db->Row();
    
    $sidebar['bl'][] = 'bl|3|'.BL_SB_CREATE.'|bl-new|'.$row->nb_bl;
    $sidebar['bl'][] = 'bl|2|'.BL_SB_MODIFY_UNITS.'|bl-unites';
?>
