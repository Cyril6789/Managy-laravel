<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 23/03/2016
 * Time: 21:12
 */
 if(empty($_SESSION['id']))
     die();
     
 if(empty($_GET['pass']))
     die();
 
 
 

require('../classes/mysql.class.php');
$db = new MySQL();


require('../constructPage/settings.gen.inc');
require('../functions/right.func.inc');
require('../functions/crypt_decrypt.func.inc');

if(right('customers', 3))
{
    $tab = explode('_', $_GET['id']);
    $type = $db->SQLFix($tab[0]);
    $id = $db->SQLFix($tab[1]);
    $user_pass = $db->SQLFix($_GET['pass']);

    $table = 'mdp';

    if($type == 'note' OR $type = 'editnote')
    {
        $table = 'notes';
        $selecteur = 'texte';
    }
    if($type == 'mdp')
        $selecteur = 'pass';
    if($type == 'identifiant')
        $selecteur = 'identifiant';

    $sql = 'SELECT '.$table.'.'.$selecteur.' AS val
            FROM '.$table.'
            INNER JOIN comptes_principaux AS cp
            ON (cp.id = '.$table.'.compte_principal)
            INNER JOIN staffs AS s
            ON (s.compte_principal = cp.id)
            WHERE s.pass = "'.sha1($user_pass).'"
            AND s.id = "'.$_SESSION['id'].'"
            AND '.$table.'.id = "'.$id.'"
            ';

    $db->Query($sql);
    $r = $db->Row();
    if($type == 'editnote')
        echo str_replace('\r\n', "\r\n", Base64_Encryption::Decrypter($r->val, CRYPT_KEY));
    else
        echo str_replace('\r\n', "<br />", Base64_Encryption::Decrypter($r->val, CRYPT_KEY));

}
else
    echo 'Vous n\'avez pas le droit de déverouillez un mot de passe';



?>