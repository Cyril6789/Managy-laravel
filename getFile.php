<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 03/03/2017
 * Time: 08:21
 */

/*******************************************************
 *  Déclaration de la fonction
 *******************************************************/

/**
 *  La fonction force le téléchargement d'un fichier
 *
 * @author : Hugo HAMON
 * @param : string $nom nom du fichier
 * @param : string $situtation emplacement sur le serveur web
 * @param : integer $poids poids du fichier en octets
 * @return : void
 **/
function forcerTelechargement($nom, $situation, $poids)
{
    header('Content-Type: application/octet-stream');
    header('Content-Length: '. $poids);
    header('Content-disposition: attachment; filename='. $nom);
    header('Pragma: no-cache');
    header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    readfile($situation);
    exit();
}


$dir = './../files_managy/'.$_GET['c'].'/i/'.$_GET['i'].'/public/'.$_GET['f'];

if(is_file($dir))
{
    forcerTelechargement($_GET['f'], $dir, $_GET['p']);
}
else
    header('location: ./'.$_GET['b']);

?>