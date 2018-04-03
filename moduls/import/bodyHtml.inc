<?php session_start();

 
// 1 : on ouvre le fichier
$monfichier = fopen('ral.csv', 'r');

while (!feof($monfichier)) { //on parcourt toutes les lignes
  $ligne = fgets($monfichier, 4096);
  $tab = explode(';', $ligne);
  global $db;
  $db->Query('SELECT COUNT(*) AS nb FROM couleurs WHERE ral ="'.$tab[0].'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
  $row = $db->Row();
  $ral = $tab[0];
  $couleur = $db->SQLfix(utf8_encode($tab[1]));
  if(!$row->nb)
  {
      $db->Query('INSERT INTO couleurs (nom, ral, compte_principal) VALUES ("'.$couleur.'", "'.$ral.'", "'.$_SESSION['compte_principal'].'" ) ');
      echo $tab[0].' -> '.utf8_encode($tab[1]).' : '.$row->nb.'<br />';
  }
}

fclose($monfichier);
?>