<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 11/04/2017
 * Time: 17:34
 */
include('./classes/mysql.class.php');
require_once('./classes/notificationMail.class.php');
require_once('./moduls/customers/classes/GetInfosCustomer.class.php');
require_once('./moduls/staffs/classes/GetInfosStaff.class.php');
include('./functions/parseDate.func.inc');
include('./functions/right.func.inc');
include ('./classes/Maileur.class.php');
include ('./classes/PhpMailer/class.phpmailer.php');
$db  = new MySQL();

$key = $db->SQLFix($_GET['k']);

$sql = 'SELECT * FROM mails_with_ack WHERE cle="'.$key.'" ';
$db->Query($sql);
$row = $db->Row();
if($row->id) {

    if($row->id_staff) {
        $staff = new GetInfosStaff($row->id_staff, true);
        $prenom = $staff->GetPrenom();
    }
    else
        $prenom = 'Managy';
    $notification = new notificationMail(10, $row->compte_principal);
    $notification->tab_parse(Array('%sujet%' => $row->sujet, '%client%' => $row->id_client, '%prenom_staff%' => $prenom, '%date_envoie%' => parse_date($row->timestamp, true)));
    $notification->sendMail();

    $sql = 'INSERT INTO accuses_mails (id_mail, timestamp) VALUES ("'.$row->id.'", "'.time().'")';
    $db->Query($sql);

}
$file = './key.png';
$type = 'image/png';
header('Content-Type:'.$type);
header('Content-Length: ' . filesize($file));
readfile($file);