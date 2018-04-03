<?php session_start();
 //GESTION DES POSTS
if(isset($_POST['submit_etape']) AND !empty($_POST['submit_etape']) AND !(empty($_POST['etape_name'])))
{
    global $db;
    $nom = $db->SQLfix($_POST['etape_name']);
    if($_POST['y_n'] == 'y')
        $time = '1';
    else
        $time = '0';
    $db->Query('INSERT INTO etapes (nom, temps, compte_principal) VALUES ("'.$nom.'", "'.$time.'", "'.$_SESSION['compte_principal'].'" ) ');
    $_SESSION['div_etape_add_succes'] = true;
}
?>