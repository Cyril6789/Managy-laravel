<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 02/08/2016
 * Time: 23:52
 */
require('../classes/mysql.class.php');
require('../classes/Maileur.class.php');
require('../classes/notificationMail.class.php');
require('../moduls/logs/classes/addLog.class.php');
require('../functions/crypt_pass.func.php');


$db = new MySQL();
require('../constructPage/settings.gen.inc');
$pseudo = $db->SQLFix($_GET['username']);
$pass = crypt_pass($db->SQLFix($_GET['password']));


$sql = "SELECT staffs.id, prenom, staffs.nom, staffs.portable, mail, staffs.compte_principal, status_chat, gerant, nom_societe, two_steps_auth
            FROM staffs
            INNER JOIN comptes_principaux
            ON (comptes_principaux.id = staffs.compte_principal)";

if(FREE_ACCESS)
    $sql .= "   INNER JOIN licences_staffs AS ls";
else
    $sql .= "   LEFT JOIN licences_staffs AS ls";
$sql .= "
            ON (staffs.licence = ls.id)
            WHERE (pseudo='" . $pseudo . "' OR mail='" . $pseudo . "')
            AND pass='" . $pass . "'
            AND comptes_principaux.bloque = '0' ";
if(!FREE_ACCESS)
    $sql .= "   AND ( ls.date_fin > '" . time() . "' OR ls.incluse = '1' OR staffs.gerant = '1')";

//echo $sql;
//echo $sql;

$db->Query($sql);
//echo $db->Error();
//echo $db->GetHTML();


$row = $db->Row();
if ($db->RowCount()) {
        $_SESSION['id'] = $row->id;
        $_SESSION['prenom'] = $row->prenom;
        $_SESSION['nom'] = $row->nom;
        $_SESSION['mail'] = $row->mail;
        $_SESSION['compte_principal'] = $row->compte_principal;
        $_SESSION['status'] = $row->status_chat;
        $_SESSION['gerant'] = $row->gerant;


        $db->Query('UPDATE staffs SET session_id = "' . session_id() . '" WHERE id="' . $row->id . ' "');
        if ($_SESSION['id'] > 1) {

            $mail = new Maileur('Connexion de ' . $_SESSION['prenom'] . ' sur managy');
            $mail->addExpediteur('noreply@managy.fr', 'Managy');
            $mail->addDest('contact@depaninfo67.com');
            $mail->AddTitle('Connexion de ' . $_SESSION['prenom']);
            $mail->setLogo('managy');

            $texte = '<strong>' . $_SESSION['prenom'] . '</strong> vient de se connecter sur managy.fr<br />
        Nom de la société : <strong>' . $row->nom_societe . '</strong><br />
        Adresse IP de connexion : <strong>' . $_SERVER['REMOTE_ADDR'] . '</strong>';


            $mail->body($texte);
            $mail->send();


            //Notification pour le gérant de chaque société
            if (!$_SESSION['gerant']) {
                $notif = new notificationMail(5);
                $notif->tab_parse(array('%prenom_staff%' => $_SESSION['prenom'], '%nom_staff%' => $_SESSION['nom'], '%ip%' => $_SERVER['REMOTE_ADDR']));
                $notif->sendMail();
            }

        }
        $log = new addLog('s\'est connecté sur Managy.fr (IP : ' . $_SERVER["REMOTE_ADDR"] . ')');
        $log->insert();
        echo 'true';
}
else
    echo 'false';



$db->Close();

?>