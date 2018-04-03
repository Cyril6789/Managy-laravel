<?php session_start();
 //GESTION DES POSTS

if(!empty($_POST['search']))
{
    if(is_numeric($_POST['search']))
    {
        if(AccesActivableModul('intervention_immo'))
            header('Location: ./affaire-'.$_POST['search']);
        else
            header('Location: ./intervention-'.$_POST['search']);
    }
}

?>