<?php session_start();
require('../classes/mysql.class.php');

$db = new MySQL();

if(isset($_GET['status']))
{
    $status = $db->SQLFix($_GET['status']);
    $_SESSION['status'] = $status;
    $db->Query('UPDATE staffs SET status_chat = "'.$status.'", last_action = "'.time().'" WHERE id = "'.$_SESSION['id'].'" ');
}
?>