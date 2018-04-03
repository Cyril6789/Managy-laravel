<?php session_start();


if(right('inter', 3))
{

    $my_inters = new WidgetBox(DASH_MY_INTERVENTIONS);

    $table_my_inters = new HtmlTable('', 'table table-hover');
    $table_my_inters->addTSection('thead');
    $table_my_inters->addRow();
    $table_my_inters->addCell(DASH_NUM_INTER, '', 'thead');
    $table_my_inters->addCell(DASH_CUSTOMER, '', 'thead');
    $table_my_inters->addCell(DASH_PANNE, '', 'thead');
    $table_my_inters->addTSection('tbody');
    foreach ($tab_my_inters AS $value_inter)
    {
        $table_my_inters->addRow('', array('onclick'=>'document.location=\'./i'.$value_inter['id'].'\'', 'style'=>'cursor: pointer;'));

        $num = new Label('warning', $value_inter['prefix'].$value_inter['id'], './i'.$value_inter['id']);


        if(!$value_inter['id_statut'])
            $statut = '<span style="color:green">'.new Font('bookmark').'</span> En cours';
        else
            $statut = '<span style="color:green">'.new Font('bookmark').'</span> '.$value_inter['nom_statut'];

        $table_my_inters->addCell($num.'<br />'.$statut);
        $table_my_inters->addCell($value_inter['name'].' '.$value_inter['lname'].'<br /><strong>'.$value_inter['materiel'].'</strong>');

        $prog = new ProgressBar($value_inter['days'], $day_max);
        $prog->display_text();

        $table_my_inters->addCell($value_inter['panne'].'<br /><br />'.$prog);
    }


    $table_my_inters->addRow();
    $table_my_inters->addCell('<strong>Rendez-vous sur site</strong>', '', 'data', array('colspan'=>3, 'style'=>'text-align: center;'));

    foreach ($tab_my_inters_rdv AS $value_inter)
    {
        $table_my_inters->addRow('', array('onclick'=>'document.location=\'./i'.$value_inter['id'].'\'', 'style'=>'cursor: pointer;'));

        $num = new Label('warning', $value_inter['prefix'].$value_inter['id'], './i'.$value_inter['id']);

        if($value_inter['rdv_debut'])
            $table_my_inters->addCell($num.'<br />'. date('d/m/Y', $value_inter['rdv_debut']).'<br />'.date('H\hi', $value_inter['rdv_debut']).' &rarr; '.date('H\hi', $value_inter['rdv_fin']));
         else
         {
             if($value_inter['priorite'] == -1)
                 $color = '';
             if($value_inter['priorite'] == 0)
                 $color = '#328CA3';
             if($value_inter['priorite'] == 1)
                 $color = '#BD362F';
             $table_my_inters->addCell($num . '<br /><span style="color: '.$color.'"><strong>Rendez-vous à définir</strong></span>');
         }

        $table_my_inters->addCell($value_inter['name'].' '.$value_inter['lname'].'<br />'.$value_inter['adresse'].' '.$value_inter['cp'].' '.$value_inter['ville']);
        $table_my_inters->addCell($value_inter['panne']);

    }

    $my_inters->setContent($table_my_inters);

    //echo $my_inters;


$largeur = 6;

} //if right inter 3

else {

    $largeur = 12;

}



$waiting_inters = new WidgetBox(DASH_INTERVENTIONS_WAITING_CLOSING);

$table_waiting_inters = new HtmlTable('', 'table table-bordered table-striped table-condensed flip-content');
$table_waiting_inters->addTSection('thead');
$table_waiting_inters->addRow();
$table_waiting_inters->addCell(DASH_NUM_INTER, '', 'thead');
$table_waiting_inters->addCell(DASH_CUSTOMER, '', 'thead');
$table_waiting_inters->addCell(DASH_PANNE, '', 'thead');
$table_waiting_inters->addTSection('tbody');

