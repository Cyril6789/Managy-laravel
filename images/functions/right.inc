<?php
//Permet de verifier si l'utilisateur courant à les droit necessaires pour visualiser la page ou l'élément, ou effectuer une action
function right($modul, $num)
{
    if(in_array($modul.'|'.$num, $_SESSION['right']))
        return true;
    else
        return FALSE;
}
?>