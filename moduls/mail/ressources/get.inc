<?php session_start();
//GESTION DES GETS

if(isset($_GET['id_suppr']) AND is_numeric($_GET['id_suppr']))
{
    global $db;

    $id_mail = $db->SQLFix($_GET['id_suppr']);

    $sql = 'DELETE FROM mails_types WHERE id="'.$id_mail.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ';

    $db->Query($sql);

    $_SESSION['notify'] = 'DeleteMailType';
    header('location: ./mail');
    die();

}
?>