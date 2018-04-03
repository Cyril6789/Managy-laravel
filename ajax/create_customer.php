<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 26/11/2017
 * Time: 17:57
 */
//print_r($_POST);

require_once('./../classes/mysql.class.php');
require_once('./../moduls/sellsy/classes/sellsyConnect.php');
require_once('./../functions/AccesActivableModul.func.inc');
require_once('./../moduls/logs/classes/addLog.class.php');
$db = new MySQL();
$no_redirect = true;
include('./../moduls/customers/sub/create/ressources/post.inc');
?>