foreach ($tab_inters AS $value_inter)
{
    $table_waiting_inters->addRow('', array('onclick'=>'document.location=\'./i'.$value_inter['id'].'\'', 'style'=>'cursor: pointer;'));

    if($value_inter['pec'])
        $color = 'warning';
    else
        $color = 'danger';

    $num = new Label($color, $value_inter['prefix'].$value_inter['id'], './i'.$value_inter['id']);
    if(!$value_inter['id_statut'])
        $statut = '<span style="color:green">'.new Font('bookmark').'</span> En cours';
    else
        $statut = '<span style="color:green">'.new Font('bookmark').'</span> '.$value_inter['nom_statut'];

   // print_r($value_inter['staffs']);

    $liste_staff = '';

    foreach ($value_inter['staffs'] AS $s)
    {
        $staff = new GetInfosStaff($s['id']);
        $liste_staff .= '<a href="'.$staff->GetProfileLink().'">'.$staff->GetPrenom().'</a><br />';

    }


    $table_waiting_inters->addCell($num.'<br />'.$statut.'<br />'.$liste_staff);
    $table_waiting_inters->addCell($value_inter['name'].' '.$value_inter['lname'].'<br /><strong>'.$value_inter['materiel'].'</strong>');

    $prog = new ProgressBar($value_inter['days'], $day_max);
    $prog->display_text();

    $table_waiting_inters->addCell($value_inter['panne'].'<br /><br />'.$prog);
}

$table_waiting_inters->addRow();
$table_waiting_inters->addCell('<strong>Rendez-vous sur site</strong>', '', 'data', array('colspan'=>3, 'style'=>'text-align: center;'));

foreach ($tab_inters_rdv AS $value_inter)
{
    $table_waiting_inters->addRow('', array('onclick'=>'document.location=\'./i'.$value_inter['id'].'\'', 'style'=>'cursor: pointer;'));

    if($value_inter['pec'])
        $color = 'warning';
    else
        $color = 'danger';

    $num = new Label($color, $value_inter['prefix'].$value_inter['id'], './i'.$value_inter['id']);

    $liste_staff = '';

    foreach ($value_inter['staffs'] AS $s)
    {
        $staff = new GetInfosStaff($s['id']);
        $liste_staff .= '<a href="'.$staff->GetProfileLink().'">'.$staff->GetPrenom().'</a><br />';
    }


    if($value_inter['rdv_debut'])
        $table_waiting_inters->addCell($num.'<br />'. date('d/m/Y', $value_inter['rdv_debut']).'<br />'.date('H\hi', $value_inter['rdv_debut']).' &rarr; '.date('H\hi', $value_inter['rdv_fin']).'<br />'.$liste_staff);
    else
    {
        if($value_inter['priorite'] == -1)
            $color = '';
        if($value_inter['priorite'] == 0)
            $color = '#328CA3';
        if($value_inter['priorite'] == 1)
            $color = '#BD362F';
        $table_waiting_inters->addCell($num . '<br /><span style="color: '.$color.'"><strong>Rendez-vous à définir</strong></span><br />' . $liste_staff);
    }
    $table_waiting_inters->addCell($value_inter['name'].' '.$value_inter['lname'].'<br />'.$value_inter['adresse'].' '.$value_inter['cp'].' '.$value_inter['ville']);
    $table_waiting_inters->addCell($value_inter['panne']);

}

//$waiting_inters->setCollapse();
$waiting_inters->setContent($table_waiting_inters);






if(right('inter', 3))
{
    $widget_others_staff_inter = New WidgetBox('Interventions prises en charge par un collègue');
    $widget_others_staff_inter->setCollapse();
    $widget_others_staff_inter->Collapsed();
    $widget_others_staff_inter->setContent('En cours');

    $col_gauche = new Col(6);
    $col_gauche->setContent($my_inters); // . $widget_others_staff_inter);

}

$col_droite = new Col($largeur);
$col_droite->setContent($waiting_inters);

$tab_general = new Tab('top', 'dashboard');
$tab_general->fullWidth();

$tab_general->addPane('Interventions en cours', $col_gauche.$col_droite, 'interventions');





/*
 * Calendrier
 */

