<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 18/11/2017
 * Time: 18:43
 */



if(isset($_POST['id_compte_prol_manu']) AND $_SESSION['compte_principal'] == '1')
{
    print_r($_POST);


    $id_compte = $db->SQLFix($_POST['id_compte_prol_manu']);
    $mois_s = $db->SQLFix($_POST['mois_s']);
    $mois_p = $db->SQLFix($_POST['mois_p']);
    $prix_b = $db->SQLFix($_POST['prix_b']);
    $prix_t = $db->SQLFix($_POST['prix_t']);
    $comm = $db->SQLFix($_POST['commentaire']);



    $compte = new DataObject('comptes_principaux');
    $compte->find($_POST['id_compte_prol_manu'], 'id', false);


    $new_fin_abo = addMonthToDate($compte->fin_abo, $_POST['mois_s']);


    $compte->fin_abo = $new_fin_abo;
    $compte->update();

    $sql = 'INSERT INTO prolongements (compte_principal, timestamp, fin_abo, mois_prolonge, mois_paye, prix_base, prix_total, transactionid) VALUES ("'.$id_compte.'", "'.time().'", "'.$new_fin_abo.'", "'.$mois_s.'", "'.$mois_p.'", "'.$prix_b.'", "'.$prix_t.'", "'.$comm.'")';

    $db->Query($sql);


    $sql = 'UPDATE licences_staffs SET date_fin = "'.$new_fin_abo.'" WHERE compte_principal = "'.$id_compte.'" AND incluse = "0" ';

    $db->Query($sql);

    $sql = 'UPDATE modules_acces SET fin_abo = "1", date_fin="'.$new_fin_abo.'" WHERE compte_principal = "'.$id_compte.'" AND acces = "1" ';
    $db->Query($sql);

    $sql = 'UPDATE modules_acces SET fin_abo = "0" WHERE compte_principal = "'.$id_compte.'" AND acces = "0" ';
    $db->Query($sql);

    header('location: ./dashboard');

}