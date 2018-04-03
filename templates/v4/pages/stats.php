<?php session_start();


$tmois = Array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');


$selecteur_date = new WidgetBox('Selectionner la période', 4);

$form = new Form('select_date', '', 'get');

$moisf = new Select('mois');
$moisf->withSearch();
for($m = 0; $m<12; $m++)
{
    if($m == $mois-1)
        $selected = true;
    else
        $selected = false;
    $moisf->addOption($m + 1, $tmois[$m], $selected);
}


$anneef = new Select('annee');
$anneef->withSearch();
for($a = 2010; $a<=date('Y'); $a++)
{
    if($a == $annee)
        $selected = true;
    else
        $selected = false;
    $anneef->addOption($a, $a, $selected);
}

$bouton = new Button('Valider', 'javascript:void();');
$bouton->setClasse('btn-primary');
$bouton->onClick("$('#select_date').submit();");

$form->setContent($moisf.$anneef.$bouton);

$selecteur_date->setContent($form, false);

echo new Row($selecteur_date);




if($mois[0] == 0)
    $mois = $mois[1];
$stats_widget = New WidgetBox('Statistiques de '.$tmois[$mois-1].' '.$annee);


$table_stats = new HtmlTable('', 'table table-bordered table-hover table-responsive');
$table_stats->addTSection('thead');
$table_stats->addRow();
$table_stats->addCell('Jours', '', 'thead');
foreach ($tab_staffs AS $staff)
    $table_stats->addCell($staff['prenom'], '', 'thead');

$table_stats->addTSection('tbody');

$total_staff[$staff['id']] = 0;
$jours = Array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
for($jour = 1; $jour<=$nb_jour; $jour++) //on parcour chaque jours du mois
{
    $time_j = mktime(0, 0, 0, $mois, $jour, $annee);
    $table_stats->addRow();

    $table_stats->addCell($jours[date('N', $time_j)-1].' '.date('d/m/Y', $time_j));

    foreach ($tab_staffs AS $staff)
    {
        $td = '';
        $compteur = 0;
        foreach($tab_inters AS $inter)
        {
            if($inter[$staff['id']][$jour]['id_inter'] > 0)
            {
                $td .=  '<b><a href="./i'.$inter[$staff['id']][$jour]['id_inter'].'">Intervention N°'.$inter[$staff['id']][$jour]['id_inter'].'</a></b> : '.$inter[$staff['id']][$jour]['duree'].' heures<br />';
                $compteur++;
            }
        }

        $td .= '<br />';
        if ($compteur)
            $td .= '<b>Total journée : '.$duree_jour[$staff['id']][$jour].' heures</b>';

        $table_stats->addCell($td);
        $total_staff[$staff['id']] += $duree_jour[$staff['id']][$jour];
    }
}

$table_stats->addRow();
$table_stats->addCell('<b>Total mois</b>');

foreach ($tab_staffs AS $staff)
    $table_stats->addCell('<b>'.$total_staff[$staff['id']].' heures</b>');


$stats_widget->setContent($table_stats, false);
echo new Row($stats_widget);

?>
