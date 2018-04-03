<?php session_start();
require('../classes/mysql.class.php');

$db = new MySQL();

if(isset($_POST['message']))
{
    $message = $db->SQLFix($_POST['message']);

    $db->Query('SELECT last_chat FROM staffs WHERE id = "'.$_SESSION['id'].'" ');
    $r = $db->Row();

    if(!empty($r->last_chat)) {
        $db->Query('INSERT INTO chat (id_expediteur, id_destinataire, message, timestamp) VALUES ("' . $_SESSION['id'] . '", "' . $r->last_chat . '", "' . $message . '", "' . time() . '" ) ');
        $db->Query('UPDATE staffs SET last_action = "'.time().'" WHERE id = "'.$_SESSION['id'].'" ');
    }
}
?>