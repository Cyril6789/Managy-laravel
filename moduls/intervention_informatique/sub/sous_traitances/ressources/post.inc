<?php session_start();

 //GESTION DES POSTS


//Ajouter/modifier sous_traitance
if(!empty($_POST['nom']) AND (right('soustraitances', 3)))
{
    global $db;
    $id_inter = $db->SQLfix($_POST['id_inter']);
    $devis = $db->SQLfix($_POST['devis']);
    $nom = $db->SQLfix($_POST['nom']);
    $num_sst = $db->SQLfix($_POST['num_sst']);
    $colis_alle = $db->SQLfix($_POST['colis_alle']);
    $colis_retour = $db->SQLfix($_POST['colis_retour']);
    $date_envoie = $db->SQLfix($_POST['date_sst']);
    $date_retour = $db->SQLfix($_POST['date_retour']);

    $tab_d1 = explode('/', $date_envoie);
    $date_e = mktime(0, 0, 0, $tab_d1[1], $tab_d1[0], $tab_d1[2]);
    
    $tab_d2 = explode('/', $date_retour);
    $date_r = mktime(0, 0, 0, $tab_d2[1], $tab_d2[0], $tab_d2[2]);
    
    $id_sst = $db->SQLfix($_POST['id_sst']); 
    $sql = 'UPDATE sous_traitances SET  devis="'.$devis.'", nom="'.$nom.'", num_cde="'.$num_sst.'", colis_alle="'.$colis_alle.'", colis_retour="'.$colis_retour.'", date_sst="'.$date_e.'", date_retour="'.$date_r.'" WHERE id_inter="'.$id_inter.'" AND id="'.$id_sst.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ';
        
    
    $db->Query($sql);

    $log = New addLog('a modifié une sous-traitance');
    $log->setInter($id_inter);
    $log->insert();


    $db->Query($sql);

    $_SESSION['notify'] = 'UpdateSousTraitance';
    header('Location: ./intervention-sous_traitances');
    die();
    
    
}

?>