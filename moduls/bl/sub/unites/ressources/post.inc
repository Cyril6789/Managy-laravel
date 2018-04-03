<?php session_start();
 //GESTION DES POSTS
if(isset($_POST['submit_unite']) AND !empty($_POST['submit_unite']) AND !(empty($_POST['unite_name'])))
{
    global $db;
    $nom = $db->SQLfix($_POST['unite_name']);
    $db->Query('INSERT INTO unites_bls (nom, compte_principal) VALUES ("'.$nom.'", "'.$_SESSION['compte_principal'].'" ) ');
    $_SESSION['div_unite_add_succes'] = true;
}
?>