<?php session_start();

$sms_types = new DatabaseWorker('sms_types');
$sms_types->setWidget(SMS_TAB_SMS);
$sms_types->displayedFields(Array('titre', 'message'));
$sms_types->labelsDisplayedFields(Array('Titre', 'Message'));
$sms_types->activateAdd('sms', 'addSms');
$sms_types->activeModify('sms', 'modifySms');
$sms_types->activeDelete('sms', 'deleteSms');

$sms_types = new Row($sms_types);

echo $sms_types;

?>