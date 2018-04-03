<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 28/03/2016
 * Time: 12:24
 */
//if(empty($_SESSION['id']))
//    die();

include('./../classes/mysql.class.php');
include('./../classes/smseur.class.php');
include('./../functions/AccesActivableModul.func.inc');

$db = new MySQL();

$sql = 'SELECT * FROM sms_differes WHERE timestamp - '.time().'  < 0 ';
$db->Query($sql);
echo $sql;
echo $db->Error();


while($row = $db->Row())
{
    $message = $row->message;
    $cp = $row->compte_principal;

    if(AccesActivableModul('sms', $cp)) {

        $db2 = new MySQL();

        $db2->Query('SELECT * FROM comptes_principaux WHERE id="' . $cp . '" ');

        $row2 = $db2->Row();

        $sms_exp = $row2->expediteur_sms;
        $signature_sms = $row2->signature_sms;
        $mail_exp = $row2->mail_contact;


        $sms = new Smseur($message, $cp, $mail_exp);
        $sms->AddExpediteur($sms_exp);


        if (is_file('./../moduls/logs/classes/addLog.class.php'))
            require_once('./../moduls/logs/classes/addLog.class.php');

        $db2->Query('SELECT id_client, destinataire FROM sms_differes_destinataires WHERE id_sms = "' . $row->id . '" ');

        while ($row3 = $db2->Row()) {
            $sms->AddNumero($row3->destinataire);
            $sms->setIdCustomer($row3->id_client);
            $sms->setIdStaff($row->id_staff);
            $sms->setIdInter($row->id_inter);
            $sms->setComptePrincipal($row->compte_principal);
            $db3 = new MySQL();
            $db3->Query('DELETE FROM sms_differes_destinataires WHERE id_sms = "' . $row->id . '" ');
            $db3->Close();

            $log = New addLog('a envoyé sms au client', $row->compte_principal);
            $log->setInter($row->id_inter);
            $log->insert();
        }

        $sms->AddSignature($signature_sms);


        $sms->envoie();


        $sql = 'DELETE FROM sms_differes WHERE id = "' . $row->id . '" ';
        $db2->Query($sql);

        $db2->Close();

    }


}


$db->Close();
?>