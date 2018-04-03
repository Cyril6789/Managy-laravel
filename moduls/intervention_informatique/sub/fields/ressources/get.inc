<?php
//GESTION DES GETS


if(isset($_GET['suppr_field']) AND is_numeric($_GET['suppr_field']))
{

    $id_suppr = $db->SQLFix($_GET['suppr_field']);

    $sql1 = 'DELETE FROM champs_personnalises WHERE id = "'.$id_suppr.'" AND compte_principal ="'.$_SESSION['compte_principal'].'"';
    $sql2 = 'DELETE FROM valeurs_champs_personnalises WHERE id_champ_personnalise = "'.$id_suppr.'" AND compte_principal ="'.$_SESSION['compte_principal'].'"';
    $db->Query($sql1);
    $db->Query($sql2);
    $_SESSION['notify'] = 'deleteField';
    header('location: ./intervention-fields');
    die();
}
?>