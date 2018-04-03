<?php session_start();
 //GESTION DES POSTS
if(isset($_POST['submit_traitement']) AND !empty($_POST['submit_traitement']) AND !(empty($_POST['traitement_name'])))
{
    global $db;
    $nom = $db->SQLfix($_POST['traitement_name']);
    $db->Query('INSERT INTO traitements (nom, compte_principal) VALUES ("'.$nom.'", "'.$_SESSION['compte_principal'].'" ) ');
    $_SESSION['div_traitement_add_succes'] = true;
}
?>