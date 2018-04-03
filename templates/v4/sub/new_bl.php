<?php session_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$widget = new WidgetBox(BL_NEW_TAB_CUSTOMER);

$table = new HtmlTable();
$table->addTSection('thead');
$table->addRow();
$table->addCell(BL_NEW_NUM, '', 'thead');
$table->addCell(BL_NEW_NAME_CUSTOMER, '', 'thead');
$table->addTSection('tbody');
foreach ($tab_clients AS $client)
{
    $table->addRow();
    $arrow = new Button(new Font('arrow-right'), './bl-inter-'.$client['id'], BL_NEW_SELECT_THIS_CUSTOMER);
    $arrow->setClasse('btn-primary');
    $table->addCell($arrow);
    $table->addCell($client['titre'].' '.$client['nom'].' '.$client['prenom']);
}


$widget->setContent($table, false);

echo $widget;

?>