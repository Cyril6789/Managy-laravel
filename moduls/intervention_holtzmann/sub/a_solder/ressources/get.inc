<?php session_start();
//GESTION DES GETS

if(isset($_GET['solder']) AND is_numeric($_GET['solder']))
{
    global $db;
    $id = $db->SQLFix($_GET['solder']);

    $db->Query('UPDATE interventions SET solde = "1"
                WHERE id_inter = "'.$id.'"
                AND compte_principal= "'.$_SESSION['compte_principal'].'" ');


    header('Location: ./intervention-a_solder');

}

?>