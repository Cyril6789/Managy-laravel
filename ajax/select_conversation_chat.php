<?php session_start();
require('../classes/mysql.class.php');

$db = new MySQL();

if(isset($_GET['id_staff']))
{
    $id_staff = $db->SQLFix($_GET['id_staff']);
    $db->Query('UPDATE staffs SET last_chat = "'.$id_staff.'"  WHERE id = "'.$_SESSION['id'].'" ');
}
?>