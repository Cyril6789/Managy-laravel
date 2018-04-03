<?php session_start();
 //GESTION DES POSTS
 if(isset($_POST['mail_nom']))
 {
     global  $db;
     $nom = $db->SQLfix($_POST['mail_nom']);
     $sujet = $db->SQLfix($_POST['mail_sujet']);
     $titre = $db->SQLfix($_POST['mail_titre']);
     $message = $db->SQLfix($_POST['mail_message']);
     $id = $db->SQLfix($_POST['mail_id']);
     
     if(is_numeric($id) AND $id > 0)
     {
         $db->Query('UPDATE mails_types SET nom="'.$nom.'", titre="'.$titre.'", sujet="'.$sujet.'", message="'.$message.'" WHERE id="'.$id.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');

         $_SESSION['notify'] = 'UpdateMailType';
         header('Location: ./mail');
         die();
         
     }
     else
     {
         $db->Query('INSERT INTO mails_types (nom, titre, sujet, message, compte_principal) VALUES ("'.$nom.'", "'.$titre.'", "'.$sujet.'", "'.$message.'", "'.$_SESSION['compte_principal'].'" ) ');
         $_SESSION['notify'] = 'AddMailType';
         header('Location: ./mail');
         die();
     }
 }
?>