<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 17/02/2016
 * Time: 08:00
 */



$widget = new WidgetBox('Tableau des clients');

$clients = new HtmlTable('', 'table table-striped table-bordered table-hover order-column table-datatable');
$clients->addTSection('thead');
$clients->addRow();
$clients->addCell(CUSTOMERS_NUM_CUSTOMER, '', 'thead');
$clients->addCell(CUSTOMERS_CUSTOMER_NAME, '', 'thead');
$clients->addCell(CUSTOMERS_MAIL, '', 'thead');
$clients->addCell(CUSTOMERS_ADRESS, '', 'thead');
$clients->addCell(CUSTOMERS_GSM, '', 'thead');
$clients->addCell(CUSTOMERS_PHONE, '', 'thead');
$clients->addCell('Nombre interventions', '', 'thead');
if(AccesActivableModul('pack_maintenance') AND right('pack-maintenance', 1))
    $clients->addCell('Solde pack maintenance (h)', '', 'thead');

$clients->addTSection('tbody');


foreach ($tab_clients AS $value_client)
{
    if($value_client['archive'])
        $color = 'danger';
    else
    {
        if ($value_client['pro_part'] == 1)
            $color = 'warning';
        else
            $color = 'success';
    }

    $num = new Label($color, new Font('eye'), './c'.$value_client['id']);

    $clients->addRow('', array('onclick'=>'document.location=\'./c'.$value_client['id'].'\'', 'style'=>'cursor: pointer;'));

    $clients->addCell($num);
    $clients->addCell($value_client['name'].' '.$value_client['lname']);
    $clients->addCell($value_client['mail']);
    $clients->addCell($value_client['adresse'].' '.$value_client['adresse_suite'].' '.$value_client['cp'].' '.$value_client['ville']);
    $clients->addCell($value_client['portable']);
    $clients->addCell($value_client['fixe']);
    $clients->addCell($value_client['nb_inter']);
    if(AccesActivableModul('pack_maintenance') AND right('pack-maintenance', 1))
        $clients->addCell($value_client['solde']);

    

}

$pro = new Label('warning', new Font('eye'));
$part = new Label('success', new Font('eye'));
$arch = new Label('danger', new Font('eye'));
$widget->setContent($clients. $pro.' Professionnel, '.$part.' Particulier, '.$arch.' Client archivé');


$row = new Row($widget);
echo $row;


?>