<?php session_start();

 //GESTION DES POSTS


//modifier commande
if(!empty($_POST['bdc']) AND (right('commandes', 3)))
{
    global $db;
    $id_inter = $db->SQLfix($_POST['id_inter']);
    $bdc = $db->SQLfix($_POST['bdc']);
    $fournisseur = $db->SQLfix($_POST['fournisseur']);
    $num_cde = $db->SQLfix($_POST['num_cde']);
    $colis = $db->SQLfix($_POST['colis']);
    $date_cde = $db->SQLfix($_POST['date_cde']);
    $date_reception = $db->SQLfix($_POST['date_reception']);

    $tab_d1 = explode('/', $date_cde);
    $date_c = mktime(0, 0, 0, $tab_d1[1], $tab_d1[0], $tab_d1[2]);
    
    $tab_d2 = explode('/', $date_reception);
    $date_r = mktime(0, 0, 0, $tab_d2[1], $tab_d2[0], $tab_d2[2]);
    
    $id_cde = $db->SQLfix($_POST['id_cde']); 
            
    $sql = 'UPDATE commandes SET fournisseur="'.$fournisseur.'", bdc="'.$bdc.'", num_cde="'.$num_cde.'", colis="'.$colis.'", date_cde="'.$date_c.'", date_reception="'.$date_r.'" WHERE id_inter="'.$id_inter.'" AND id="'.$id_cde.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ';


    $log = New addLog('a modifié une commande');
    $log->setInter($id_inter);
    $log->insert();
    
    
    $db->Query($sql);

    $_SESSION['notify'] = 'UpdateOrder';
    header('Location: ./intervention-commandes');
    die();
}

?>