if(right('calendar', 1)) {



    $mon_calendrier = new Calendar('Calendrier', 'full_cal');



//Interventions sur site
    foreach ($tab_inter_site AS $rdv)
    {
        $description = $rdv['jour'].'/';
        if($rdv['mois']+1 < 10)
        {
            $description .= '0';
            $description .= $rdv['mois']+1;
        }
        else
        {
            $description .= $rdv['mois']+1;

        }

        $description .= '/'.$rdv['annee'].' '.$rdv['heure'].':'.$rdv['minute'].'#'.$rdv['jourf'].'/';
        if($rdv['moisf']+1 < 10)
        {
            $description .= '0';
            $description .= $rdv['moisf']+1;
        }
        else
        {
            $description .= $rdv['moisf']+1;

        }

        $description .= '/'.$rdv['anneef'].' '.$rdv['heuref'].':'.$rdv['minutef'].'#'.$rdv['sms_veille'];

        $mon_calendrier->addEvent('s'.$rdv['id'], addslashes($rdv['title']).' ('.$rdv['prefix'].$rdv['id'].')\n'.addslashes($rdv['adresse']), $rdv['annee'], $rdv['mois'], $rdv['jour'], $rdv['heure'], $rdv['minute'], $rdv['anneef'], $rdv['moisf'], $rdv['jourf'], $rdv['heuref'], $rdv['minutef'], $description, $rdv['cal_color']);

    }

//Rendez-vous normaux
    foreach ($tab_rdv AS $rdv)
    {
        $description = $rdv['jour'].'/';
        if($rdv['mois']+1 < 10)
        {
            $description .= '0';
            $description .= $rdv['mois']+1;
        }
        else
        {
            $description .= $rdv['mois']+1;

        }

        $description .= '/'.$rdv['annee'].' '.$rdv['heure'].':'.$rdv['minute'].'#'.$rdv['jourf'].'/';
        if($rdv['moisf']+1 < 10)
        {
            $description .= '0';
            $description .= $rdv['moisf']+1;
        }
        else
        {
            $description .= $rdv['moisf']+1;

        }
        $description .= '/'.$rdv['anneef'].' '.$rdv['heuref'].':'.$rdv['minutef'].'#'.$rdv['sms_veille'].'#'.$rdv['sms_15'].'#'.$rdv['sms_satisfaction'].'#'.$rdv['lieu_client_atelier'];

        if($rdv['lieu_client_atelier'] == 1)
        {
            $adresse = addslashes($rdv['adresse']);
            $color = 'blue';
        }
        else
        {
            $adresse = 'Atelier';
            $color = 'green';
        }

        $mon_calendrier->addEvent($rdv['id'], $rdv['title'].'\n'.$adresse, $rdv['annee'], $rdv['mois'], $rdv['jour'], $rdv['heure'], $rdv['minute'], $rdv['anneef'], $rdv['moisf'], $rdv['jourf'], $rdv['heuref'], $rdv['miutef'], $description, $color);

    }

    //$mon_calendrier->setNotLoad();

    $tab_general->addPane('Calendrier', $mon_calendrier, 'calendar');
}



/*
 * Statistiques
 */


$widget_graphique = new WidgetBox('Interventions ces 12 derniers mois', 12);

$graph_inter = new GraphsLines('graph_inters');
$graph_inter->addColor('#67B7DC', 'Intervention atelier');

$table_time_atelier = Array();
$table_exist_atelier = array();

$tab_mois = Array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aôut', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
foreach($tab_graph AS $val)
{
    if($val['type'] == 1)
    {
        $time = $tab_mois[$val['mois'] - 1].' '.$val['annee']; //mktime(0,0,0,$val['mois'], 1, $val['annee']);
        $table_time_atelier[] = $time;
        $table_exist_atelier[$time]['val'] = $val['nb'];
        $graph_inter->addData($val['nb'], $time);
    }

}

