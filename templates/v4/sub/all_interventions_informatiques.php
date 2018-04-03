<?php session_start();


/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 16/02/2016
 * Time: 20:09
 */



$widget = new WidgetBox('Tableau des interventions', 12);

$inters = new HtmlTable('', 'table table-bordered table-hover table-responsive table-datatable');
$inters->addTSection('thead');
$inters->addRow();
$inters->addCell('#', '', 'thead');
$inters->addCell('Client', '', 'thead');
$inters->addCell('Ouvert le', '', 'thead');
$inters->addCell('Type', '', 'thead');
$inters->addCell('Panne', '', 'thead');
$inters->addCell('Résolution', '', 'thead');
$inters->addCell('Cloturé le', '', 'thead');
$inters->addTSection('tbody');

foreach ($tab_inters AS $value_inter)
{
    $inters->addRow('', array('onclick'=>'document.location=\'./intervention-'.$value_inter['id'].'\'', 'style'=>'cursor: pointer;'));

    if($value_inter['cloture'])
        $color = 'success';
    else
    {
        if($value_inter['pec'])
            $color = 'warning';
        else
            $color = 'danger';
    }

    $zero = '';
    $lg = strlen($value_inter['id']);
    for($i=$lg; $i<4; $i++)
        $zero .= '0';


    $num = new Label($color, $value_inter['prefix'].$zero.$value_inter['id'], './i'.$value_inter['id']);

    $inters->addCell($num);

    $inters->addCell($value_inter['titre'].' '.$value_inter['name'].' '.$value_inter['lname']);
    $inters->addCell(parse_date($value_inter['ouverture']));

    if($value_inter['type_a_r'] == 1)
        $inters->addCell($value_inter['materiel']);
    else
    {
        if($value_inter['rdv_annule'])
            $inters->addCell('Rendez-vous annulé');
        else
            $inters->addCell('Intervention sur site le '.parse_date($value_inter['rdv_debut']));
    }

    $inters->addCell($value_inter['panne']);
    $inters->addCell($value_inter['resolution']);

    if($value_inter['cloture'] )
        $inters->addCell(parse_date($value_inter['cloture']));
    else
        $inters->addCell('Non clôturé');
}

$widget->setContent($inters);

$row = new Row($widget);
echo $row;



?>
