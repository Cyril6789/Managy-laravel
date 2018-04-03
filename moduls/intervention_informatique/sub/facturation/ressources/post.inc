<?php session_start();

if(isset($_POST['id_inter']) AND !empty($_POST['id_inter'])) 
{
    global $db;

    $id_inter = $db->SQLFix($_POST['id_inter']);


    $facture = 1;

    if($_POST['paye'] == 'oui')
        $paye = 1;
    else
        $paye = 0;

    $sql = 'UPDATE interventions SET facture = "'.$facture.'", paye="'.$paye.'", id_staff_restitution="'.$_SESSION['id'].'" WHERE id_inter="'.$id_inter.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ';

    $db->Query($sql);


    $log = New addLog('a marqué l\'intervention comme facturée');
    $log->setInter($id_inter);
    $log->insert();

    header('location: ./intervention-facturation');

    die();
}
?>