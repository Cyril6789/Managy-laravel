<?php session_start();
global $db;

$sql = 'SELECT *
FROM maintenance
WHERE id_client = "'.$id_customer.'"
AND compte_principal = "'.$_SESSION['compte_principal'].'"
ORDER BY id DESC';
$db->Query($sql);
echo $db->Error();
$i=0;
$tab_maintenances = Array();
while ($row3 = $db->Row())
{
    $tab_maintenances[$i]['date'] = $row3->date_mouvement;
    $tab_maintenances[$i]['mouvements'] = $row3->mouvements;
    $tab_maintenances[$i]['commentaires'] = stripslashes($row3->commentaires);
    $i++;
}


$requete = "SELECT SUM(mouvements) AS solde
FROM maintenance
WHERE id_client = '".$id_customer."'
AND compte_principal = '".$_SESSION['compte_principal']." '";
$db->Query($requete);
$d = $db->Row();

if($d->solde)
{
    $heures = floor(abs($d->solde));
    $minutes = (abs($d->solde) - $heures) * 60;
    if($d->solde < 0)
        $moins = '-';
    else
        $moins = '';

    if(round($minutes) < 10)
        $solde = $moins.$heures.'h0'.round($minutes);
    else
        $solde = $moins.$heures.'h'.round($minutes);
}
else
    $solde = 0;


$sql = 'SELECT COUNT(*) AS nb
        FROM chrono_maintenance
        WHERE debut > 0
        AND id_client = "'.$id_customer.'"
        AND compte_principal ="'.$_SESSION['compte_principal'].'"  ';
$db->Query($sql);

$d = $db->Row();

if($d->nb)
    $started = true;
else
    $started = false;

if(!$started)
{
    $sql = 'SELECT buffer
        FROM chrono_maintenance
        WHERE id_client = "' . $id_customer . '"
        AND compte_principal ="' . $_SESSION['compte_principal'] . '"  ';
    $db->Query($sql);

    $d = $db->Row();
    $buffer = $d->buffer;
    $temp = round($buffer / 60);
    $heures_afficher = intval($buffer / 3600);
    $minutes_afficher = intval(($buffer % 3600) / 60);
}


?>