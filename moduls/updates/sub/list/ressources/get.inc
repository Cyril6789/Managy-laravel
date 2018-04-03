<?php session_start();
//GESTION DES GETS
if(isset($_GET['id_suppr']) AND !empty($_GET['id_suppr']))
{
    if(is_numeric($_GET['id_suppr']))
    {
        if(right('updates', -1))
        {
            global $db;
            $id = $db->SQLfix($_GET['id_suppr']);
            
           $db->Query('DELETE FROM maj WHERE id = "'.$id.'" ');     
            
        }
    }
}
?>