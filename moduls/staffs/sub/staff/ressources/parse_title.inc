<?php session_start();


if($_GET['id_staff'] AND !empty($_GET['id_staff']) AND is_numeric($_GET['id_staff'])) //Un emply séléctionné
{
    global $db;
    $id_staff = $db->SQLfix($_GET['id_staff']);
    
    $db->QUERY('SELECT COUNT(*) AS nb FROM staffs WHERE id ="'.$id_staff.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
    $row = $db->Row();

    if($row->nb) //employé existant
    {
        $db->Query('SELECT prenom, nom FROM staffs WHERE id="'.$id_staff.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
        $row = $db->Row();
        $page_title = str_replace('%lastname%', $row->prenom.' '.$row->nom , $page_title);

    }
    else
        $page_title = str_replace('%lastname%', 'inexistant', $page_title);
}
else
{
    $page_title = str_replace('%lastname%', 'non selectionné', $page_title);
}
            
  
?>