<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 16/06/2017
 * Time: 15:15
 */

$_SESSION['referer'] = $_GET['r'];
setcookie('referer', $_GET['r'], time() +(60*60*24*30));
header('location: ./');