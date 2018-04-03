<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 23/06/2017
 * Time: 15:40
 */

include('./../classes/mysql.class.php');
include('./../classes/Maileur.class.php');


$db = new MySQL();
include('./../constructPage/settings.gen.inc');



$sql = 'SELECT * FROM mails_managy';
$sql = $db->Query($sql);

$i = 0;
$tab_mails = Array();
while($row = $db->Row())
{
    $tab_mails[$i]['titre'] = $row->titre;
    $tab_mails[$i]['sujet'] = $row->sujet;
    $tab_mails[$i]['message'] = $row->message;
    $tab_mails[$i]['jours'] = $row->jours;
    $tab_mails[$i]['in_fa'] = $row->inscription_finabo;
    $i++;
}

$sql = 'SELECT id, nom_societe, fin_abo, date_inscription, mail_contact FROM comptes_principaux';

$db->Query($sql);

while($row = $db->Row())
{
    $ajd = time();
    $fa = $row->fin_abo;
    $in = $row->date_inscription;

    $diff_fa = round(($ajd-$fa)/60/60/24 );
    $diff_in = round(($ajd-$in)/60/60/24 );

    foreach ($tab_mails AS $v)
    {
        if(($v['in_fa'] == 'inscription' AND $diff_in == $v['jours']) OR ($v['in_fa'] == 'fin_abo' AND $diff_fa == $v['jours'] AND !FREE_ACCESS))
        {
            $message = str_replace('::societe::', $row->nom_societe, $v['message']);

            $mail = new Maileur($v['sujet']);
            $mail->addDest($row->mail_contact);
            $mail->addDest('contact@managy.fr');
            $mail->AddTitle($v['titre']);
            $mail->body($message);
            $mail->addSignature('Cyril de Managy', 'https://www.managy.fr', 'contact@managy.fr', '06.87.47.00.91');
            $mail->send();
        }
    }
}