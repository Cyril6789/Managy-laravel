<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 08/03/2016
 * Time: 11:35
 */

$notifications = new DatabaseWorker('notifications', false);
$notifications->setWidget('Tableaux des notifications');
$notifications->displayedFields(Array('alias', 'text', 'type', 'timeout'));
$notifications->labelsDisplayedFields(Array('Alias', 'Texte', 'Type de notification', 'Temps d\'affichage (ms)'));
$notifications->activateAdd();
$notifications->activeModify();
$notifications->activeDelete();

$notifications = new Row($notifications);
echo $notifications;

/*
$widget_notifys = new WidgetBox('Tableau des Notifications Managy');


$add_notify_modal = New Modal('Ajouter une notification', 'add_notify');
$add_notify_modal->openButton('<i class="fa fa-plus"></i>', 'btn-xs btn-primary');

$form = new FormLayout('Saisie');
$form->setFormControls('send_new_notify');

$alias = new Text('alias');
$form->addLine('Alias', $alias);

$texte = new Text('text');
$form->addLine('Texte', $texte);

$type = new Select('type');
$type->withSearch();
$type->addOption('success', 'Success', true);
$type->addOption('warning', 'Warning');
$type->addOption('info', 'Info');
$type->addOption('error', 'Error');
$form->addLine('Type', $type);

$timeout = new Text('timeout');
$form->addLine('Timeout (ms)', $timeout);

$line = new Row($form);
$add_notify_modal->setContent($line);
$add_notify_modal->setSubmitButton('send_new_notify', 'Ajouter la notification');
$widget_notifys->addToolbarButtons($add_notify_modal->getOpenHtml());


$table = new HtmlTable('', 'table table-bordered table-hover table-responsive');
$table->addTSection('thead');
$table->addRow();
$table->addCell('Alias', '', 'thead');
$table->addCell('Texte', '', 'thead');
$table->addCell('Temps d\'affichage (ms)', '', 'thead');
$table->addCell('Actions', '', 'thead');

$table->addTSection('tbody');


foreach($tab_notifys AS $noti)
{
    $table->addRow();
    $table->addCell($noti['alias']);

    if($noti['type'] == 'success')
        $alert = new Success($noti['texte'], false);
    if($noti['type'] == 'error')
        $alert = new Danger($noti['texte'], false);
    if($noti['type'] == 'warning')
        $alert = new Warning($noti['texte'], false);
    if($noti['type'] == 'info')
        $alert = new Info($noti['texte'], false);

    $table->addCell($alert);
    $table->addCell($noti['timeout']);
}

$widget_notifys->setContent($table.$add_notify_modal->getModalHtml());

echo $widget_notifys;*/

?>