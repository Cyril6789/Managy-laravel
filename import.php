<?php session_start();
if($_SESSION['id'] != 1)
    die();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 30/03/2017
 * Time: 08:25
 */


function parseTel($tel)
{
    if($tel) {
        $tel = str_replace(' ', '', $tel);
        $tel = str_replace('-', '', $tel);
        $tel = str_replace('/', '', $tel);
        $tel = str_replace(',', '', $tel);
        $tel = str_replace('.', '', $tel);
        $tel = str_replace('|', '', $tel);
        $tel = str_replace('_', '', $tel);

        $tel = '+33 (0)' . $tel[1] . ' ' . $tel[2] . $tel[3] . ' ' . $tel[4] . $tel[5] . ' ' . $tel[6] . $tel[7] . ' ' . $tel[8] . $tel[9];

        return $tel;
    }
    else
        return '';

}

include ('./classes/mysql.class.php');
$db = new MySQL();

$file = 'RIEN.txt';
if(is_file($file)) {

    $monfichier = fopen($file, 'r');

    $i=0;
    while (!feof($monfichier))
    {
        $ligne = fgets($monfichier, 4096);
        if($i>0)
        {
            echo $ligne . '<br />';

            $tab = explode(';', $ligne);


            $titre = 'Sté';

            $tab_n = explode(' ', $tab[1]);

            $prenom = strtolower($tab_n[1]);
            $prenom[0] = strtoupper($prenom[0]);



            $sql = 'INSERT INTO clients (titre, nom, pro_part, mail, adresse, cp, ville, fixe, portable, compte_principal) VALUES ("'.$titre.'", "'.$tab[0].'",  "1", "'.trim($tab[6]).'", "'.$db->SQLFix($tab[1]).'", "'.$tab[2].'", "'.$db->SQLFix($tab[3]).'", "'.parseTel($tab[4]).'", "'.parseTel($tab[5]).'", "26") ';

            echo $sql.'<br /><br />';
            //$db->Query($sql);

        }
        $i++;

    }

}