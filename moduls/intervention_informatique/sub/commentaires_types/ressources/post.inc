<?php session_start();
 //GESTION DES POSTS
 if(isset($_POST['commentaire_type_titre']))
 {
     global  $db;
     $titre = $db->SQLfix($_POST['commentaire_type_titre']);
     $texte = $db->SQLfix($_POST['commentaire_type_texte']);
     $id = $db->SQLfix($_POST['commentaire_type_id']);
     
     if(is_numeric($id) AND $id > 0)
     {
         $db->Query('UPDATE commentaires_types SET titre="'.$titre.'", texte="'.$texte.'" WHERE id="'.$id.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
         $_SESSION['notify'] = 'ModifyCommentaireType';
         
     }
     else
     {
         $db->Query('INSERT INTO commentaires_types (titre, texte, compte_principal) VALUES ("'.$titre.'", "'.$texte.'",  "'.$_SESSION['compte_principal'].'" ) ');
         $_SESSION['notify'] = 'AddCommentaireType';
     }

     header('location: ./intervention-commentaires_types');
     die();
 }
?>