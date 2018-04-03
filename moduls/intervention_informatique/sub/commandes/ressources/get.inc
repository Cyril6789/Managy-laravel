<?php session_start();

//GESTION DES GETS

//Suppression commande
if(!empty($_GET['suppr_cde']) AND is_numeric($_GET['suppr_cde']) AND right('commandes', 3))
{

    global $db;
    $id_cde = $db->SQLfix($_GET['suppr_cde']);
    $id_inter = $db->SQLfix($_GET['id_inter']);
    
    $db->Query('DELETE FROM commandes WHERE id="'.$id_cde.'" AND id_inter="'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');

    $log = New addLog('a supprimé une commande');
    $log->setInter($id_inter);
    $log->insert();

    $_SESSION['notify'] = 'DeleteOrder';
    header('Location: ./intervention-commandes');
    die();
}


//Reception commande
if(!empty($_GET['reception_cde']) AND is_numeric($_GET['reception_cde']) AND right('commandes', 5))
{
    global $db;
    $id_cde = $db->SQLfix($_GET['reception_cde']);
    $id_inter = $db->SQLfix($_GET['id_inter']);
    
    $db->Query('UPDATE commandes SET cde_recue="1", date_reception="'.time().'" WHERE id="'.$id_cde.'" AND id_inter="'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');

    $log = New addLog('a marqué une commande comme réceptionnée');
    $log->setInter($id_inter);
    $log->insert();

    $_SESSION['notify'] = 'ReceiveOrder';
    header('Location: ./intervention-commandes');
    die();
}

?>