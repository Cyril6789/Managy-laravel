<?php session_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$widget = new WidgetBox('Module "'.$nom_module.'"');

$form_moduls = new Form('form_moduls');

$table_moduls = new HtmlTable('', 'table table-bordered table-hover table-responsive');
$table_moduls->addTSection('thead');
$table_moduls->addRow();
$table_moduls->addCell('Nom de la société', '', 'thead');
$table_moduls->addCell('Accès', '', 'thead');
$table_moduls->addCell('Alias', '', 'thead');
$table_moduls->addCell('Nom propre', '', 'thead');

$table_moduls->addTSection('tbody');
foreach ($tab_societe AS $societe)
{
    $table_moduls->addRow();

    $table_moduls->addCell($societe['nom']);

    $disable = new Radio('acces_'.$societe['id'], 0);
    if($societe['type'] == 0)
        $disable->checked();
    $enable = new Radio('acces_'.$societe['id'], 1);
    if($societe['type'] == 1)
        $enable->checked();
    $enablable = new Radio('acces_'.$societe['id'], 2);
    if($societe['type'] == 2)
        $enablable->checked();

    $prix = new Text('prix_'.$societe['id']);
    $prix->setValue($societe['prix']);

    $table_moduls->addCell($disable.' Désactivé<br />'.$enable.' Activé<br />'.$enablable.' Activable, prix : '.$prix);

    $alias = new Text('alias_'.$societe['id']);
    $alias->setValue($societe['alias']);
    $table_moduls->addCell($alias);

    $nom_propre = new Text('nom_propre_'.$societe['id']);
    $nom_propre->setValue($societe['nom_propre']);

    $table_moduls->addCell($nom_propre);


}

$table_moduls->addRow();

$submit = new Button('Mettre à jour', 'javascript:void();');
$submit->onClick('$(\'#form_moduls\').submit();');
$submit->setClasse('btn-primary');

$table_moduls->addCell($submit);
$hidden = new Hidden('send');
$hidden->setValue(1);
$table_moduls->addCell($hidden);
$table_moduls->addCell();
$table_moduls->addCell();

$form_moduls->setContent($table_moduls);

$widget->setContent($form_moduls, false);

$row = new Row($widget);
echo $row;

?>
