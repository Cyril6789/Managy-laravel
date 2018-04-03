<?php


if (isset($_GET['debut'])) {
    $debut = utf8_decode($_GET['debut']);
} else {
    $debut = "";
}
$debut = strtolower($debut);


require('../classes/mysql.class.php');



$db = new MySQL();


$mot = $db->SQLFix($debut);

$db->Query("SELECT nom_ville, code_postal, nom_departement, nom_region
		FROM villes
		INNER JOIN departement
		ON (departement.code = villes.departement)
		INNER JOIN region
		ON (region.id_region = departement.id_region)
		WHERE code_postal LIKE '".$mot."%'
		OR nom_ville LIKE '".$mot."%'
		ORDER BY nom_ville ASC
		LIMIT 15");
echo $db->Error();
//$liste = Array();


$result = Array();
while($row = $db->Row())
{
    //$//liste[] = $row->nom_ville; //.' ('.$row->code_postal.')';
    $result[] =  array (
        "ville" => utf8_encode($row->nom_ville),
        "cp" => $row->code_postal,
        "departement" => utf8_encode($row->nom_departement),
        "regio" => utf8_encode($row->nom_region),
    );
    
}


        echo json_encode($result);
?>

