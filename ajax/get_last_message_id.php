<?php
session_start();
require('../classes/mysql.class.php');
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/03/2016
 * Time: 11:15
 */


$db = new MySQL();

$sql = 'SELECT chat.id
        FROM chat
        INNER JOIN staffs
        ON (staffs.id = chat.id_destinataire)
        OR (staffs.id = chat.id_expediteur)
        WHERE
           (chat.id_destinataire = staffs.last_chat
              AND chat.id_expediteur = "'.$_SESSION['id'].'"
           )
        OR
            (
            chat.id_destinataire = "'.$_SESSION['id'].'"
                AND chat.id_expediteur = staffs.last_chat
            )
        ORDER BY chat.id DESC
        LIMIT 1';

$db->Query($sql);
$row = $db->Row();

echo 'msg-'.$row->id;
?>