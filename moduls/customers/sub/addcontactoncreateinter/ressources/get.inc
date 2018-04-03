<?php
//GESTION DES GETS

if($_GET['action'] == 'add' AND isset($_GET['id_customer']) AND !empty($_GET['id_customer']) AND is_numeric($_GET['id_customer']) AND isset($_GET['id_contact']) AND !empty($_GET['id_contact']) AND is_numeric($_GET['id_contact']))
{

    global $db;
    $id_customer = $db->SQLfix($_GET['id_customer']);
    $id_contact = $db->SQLfix($_GET['id_contact']);
    $db->Query('SELECT COUNT(*) AS nb_pro FROM clients WHERE id = "'.$id_customer.'" AND pro_part = 1 AND compte_principal="'.$_SESSION['compte_principal'].'" ');
    $row = $db->Row();
    $nb_pro = $row->nb_pro;

    $db->Query('SELECT COUNT(*) AS nb_part FROM clients WHERE id = "'.$id_contact.'" AND pro_part = 2 AND id_parent = 0 AND compte_principal="'.$_SESSION['compte_principal'].'" ');
    $row = $db->Row();
    $nb_part = $row->nb_part;

    if($nb_pro AND $nb_part)
        $db->Query('UPDATE clients SET id_parent = "'.$id_customer.'" WHERE id="'.$id_contact.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');

    $log = New addLog('a ajouté un particulié à la société');
    $log->setClient($id_customer);
    $log->insert();

    header('Location: intervention-new-customer-'.$id_customer.'?ref='.$id_contact);
}


?>