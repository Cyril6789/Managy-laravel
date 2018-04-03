<?php session_start();
 //GESTION DES POSTS

if(isset($_POST['cgu']))
{
    $db = new MySQL();
    $cgu = $db->SQLFix($_POST['cgu']);
    $version = $db->SQLFix($_POST['version']);

    $sql = 'INSERT INTO cgu (texte) VALUES ("'.$cgu.'")';
    $db->Query($sql);
    header('location: ./admin-cgu');
}

?>