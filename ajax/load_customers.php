<?php session_start();

require('../classes/mysql.class.php');

$db = new MySQL();
require_once('../constructPage/settings.gen.inc');



$word = $db->SQLFix($_GET['word']);
if(!empty($word))
{
    $sql = 'SELECT * FROM clients WHERE (nom LIKE "%'.$word.'%" OR prenom LIKE "%'.$word.'%" OR mail LIKE "%'.$word.'%" OR adresse LIKE "%'.$word.'%" OR cp LIKE "%'.$word.'%" OR ville LIKE "%'.$word.'%" OR portable LIKE "%'.$word.'%" OR fixe LIKE "%'.$word.'%")';
    if($_GET['type'] == 1)
        $sql .= ' AND pro_part=1 ';
    if($_GET['type'] == 2)
        $sql .= ' AND pro_part=2 AND id_parent = 0';
    if($_GET['no_parent'] == 1)
        $sql .= ' AND id_parent = 0';
    
    $sql .= ' AND compte_principal="'.$_SESSION['compte_principal'].'" AND archive="0" ';
    $db->Query($sql);



    if(is_file('../templates/'.TEMPLATE_NAME.'/ajax/load_customers.php'))
    {
        include('../templates/'.TEMPLATE_NAME.'/ajax/load_customers.php');
    }
     else {

    echo "erreur";

     }

    $db->Close();
}

?>