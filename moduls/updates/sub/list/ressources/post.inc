<?php session_start();
 //GESTION DES POSTS
if(isset($_POST['send']))
{
    if(isset($_POST['edit']) AND !empty($_POST['edit'])) //màj
    {
        if(is_numeric($_POST['edit']))
        {
            global $db;
            $id_maj = $db->SQLfix($_POST['edit']);
            $titre = $db->SQLfix($_POST['titre']);
            $sous_titre = $db->SQLfix($_POST['sous_titre']);
            $texte = $db->SQLfix($_POST['texte']);
            
            $db->Query('UPDATE maj SET titre = "'.$titre.'", sous_titre ="'.$sous_titre.'", texte="'.$texte.'" WHERE id = "'.$id_maj.'" ');
        }
    }
    else // insert
    {
        if(!empty($_POST['titre']))
        {
            global $db;
            $titre = $db->SQLfix($_POST['titre']);
            $sous_titre = $db->SQLfix($_POST['sous_titre']);
            $texte = $db->SQLfix($_POST['texte']);

            $db->Query('INSERT INTO maj (titre, sous_titre, texte, timestamp) VALUES ("'.$titre.'", "'.$sous_titre.'", "'.$texte.'", "'.time().'" )');
            echo $db->Error();
        }
    }
}
?>