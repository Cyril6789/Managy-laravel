<?php session_start();
require('../classes/mysql.class.php');

$db = new MySQL();

if(isset($_GET['status']))
{
    $status = $db->SQLFix($_GET['status']);
    $db->Query('UPDATE staffs SET visible_chat = "'.$status.'" WHERE id = "'.$_SESSION['id'].'" ');
}
?>