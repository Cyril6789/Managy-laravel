<?php session_start();

//GESTION DES GETS

//Suppression sous_traitance
if(!empty($_GET['suppr_sst']) AND is_numeric($_GET['suppr_sst']) AND right('soustraitances', 3))
{
    global $db;
    $id_sst = $db->SQLfix($_GET['suppr_sst']);
    $id_inter = $db->SQLfix($_GET['id_inter']);
    
    $db->Query('DELETE FROM sous_traitances WHERE id="'.$id_sst.'" AND id_inter="'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');


    $log = New addLog('a supprimé une sous-traitance');
    $log->setInter($id_inter);
    $log->insert();

    $_SESSION['notify'] = 'DeleteSousTraitance';
    header('Location: ./intervention-sous_traitances');
    die();
}


//Reception sous_traitance
if(!empty($_GET['retour_sst']) AND is_numeric($_GET['retour_sst']) AND right('soustraitances', 5))
{
    global $db;
    $id_sst = $db->SQLfix($_GET['retour_sst']);
    $id_inter = $db->SQLfix($_GET['id_inter']);
    
    $db->Query('UPDATE sous_traitances SET retour="1", date_retour="'.time().'" WHERE id="'.$id_sst.'" AND id_inter="'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');

    $log = New addLog('a marqué une sous-traitance comme réceptionnée');
    $log->setInter($id_inter);
    $log->insert();

    $_SESSION['notify'] = 'RetourSousTraitance';
    header('Location: ./intervention-sous_traitances');
    die();
}

?>