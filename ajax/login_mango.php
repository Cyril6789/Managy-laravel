<?php session_start();
require('../classes/mysql.class.php');
require('../moduls/logs/classes/addLog.class.php');

$db = new MySQL();

$pseudo = $db->SQLFix($_POST['login_name']);
$pass = sha1($db->SQLFix($_POST['login_pw']));



$db->Query("SELECT staffs.id, prenom, nom, mail, compte_principal, status_chat, gerant
            FROM staffs
            INNER JOIN comptes_principaux
            ON (comptes_principaux.id = staffs.compte_principal)
            WHERE pseudo='".$pseudo."'
            AND pass='".$pass."'
            AND comptes_principaux.bloque = '0'");
echo $db->Error();
$row = $db->Row();
    $_SESSION['id'] = $row->id;
    $_SESSION['prenom'] = $row->prenom;
    $_SESSION['nom'] = $row->nom;
    $_SESSION['mail'] = $row->mail;
    $_SESSION['compte_principal'] = $row->compte_principal;
    $_SESSION['status'] = $row->status_chat;
    $_SESSION['gerant'] = $row->gerant;


if($db->RowCount()) {
    $db->Query('UPDATE staffs SET session_id = "'.session_id().'" WHERE id="'.$row->id.' "');
    if ($_SESSION['id'] > 1)
    {
        $texte = $_SESSION['prenom'] . ' vient de se connecter sur managy.fr';
        mail('contact@depaninfo67.com', 'connection sur managy', $texte);
    }
    $log = new addLog('s\'est connecté sur Managy.fr (IP : '.$_SERVER["REMOTE_ADDR"].')');
    $log->insert();
    echo 'true';
}
else
    echo 'false';

$db->Close();
?>
