<?php session_start();
require('../classes/mysql.class.php');
require('../classes/automatismes.class.php');
require('../classes/Maileur.class.php');
require('../classes/smseur.class.php');
require('../functions/AccesActivableModul.func.inc');
$db = new MySQL();


$id_rdv = $db->SQLFix($_GET['id_rdv']);

$start = $db->SQLFix($_GET['new_time']);
$end = $db->SQLFix($_GET['new_end']);



$tab_mois['Jan'] = '01';
$tab_mois['Feb'] = '02';
$tab_mois['Mar'] = '03';
$tab_mois['Apr'] = '04';
$tab_mois['May'] = '05';
$tab_mois['Jun'] = '06';
$tab_mois['Jul'] = '07';
$tab_mois['Agu'] = '08';
$tab_mois['Sep'] = '09';
$tab_mois['Oct'] = '10';
$tab_mois['Nov'] = '11';
$tab_mois['Dec'] = '12';


$tab_1 = explode(" ", $start);
$mois = $tab_mois[$tab_1[1]];
$jour = $tab_1[2];
$annee = $tab_1[3];
$tab_h = explode(":", $tab_1[4]);
$heure = $tab_h[0];
$min = $tab_h[1];

$debut = mktime($heure, $min, 0, $mois, $jour, $annee);

$tab_2 = explode(" ", $end);
$moisf = $tab_mois[$tab_2[1]];
$jourf = $tab_2[2];
$anneef = $tab_2[3];
$tab_hf = explode(":", $tab_2[4]);
$heuref = $tab_hf[0];
$minf = $tab_hf[1];

$fin = mktime($heuref, $minf, 0, $moisf, $jourf, $anneef);


if($id_rdv[0] == 's')
{
    $id_rdv = trim(str_replace('s', '', $id_rdv));
    $db->Query('UPDATE interventions SET rdv_debut="'.$debut.'", rdv_fin="'.$fin.'" WHERE id_inter="'.$id_rdv.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
    $auto = new automatismes($id_rdv, 'change_rdv');

}
else
    $db->Query('UPDATE rdv SET date="'.$debut.'", date_fin="'.$fin.'" WHERE id="'.$id_rdv.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');

?>