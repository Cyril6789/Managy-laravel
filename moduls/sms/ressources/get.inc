<?php session_start();
//GESTION DES GETS

if(isset($_GET['id_suppr']) AND is_numeric($_GET['id_suppr']))
{
    global $db;

    $id_sms = $db->SQLFix($_GET['id_suppr']);

    $sql = 'DELETE FROM sms_types WHERE id="'.$id_sms.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ';

    $db->Query($sql);

    $_SESSION['notify'] = 'DeleteSmsType';
    header('location: ./sms');
    die();

}
?>