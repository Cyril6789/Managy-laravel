<?php session_start();
 //GESTION DES POSTS
if(isset($_POST['send']) AND !(empty($_POST['send'])))
{
    global $db;
    foreach ($_POST AS $key => $post)
    {
       $tab = explode('_', $key);
       if($tab[0] == 'stock')
       {
           if(is_numeric($tab[1]) AND is_numeric($tab[2]))
           {
               $stock = $db->SQLfix($_POST[$key]);
               $id_couleur = $db->SQLfix($tab[1]);
               $id_aspect = $db->SQLfix($tab[2]);
               
               $db->Query('SELECT COUNT(*) AS nb FROM stocks_couleurs WHERE id_couleur ="'.$id_couleur.'" AND id_aspect = "'.$id_aspect.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
               
               $row = $db->Row();
               if($row->nb) //maj
               {
                   $db->Query('UPDATE stocks_couleurs SET stock = stock +"'.$stock.'" WHERE id_couleur ="'.$id_couleur.'" AND id_aspect = "'.$id_aspect.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
               }
               else //insert
               {
                   $db->Query('INSERT INTO stocks_couleurs (stock, id_couleur, id_aspect, compte_principal) VALUES ("'.$stock.'", "'.$id_couleur.'", "'.$id_aspect.'", "'.$_SESSION['compte_principal'].'" ) ');
               }
               
           }
       }
    }
}
?>