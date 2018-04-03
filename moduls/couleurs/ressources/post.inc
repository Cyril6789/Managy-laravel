<?php session_start();
if(isset($_POST['submit_color']) AND !empty($_POST['submit_color']) AND !empty($_POST['color_name']))
{
    global $db;
    $nom = $db->SQLfix($_POST['color_name']);
    $ral = $db->SQLfix($_POST['color_ral']);
    $db->Query('INSERT INTO couleurs (nom, ral, compte_principal) VALUES ("'.$nom.'", "'.$ral.'", "'.$_SESSION['compte_principal'].'") ');
    $_SESSION['div_color_add_succes'] = true;
}

if(isset($_POST['submit_aspect']) AND !empty($_POST['submit_aspect']) AND !empty($_POST['aspect_name']))
{
    global $db;
    $nom = $db->SQLfix($_POST['aspect_name']);
    $db->Query('INSERT INTO aspects (nom, compte_principal) VALUES ("'.$nom.'", "'.$_SESSION['compte_principal'].'") ');
    $_SESSION['div_aspect_add_succes'] = true;
}
?>