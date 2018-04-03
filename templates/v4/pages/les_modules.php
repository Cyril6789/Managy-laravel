<?php session_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$widget_moduls = new WidgetBox('Tableau des modules');

$table_moduls = new HtmlTable('', 'table table-bordered table-hover table-responsive');

$table_moduls->addTSection('thead');
$table_moduls->addRow();
$table_moduls->addCell('Nom du module', '', 'thead');
$table_moduls->addCell('Actions', '', 'thead');
$table_moduls->addTSection('tbody');

foreach($tab_modules AS $module)
{
    $table_moduls->addRow();
    $table_moduls->addCell($module);

    $btn = new Button('Editer', './les_modules-edit?module='.$module, 'Editer ce module');
    $btn->setClasse('btn-primary');
    $table_moduls->addCell($btn);
}

$widget_moduls->setContent($table_moduls, false);
$row = new Row($widget_moduls);
echo $row;

?>