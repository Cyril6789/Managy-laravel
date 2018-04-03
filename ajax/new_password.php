<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 31/12/2016
 * Time: 14:26
 */

include('../classes/mysql.class.php');
include('../functions/crypt_pass.func.php');
include('../classes/Maileur.class.php');

$db = new MySQL();



$pass = $db->SQLFix($_GET['pass']);
$mot = $db->SQLFix($_GET['mot']);
$id_staff = $db->SQLFix($_GET['id']);

if($pass != '' AND $mot != '' AND is_numeric($id_staff) AND $id_staff != '')
{

    $pass_c = crypt_pass($pass);

    $sql = 'UPDATE staffs SET pass = "'.$pass_c.'", forgot="" WHERE id="'.$id_staff.'" AND forgot="'.$mot.'"';
    $db->Query($sql);


    $db->Query("SELECT  staffs.mail, staffs.prenom
            FROM staffs
            INNER JOIN comptes_principaux
            ON (comptes_principaux.id = staffs.compte_principal)
            LEFT JOIN licences_staffs AS ls
            ON (staffs.licence = ls.id)
            WHERE (staffs.id='".$id_staff."')
            AND comptes_principaux.bloque = '0'
            AND
                ( ls.date_fin > '".time()."' OR ls.incluse = '1' OR staffs.gerant = '1')
            ");


    $row = $db->Row();
    $mail_staff = $row->mail;
    $prenom = $row->prenom;

    $mail = new Maileur('Mot de passe modifié');
    $mail->addExpediteur('noreply@managy.fr', 'Managy.fr');
    $mail->addDest($mail_staff);
    $mail->AddTitle('Mot de passe réinitialisé');
    $mail->setLogo('managy');

    $texte = 'Bonjour '.$prenom.'<br /><br />Votre mot de passe vient d\'être réinitialisé.<br /><br />Pour des raisons de sécurités, en voici qu\'une partie : <strong>'.$pass[0].'*****</strong>.<br />Ne le communiquez à personne.<br /><br />Si ce n\'est pas vous qui avez fait cette demande, veuillez modifier le mot de passe d\'accès à votre messagerie ('.$mail_staff.'), et de relancer une <a href="https://www.managy.fr/forgot.html">demande de réinitialisation de mot de passe</a> de Managy.fr.';


    $mail->body($texte);
    $mail->send();

    echo 'true';
}
else
{
    echo 'false';
}
$db->Close();


?>