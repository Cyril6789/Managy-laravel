<?php session_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$col = new Col(12);

$pdf = new Button('<span class="icon icon-print"></span> Imprimer toutes ces fiches OF', "./fiche-of-peinture-a-solder.pdf");
$pdf->setClasse('btn-primary');

$col->setContent($pdf.$col);

echo $col;

$widget_tableau = new WidgetBox(INTERVENTION_A_SOLDER_TAB_INTERVENTION);

$tableau = new HtmlTable();
$tableau->addTSection('thead');
$tableau->addRow();
$tableau->addCell('#', '', 'thead');
$tableau->addCell('Nom de la société', '', 'thead');
$tableau->addCell('Détails', '', 'thead');
$tableau->addCell('Solder', '', 'thead');
$tableau->addTSection('tbody');
foreach ($tab_inters AS $inter)
{
    $tableau->addRow();
    $lg = strlen($inter['id_inter']);
    $num_inter = '';
    for($i=$lg; $i<4; $i++)
        $num_inter .= '0';
    $num_inter .= $inter['id_inter'];
    $num = new Label('success', $num_inter, './i'.$inter['id_inter']);
    $tableau->addCell($num);
    $tableau->addCell($inters['titre'].' '.$inter['nom'].' '.$inter['titre']);

    $td = $inter['nom_coul'].' ('.$inter['nom_ral'].', '.$inter['aspect'].')'.' Quantité : '.$inter['qte_coul'].'Kg (Déconditionné :';
    if($inter['decon'])
        $td .= 'Oui)';
    else
        $td .= 'Non)';

    $td .= '<br />Forfait : ';
    if($inter['forfait'])
        $td .= 'Oui';
    else
        $td .= 'non';
    $td .= ', Référence chantier :'.$inter['ref_chantier'].'
    <br />Nombre balancelle(s) : '.$inter['nb_balancelle'].'
    <br />Temps d\'accroche : '.$inter['tps_ac'].'
    <br />Temps d\'application : '.$inter['appli'].'
    <br />Temps d\'emballage : '.$inter['emballage'];

    foreach($inter['traitements'] AS $traitement)
    {
        $td .= '<br />'.$traitement['nom'].' : '.$traitement['duree'];
    }
    foreach($inter['etapes'] AS $etape)
    {
        $rd .= '<br />'.$etape['nom'].' : '.$etape['duree'];
    }
    $tableau->addCell($td);

    $btn_a_solder = new Button(new Font('check-square-o').' Solder</span>', "?solder=".$inter['id_inter']);
    $btn_a_solder->setClasse('btn-success');
    $tableau->addCell($btn_a_solder);

}

$widget_tableau->setContent($tableau, false);

$row = new Row($widget_tableau);
echo $row;

?>