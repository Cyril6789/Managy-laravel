<?php session_start();
 //GESTION DES POSTS
if(isset($_POST['color']) AND !empty($_POST['color']) AND is_numeric($_POST['color']) AND isset($_POST['aspect']) AND !empty($_POST['aspect']) AND is_numeric($_POST['aspect']))
{
    if(right("colors", 4))
    {
        global $db;

        $id_couleur = $db->SQLfix($_POST['color']);
        $id_aspect = $db->SQLfix($_POST['aspect']);
        $codebarre = $db->SQLfix($_POST['cb_'.$id_couleur.'_'.$id_aspect]);

        if(!empty($codebarre))
        {
            $db->Query('SELECT COUNT(*) AS nb FROM codebarres_couleurs WHERE id_couleur = "'.$id_couleur.'" AND id_aspect="'.$id_aspect.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            //echo 'SELECT COUNT(*) AS nb FROM codebarres_couleurs WHERE id_couleur = "'.$id_couleur.'" AND id_aspect="'.$id_aspect;
            if($row->nb) //Existant, maj
            {
                echo 'maj';
                $db->Query('UPDATE codebarres_couleurs SET code ="'.$codebarre.'" WHERE id_couleur = "'.$id_couleur.'" AND id_aspect="'.$id_aspect.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            }
            else //inexistant, add
            {
                echo 'insert';
                $db->Query('INSERT INTO codebarres_couleurs (id_couleur, id_aspect, code, compte_principal) VALUES("'.$id_couleur.'", "'.$id_aspect.'", "'.$codebarre.'", "'.$_SESSION['compte_principal'].'" ) ');
            }
        }
    }
}
?>