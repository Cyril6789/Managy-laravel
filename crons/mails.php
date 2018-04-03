<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 28/03/2016
 * Time: 12:24
 */

include('./../classes/mysql.class.php');
include('./../classes/Maileur.class.php');
include('./../functions/AccesActivableModul.func.inc');

$db = new MySQL();

$sql = 'SELECT * FROM mails_differes WHERE timestamp - '.time().'  < 0 ';
$db->Query($sql);

while($row = $db->Row())
{
    $sujet = $row->sujet;
    $titre = $row->titre;
    $message = $row->message;
    $cp = $row->compte_principal;

    if(AccesActivableModul('mail', $cp)) {
        $db2 = new MySQL();

        $db2->Query('SELECT * FROM comptes_principaux WHERE id="' . $cp . '" ');
        $row2 = $db2->Row();

        $mail_exp = $row2->mail_contact;
        $nom_societe = $row2->nom_societe;
        $logo = $row2->logo;
        $web = $row2->web;
        $tel = $row2->tel;

        $email = new Maileur($sujet);
        $email->AddTitle($titre);

        $db2->Query('SELECT destinataire FROM mails_differes_destinataires WHERE id_mail = "' . $row->id . '" ');
        while ($row3 = $db2->Row()) {
            $email->addDest($row3->destinataire);
            $db3 = new MySQL();
            $db3->Query('DELETE FROM mails_differes_destinataires WHERE id_mail = "' . $row->id . '" ');
            $db3->Close();
        }

        $email->addDest($mail_exp);
        $email->addExpediteur($mail_exp, $nom_societe);
        $email->setLogo($logo);
        $email->addSignature($nom_societe, $web, $mail_exp, $tel);
        $email->body($message);


        $email->send();


        $sql = 'DELETE FROM mails_differes WHERE id = "' . $row->id . '" ';
        $db2->Query($sql);

        $db2->Close();
    }

}


$db->Close();
?>