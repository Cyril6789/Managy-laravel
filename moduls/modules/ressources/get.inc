<?php session_start();
//GESTION DES GETS
if(isset($_GET['activer']))
{
    if(right('modules', 1))
    {
        global $db;
        $nom_module = $db->SQLfix($_GET['activer']);
        $db->Query('SELECT COUNT(*) AS nb FROM modules_acces WHERE nom ="'.$nom_module.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ');
        $row = $db->Row();
        if(!$row->nb)
        {
            $db->Query('INSERT INTO modules_acces (nom, date_activation, acces, compte_principal) VALUES ("'.$nom_module.'", "'.time().'", "1", "'.$_SESSION['compte_principal'].'") ');
        
            $mail = new Maileur('Activation de module !');
            $mail->AddTitle('Module '.$nom_module.' activé');
            $mail->addDest('contact@depaninfo67.com');
            $message = 'Salut Cyril, <br />'.$_SESSION['prenom'].' '.$_SESSION['nom'].' vient d\'activer le module '.$nom_module.' sur Managy !<br />';
            $mail->body($message);
            $mail->setLogo('managy');
            $mail->send();
        
        }
        else
        {
            $db->Query('UPDATE modules_acces SET acces="1" WHERE nom="'.$nom_module.'" and compte_principal = "'.$_SESSION['compte_principal'].'" ');
        }
        
        header('Location: ./modules');
    }
}

if(isset($_GET['desactiver']))
{
    if(right('modules', 1))
    {
        global $db;
        $nom_module = $db->SQLfix($_GET['desactiver']);
        $db->Query('UPDATE modules_acces SET acces="0" WHERE nom="'.$nom_module.'" and compte_principal = "'.$_SESSION['compte_principal'].'" ');
        
        $mail = new Maileur('Désactivation de module !');
        $mail->AddTitle('Module '.$nom_module.' désactivé');
        $mail->addDest('contact@depaninfo67.com');
        $message = 'Salut Cyril, <br />'.$_SESSION['prenom'].' '.$_SESSION['nom'].' vient de désactiver le module '.$nom_module.' sur Managy !<br />';
        $mail->body($message);
        $mail->setLogo('managy');
        $mail->send();
        
        header('Location: ./modules');
    }
}
?>