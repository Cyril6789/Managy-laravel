<?php session_start();
 //GESTION DES POSTS

 if(isset($_POST['text']) AND isset($_POST['alias']) AND isset($_POST['type']))
 {
     global  $db;
     $alias = $db->SQLfix($_POST['alias']);
     $texte = $db->SQLfix($_POST['text']);
     $type = $db->SQLfix($_POST['type']);
     $timeout = $db->SQLfix($_POST['timeout']);
     $id = $db->SQLfix($_POST['notify_id']);
     
     if(is_numeric($id) AND $id > 0)
     {
         $db->Query('UPDATE notifications SET alias="'.$alias.'", text="'.$text.'", $type="'.$type.'", timeout="'.$timeout.'" WHERE id="'.$id.'"  ');
         $_SESSION['notify'] = 'UpdatedNotify';
         
     }
     else
     {
         $db->Query('INSERT INTO notifications (alias, text, type, timeout) VALUES ("'.$alias.'", "'.$texte.'", "'.$type.'", "'.$timeout.'") ');
         $_SESSION['notify'] = 'InsertedNotify';
     }

     header('location: ./notifys');
     die();
 }
?>