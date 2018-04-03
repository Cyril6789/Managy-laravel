<?php session_start();
if(!$_SESSION['id'])
	die();


if (isset($_GET['debut'])) {
    $debut = utf8_decode($_GET['debut']);
} else {
    $debut = "";
}
$debut = strtolower($debut);


require('../classes/mysql.class.php');



$db = new MySQL();


$mot = $db->SQLFix($debut);

$tab_mot = explode(' ', $mot);

$sql = 'SELECT nom_ville, code_postal, nom_departement, nom_region
		FROM villes
		INNER JOIN departement
		ON (departement.code = villes.departement)
		INNER JOIN region
		ON (region.id_region = departement.id_region)
		WHERE ';
$i = 0;
foreach($tab_mot AS $m) {
	if($i)
		$sql .= ' AND ';
	$sql .= '(
			 code_postal LIKE "' . $m . '%"
			OR nom_ville LIKE "' . $m . '%"
			)';

	$i++;
}
$sql .= 'ORDER BY nom_ville ASC
		LIMIT 15';


//echo $sql;
$db->Query($sql);

//echo $db->Error();
//$liste = Array();


$result = Array();
while($row = $db->Row())
{
    $result[] =  array (
	"id" => utf8_encode($row->code_postal),
	"name" => $row->nom_ville,
);

}


        echo json_encode($result);
?>

