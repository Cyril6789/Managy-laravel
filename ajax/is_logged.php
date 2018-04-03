<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 30/09/2017
 * Time: 13:15
 */

require('../classes/mysql.class.php');

$db = new MySQL();

$db->Query('SELECT session_id FROM staffs WHERE id="' . $_SESSION['id'] . '"');
$row = $db->Row();
$session_id = $row->session_id;
if ($session_id == session_id() OR $_SESSION['id'] == 1) //Même PC, même ip, ou moi
    echo 'ok';
else
    echo 'ko';