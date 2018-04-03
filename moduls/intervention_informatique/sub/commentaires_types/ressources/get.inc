<?php session_start();
//GESTION DES GETS

if(isset($_GET['id_suppr']) AND is_numeric($_GET['id_suppr']))
{
    global $db;

    $id = $db->SQLFix($_GET['id_suppr']);

    $sql = 'DELETE FROM commentaires_types WHERE id="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ';

    $db->Query($sql);

    $_SESSION['notify'] = 'DeleteCommentaireType';
    header('location: ./intervention-commentaires_types');
    die();

}
?>