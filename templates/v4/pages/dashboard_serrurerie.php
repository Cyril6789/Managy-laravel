<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 17/02/2016
 * Time: 10:37
 */

$widget = new WidgetBox('Interventions en cours');

$table = new HtmlTable('', 'table table-hover table-bordered');
$table->addTSection('thead');
$table->addRow();
$table->addCell(DASH_NUM_INTER, '', 'thead');
$table->addCell(DASH_CUSTOMER, '', 'thead');
if(right('inter', 5))
    $table->addCell('Durée totale', '', 'thead');

$table->addTSection('tbody');

foreach ($tab_inters AS $value_inter)
{
    $table->addRow('', array('onclick'=>'document.location=\'./intervention-'.$value_inter['id'].'\'', 'style'=>'cursor: pointer;'));

    $num = new Label('danger', $value_inter['id'], './i'.$value_inter['id']);

    $table->addCell($num);
    $table->addCell($value_inter['name'].' '.$value_inter['lname'].'<br /><strong>'.$value_inter['chantier']);

    if(right('inter', 5))
    {
        $h = floor($value_inter['minutes'] / 60);
        $m = $value_inter['minutes'] - $h * 60;
        $table->addCell($value_inter['heures'] + $h . 'h' . $m . 'min');
    }
}

$widget->setContent($table);

echo $widget;
?>