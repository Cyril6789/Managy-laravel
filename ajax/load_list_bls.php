<?php session_start();
/**
 * Created by PhpStorm.
 * User: Staff
 * Date: 22/01/2016
 * Time: 08:35
 */
require('../classes/mysql.class.php');
$db = new MySQL();
$id_client = $db->SQLFix($_GET['id_client']);

$debut = $db->SQLFix($_GET['debut']);

$tab_d = explode('/', $debut);
if($debut)
    $time_d = @mktime(0,0,0,$tab_d['1'],$tab_d['0'], $tab_d['2']);


$fin = $db->SQLFix($_GET['fin']);

$tab_f = explode('/', $fin);
if($fin)
    $time_f = @mktime(29,59,59,$tab_f['1'],$tab_f['0'], $tab_f['2']);
$debut_n = $db->SQLFix($_GET['debut_n']);
$fin_n = $db->SQLFix($_GET['fin_n']);

$sql = 'SELECT id_bl, clients.id AS id_c, titre, nom, prenom, timestamp
                        FROM bls
                        INNER JOIN clients
                        ON (bls.id_client = clients.id)
                        AND (clients.compte_principal = "'.$_SESSION['compte_principal'].'")
                        WHERE bls.compte_principal = "'.$_SESSION['compte_principal'].'"
                        ';

if($id_client)
    $sql .= ' AND clients.id = '.$id_client;

if($time_d)
    $sql .= ' AND timestamp > "'.$time_d.'" ';

if($time_f)
    $sql .= ' AND timestamp < "'.$time_f.'" ';

if($debut_n)
    $sql .= ' AND id_bl >= "'.$debut_n.'" ';
if($fin_n)
    $sql .= ' AND id_bl <= "'.$fin_n.'" ';
$sql .= ' ORDER BY bls.id DESC LIMIT 50';



$db->Query($sql);
$tab_bls = Array();
$i=0;
while($row = $db->Row())
{
    $tab_bls[$i]['id_bl'] = $row->id_bl;
    $tab_bls[$i]['id_client'] = $row->id_c;
    $tab_bls[$i]['titre'] = $row->titre;
    $tab_bls[$i]['nom'] = $row->nom;
    $tab_bls[$i]['prenom'] = $row->prenom;
    $tab_bls[$i]['timestamp'] = $row->timestamp;

    $j=0;
    $db2 = new MySQL();
    $db2->Query('SELECT id_inter FROM bls_interventions WHERE id_bl="'.$row->id_bl.'" AND compte_principal="'.$_SESSION['compte_principal'].'"');
    $tab_bls[$i]['inters'] = Array();
    while($row2 = $db2->Row())
    {
        $tab_bls[$i]['inters'][$j] = $row2->id_inter;
        $j++;
    }


    $j=0;
    $db2->Query('SELECT id_client, id_staff, timestamp, destinataire FROM mails_with_ack WHERE id_bl="'.$row->id_bl.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id DESC ');
    $tab_bls[$i]['histo'] = Array();
    while($row2 = $db2->Row())
    {
        $tab_bls[$i]['histo'][$j]['id_client'] = $row2->id_client;
        $tab_bls[$i]['histo'][$j]['id_staff'] = $row2->id_staff;
        $tab_bls[$i]['histo'][$j]['timestamp'] = $row2->timestamp;
        $tab_bls[$i]['histo'][$j]['destinataire'] = $row2->destinataire;
        $j++;
    }


    $i++;
}


if(is_file('../templates/'.$_GET['template_name'].'/ajax/load_list_bls.php'))
{
    include('../templates/'.$_GET['template_name'].'/ajax/load_list_bls.php');
}
?>
