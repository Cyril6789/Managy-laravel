<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 02/08/2016
 * Time: 23:52
 */
require('../classes/mysql.class.php');
require('../classes/Maileur.class.php');
require('../functions/crypt_pass.func.php');


$db = new MySQL();

$mail_f = $db->SQLFix($_GET['mail']);




$db->Query("SELECT  staffs.id, staffs.prenom
            FROM staffs
            INNER JOIN comptes_principaux
            ON (comptes_principaux.id = staffs.compte_principal)
            LEFT JOIN licences_staffs AS ls
            ON (staffs.licence = ls.id)
            WHERE (mail='".$mail_f."')
            AND comptes_principaux.bloque = '0'
            AND
                ( ls.date_fin > '".time()."' OR ls.incluse = '1' OR staffs.gerant = '1')
            ");


$row = $db->Row();
$id = $row->id;
$prenom = $row->prenom;




$mot = sha1(microtime()).'_'.crypt_pass($mail).'_'.crypt_pass(rand(1, 561655)).'_'.sha1(crypt_pass($id));




if($id>0) {





    $db->Query('UPDATE staffs SET forgot = "'.$mot.'" WHERE id="'.$id.' "');


        $mail = new Maileur('Mot de passe oublié');
        $mail->addExpediteur('noreply@managy.fr', 'Managy.fr');
        $mail->addDest($mail_f);
        $mail->AddTitle('Réinitialisation de votre mot de passe');
        $mail->setLogo('managy');

        $texte = 'Bonjour '.$prenom.'<br /><br />Vous avez demandé à réinitialiser votre mot de passe.<br /><br />Pour valider votre demande, merci de suivre ce lien : <a href="https://www.managy.fr/new_password.html?q='.$mot.'">Mot de passe oublié</a>.<br /><br />Si ce n\'est pas vous qui avez fait cette demande, il vous suffit d\'ignorer ce mail, votre mot de passe n\'a pas été modifié.';




        $mail->body($texte);
        $mail->send();
}


$db->Close();
?>