$y = date('Y');
for($m = date('n') - 11; $m<=date('n'); $m++) {
    if ($m < 1) {
        $mois = $m + 12;
        $annee = $y - 1;

    } else {
        $mois = $m;
        $annee = $y;
    }
    $timestamp = $tab_mois[$mois - 1].' '.$annee; //mktime(0, 0, 0, $mois, 1, $annee);

    if(!in_array($timestamp, $table_time_atelier))
        $graph_inter->addData(0, $timestamp);
    else
        $graph_inter->addData($table_exist_atelier[$timestamp]['val'], $timestamp);
}


$graph_inter->addColor('yellow', 'Intervention sur site');
$table_time_site = Array();
$table_exist_site = array();

foreach($tab_graph AS $val)
{
    if($val['type'] == 2)
    {
        $time = $time = $tab_mois[$val['mois'] - 1].' '.$val['annee']; //mktime(0,0,0,$val['mois'], 1, $val['annee']);
        $table_time_site[] = $time;
        $table_exist_site[$time]['val'] = $val['nb'];
        $graph_inter->addData($val['nb'], $time);
    }

}

for($m = date('n') - 11; $m<=date('n'); $m++) {
    if ($m < 1) {
        $mois = $m + 12;
        $annee = $y - 1;

    } else {
        $mois = $m;
        $annee = $y;
    }
    $timestamp = $tab_mois[$mois - 1].' '.$annee;; //mktime(0, 0, 0, $mois, 1, $annee);

    if(!in_array($timestamp, $table_time_site))
        $graph_inter->addData(0, $timestamp);
    else
        $graph_inter->addData($table_exist_site[$timestamp]['val'], $timestamp);
}


$widget_graphique->setContent($graph_inter, false);



$widget_actual_month_pie = new WidgetBox('Matériels mois en cours', 6, 'lg');
$widget_actual_month_pie->addColW(12, 'md');
$actual_month_pie = new GraphsPies('pie_actual_month');

foreach($tab_pie_actual_month AS $val)
{
    $actual_month_pie->addData($val['label'], $val['value']);
}

$widget_actual_month_pie->setContent($actual_month_pie, false);



$widget_previous_month_pie = new WidgetBox('Matériels mois précédent', 6, 'lg');
$widget_previous_month_pie->addColW(12, 'md');
$previous_month_pie = new GraphsPies('pie_previous_month');

foreach($tab_pie_previous_month AS $val)
{
    $previous_month_pie->addData($val['label'], $val['value']);
}

$widget_previous_month_pie->setContent($previous_month_pie, false);


$widget_actual_year_pie = new WidgetBox('Matériels année en cours', 6, 'lg');
$widget_actual_year_pie->addColW(12, 'md');
$actual_year_pie = new GraphsPies('pie_actual_year');

foreach($tab_pie_actual_year AS $val)
{
    $actual_year_pie->addData($val['label'], $val['value']);
}

$widget_actual_year_pie->setContent($actual_year_pie, false);


$widget_previous_year_pie = new WidgetBox('Matériels année précédente', 6, 'lg');
$widget_previous_year_pie->addColW(12, 'md');
$previous_year_pie = new GraphsPies('pie_previous_year');

foreach($tab_pie_previous_year AS $val)
{
    $previous_year_pie->addData($val['label'], $val['value']);
}

$widget_previous_year_pie->setContent($previous_year_pie, false);



$widget_all_pie = new WidgetBox('Matériels depuis le début', 12);
$all_pie = new GraphsPies('pie_all');

foreach($tab_pie_all AS $val)
{
    $all_pie->addData($val['label'], $val['value']);
}

$widget_all_pie->setContent($all_pie, false);



$tab_general->addPane('Statistiques', $widget_graphique.$widget_actual_month_pie.$widget_previous_month_pie.$widget_actual_year_pie.$widget_previous_year_pie.$widget_all_pie, 'stats');

echo $tab_general;

?>

<script>
    /*$(document).ready(function() {
        $('#tab_dashboard_calendar_tab').click(function (e) {
            alert('ok');
            $('#full_cal').fullCalendar('render');
        })
    });*/

    $(document).ready(function () {
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $('#full_cal').fullCalendar('render');
        });
    });

</script>