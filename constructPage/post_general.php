<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 13/09/2016
 * Time: 14:17
 */

if(AccesActivableModul('tasks'))
    if(is_file('./moduls/tasks/ressources/post_general.php'))
        include('./moduls/tasks/ressources/post_general.php');


if($_GET['new_cgu'] == 'ok' AND $_SESSION['gerant'])
{
    $sql = 'SELECT id FROM cgu ORDER BY id DESC LIMIT 1';
    $db->Query($sql);

    $row = $db->Row();
    $id_cgu = $row->id;

    $sql = 'UPDATE comptes_principaux SET id_cgu="'.$id_cgu.'" WHERE id="'.$_SESSION['compte_principal'].'" ';
    $db->Query($sql);

    header('location: ./dashboard');
}


if(AccesActivableModul('sellsy'))
    if(is_file('./moduls/sellsy/ressources/post.inc'))
        include ('./moduls/sellsy/ressources/post.inc');