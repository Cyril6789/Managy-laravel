<?php session_start();

require('../classes/mysql.class.php');

$db = new MySQL();


if(!empty($_GET['barcode']))
{
    $barcode = $db->SQLFix($_GET['barcode']);
    $db->Query('SELECT couleurs.nom AS nom_couleur, aspects.nom AS nom_aspect, couleurs.ral, sc.stock, cc.id_couleur, cc.id_aspect
                FROM codebarres_couleurs AS cc
                INNER JOIN couleurs
                ON (couleurs.id = cc.id_couleur)
                INNER JOIN aspects
                ON (aspects.id = cc.id_aspect)
                LEFT JOIN stocks_couleurs AS sc
                ON (sc.id_couleur = cc.id_couleur)
                AND (sc.id_aspect = cc.id_aspect)
                AND (sc.compte_principal ="'.$_SESSION['compte_principal'].'")
                WHERE code LIKE "%'.$barcode.'%"
                AND couleurs.compte_principal="'.$_SESSION['compte_principal'].'"
                AND cc.compte_principal="'.$_SESSION['compte_principal'].'"
                AND aspects.compte_principal="'.$_SESSION['compte_principal'].'" ');
    echo $db->Error();
    $i=0;
    $tab_resultats = Array();
    while ($row = $db->Row())
    {
        $tab_resultats[$i]['couleur'] = $row->nom_couleur;
        $tab_resultats[$i]['ral'] = $row->ral;
        $tab_resultats[$i]['aspect'] = $row->nom_aspect;
        $tab_resultats[$i]['stock'] = $row->stock;
      
        $tab_resultats[$i]['id_c'] = $row->id_couleur;
        $tab_resultats[$i]['id_a'] = $row->id_aspect;
        $i++;
    }
    if(is_file('../templates/mango/ajax/load_color_and_stocks.php'))
    {
        include('../templates/mango/ajax/load_color_and_stocks.php');
    }
}


if(!empty($_GET['couleur']) AND !empty($_GET['aspect']))
{
    $couleur = $db->SQLFix($_GET['couleur']);
    $aspect = $db->SQLFix($_GET['aspect']);
    $db->Query('SELECT c.nom AS nom_couleur, c.ral, a.nom AS nom_aspect, sc.stock
                FROM couleurs AS c
                INNER JOIN aspects AS a
                LEFT JOIN stocks_couleurs AS sc
                ON (sc.id_couleur = c.id)
                AND (sc.id_aspect = a.id)
                AND (sc.compte_principal ="'.$_SESSION['compte_principal'].'")
                WHERE c.id = "'.$couleur.'" 
                AND a.id = "'.$aspect.'"
                AND c.compte_principal="'.$_SESSION['compte_principal'].'" 
                AND a.compte_principal="'.$_SESSION['compte_principal'].'"  ');
    echo $db->Error();
    $i=0;
    $tab_resultats = Array();
    while ($row = $db->Row())
    {
        $tab_resultats[$i]['ral'] = $row->ral;
        $tab_resultats[$i]['couleur'] = $row->nom_couleur;
        $tab_resultats[$i]['aspect'] = $row->nom_aspect;
        $tab_resultats[$i]['stock'] = $row->stock;
      
        $tab_resultats[$i]['id_c'] = $couleur;
        $tab_resultats[$i]['id_a'] = $aspect;
        $i++;
    }
    if(is_file('../templates/mango/ajax/load_color_and_stocks.php'))
    {
        include('../templates/mango/ajax/load_color_and_stocks.php');
    }
}

?>