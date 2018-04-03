<?php
 //GESTION DES POSTS
if(isset($_POST['submit_sms']) AND !empty($_POST['submit_sms']) AND !(empty($_POST['sms_titre'])))
{
    global $db;
    $titre = $db->SQLfix($_POST['sms_titre']);
    $message = $db->SQLfix($_POST['sms_message']);
    $db->Query('INSERT INTO sms_types (titre, message, compte_principal) VALUES ("'.$titre.'", "'.$message.'", "'.$_SESSION['compte_principal'].'" ) ');

    $_SESSION['notify'] = 'addSms';
    header('Location: ./sms');
    die();


}
?>