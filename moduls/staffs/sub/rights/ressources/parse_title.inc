<?php session_start();
if(isset($_GET['id_staff']) AND !empty($_GET['id_staff']) AND is_numeric($_GET['id_staff']))
{
    
    global $db;
    
    $id_staff = $db->SQLfix($_GET['id_staff']);
    $db->Query('SELECT COUNT(*) AS nb FROM staffs WHERE id = "'.$id_staff.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
    $row = $db->Row();
    if($row->nb)
    {
        
        $db->Query('SELECT prenom FROM staffs WHERE id = "'.$id_staff.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
        $row = $db->Row();
        
        $page_title = str_replace('%name%', $row->prenom, $page_title);
    }
}
?>