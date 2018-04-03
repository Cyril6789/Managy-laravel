<?php session_start();

if(isset($_GET['rdv_suppr']) AND is_numeric($_GET['rdv_suppr']))
{
    global $db;
    $id_rdv = $db->SQLfix($_GET['rdv_suppr']);
    
    
    $db->Query('DELETE FROM rdv WHERE id="'.$id_rdv.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
    header('Location:rdv');
    
    
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>