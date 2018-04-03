<?php session_start();

if(isset($_POST['id_inter']) AND !empty($_POST['id_inter'])) 
{
    global $db;

    $id_inter = $db->SQLFix($_POST['id_inter']);

    $t_d = explode('/', $_POST['date_restitution']);

    $date = mktime(23, 59, 59, $t_d[1], $t_d[0], $t_d[2]);

    if($_POST['facture'] == 'oui')
        $facture = 1;
    else
        $facture = 0;

    if($_POST['paye'] == 'oui')
        $paye = 1;
    else
        $paye = 0;
    $comment = $db->SQLFix($_POST['commentaire']);

    $sql = 'UPDATE interventions SET time_restitution = "'.$date.'", facture = "'.$facture.'", paye="'.$paye.'", id_staff_restitution="'.$_SESSION['id'].'", note="'.$comment.'" WHERE id_inter="'.$id_inter.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ';

    $db->Query($sql);

    $log = New addLog('a marqué l\'intervention comme restituée au client');
    $log->setInter($id_inter);
    $log->insert();

    changements_interventions($id_inter);

    header('location: ./intervention-restitution');

    die();
}
?>