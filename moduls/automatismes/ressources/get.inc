<?php session_start();
//GESTION DES GETS

if(isset($_GET['suppr']) AND !(empty($_GET['suppr'])))
{
    $id = $db->SQLFix($_GET['suppr']);

    $sql = 'DELETE FROM automatismes WHERE id = "'.$id.'" AND compte_principal = "'.$_SESSION['compte_principal'].'"';

    $db->Query($sql);


    header('location: ./automatismes');
    die();

}
?>