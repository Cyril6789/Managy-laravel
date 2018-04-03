<?php session_start();


if($_GET['id_customer'] AND !empty($_GET['id_customer']) AND is_numeric($_GET['id_customer'])) //Un emply séléctionné
{
    global $db;
    $id_customer = $db->SQLfix($_GET['id_customer']);

    $db->QUERY('SELECT COUNT(*) AS nb FROM clients WHERE id ="'.$id_customer.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
    $row = $db->Row();

    if($row->nb) //employé existant
    {
        $db->Query('SELECT nom, prenom, pro_part FROM clients WHERE id="'.$id_customer.'"');
        $row = $db->Row();
        if($row->pro_part == 1)
        {
            $page_title = str_replace('%name%', 'de la société '.$row->nom, $page_title);
        }
        else
        {
            $page_title = str_replace('%name%', 'du client '.$row->prenom . ' ' . $row->nom, $page_title);
        }
    }
    else
        $page_title = str_replace('%name%', 'd\'un client inexistant', $page_title);
}
else
{
    $page_title = str_replace('%name%', 'd\'un client non selectionné', $page_title);
}

  
?>