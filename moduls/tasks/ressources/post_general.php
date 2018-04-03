<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 13/09/2016
 * Time: 14:20
 */

if(isset($_POST['task_id']) AND !empty($_POST['task_id']))
{
    $titre = $db->SQLFix($_POST['titre']);
    $statut = $db->SQLFix($_POST['statut']);
    $pourcentage = $db->SQLFix($_POST['pourcentage']);
    $staff_assignation = $db->SQLFix($_POST['staff']);
    $commentaire = $db->SQLFix($_POST['commentaire']);

    $id = $db->SQLFix($_POST['task_id']);

    if(is_numeric($id)) // Edition
        $sql = 'UPDATE taches SET titre="'.$titre.'", statut = "'.$statut.'", id_staff_assignation="'.$staff_assignation.'", pourcentage="'.$pourcentage.'", commentaire="'.$commentaire.'" WHERE id="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ';
    else
        $sql = 'INSERT INTO taches (titre, statut, id_staff_ouverture, id_staff_assignation, pourcentage, commentaire, time_ouverture, compte_principal) VALUES ("'.$titre.'", "'.$statut.'", "'.$_SESSION['id'].'", "'.$staff_assignation.'", "'.$pourcentage.'", "'.$commentaire.'", "'.time().'", "'.$_SESSION['compte_principal'].'") ';

    
    $db->Query($sql);

    header('location: '.$_SERVER['HTTP_REFERER']);



    die();
}