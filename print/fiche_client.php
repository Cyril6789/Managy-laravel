<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 24/03/2017
 * Time: 18:24
 */

if(empty($_GET['id']))
    die();

require_once('./../classes/mysql.class.php');

$db = new MySQL();

$db->Query('SELECT id_inter, compte_principal, type_atelier_rdv FROM interventions WHERE external_link="'.$db->SQLFix($_GET['id']).'" ');

//echo $db->GetHTML();
$row = $db->Row();
$_GET['id_inter'] = $row->id_inter;
$_GET['type'] = "sortie";
$_SESSION['compte_principal'] = $row->compte_principal;

$no_change_signature = true;
if($row->type_atelier_rdv == '1')
    include('./imprimer.php');

if($row->type_atelier_rdv == '2')
    include('./inter_site.php');
?>