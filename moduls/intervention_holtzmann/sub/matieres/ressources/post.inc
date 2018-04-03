<?php session_start();
 //GESTION DES POSTS
if(isset($_POST['submit_matiere']) AND !empty($_POST['submit_matiere']) AND !(empty($_POST['matiere_name'])))
{
    global $db;
    $nom = $db->SQLfix($_POST['matiere_name']);
    $db->Query('INSERT INTO matieres (nom, compte_principal) VALUES ("'.$nom.'", "'.$_SESSION['compte_principal'].'" ) ');
    $_SESSION['div_matiere_add_succes'] = true;
}
?>