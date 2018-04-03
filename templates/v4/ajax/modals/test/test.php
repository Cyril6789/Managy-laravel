<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 18/08/2017
 * Time: 09:01
 */

$modal = new jsonModal('Super titre de modal');
$modal->width('50%');
$modal->form_id('form_test');

$form = new FormLayout('Modifier un client');
$form->setFormControls('form_test');

$text = new Text('name');
$form->addLine('Nom', $text);

$prenom = new Text('lastname');
$form->addLine('Prénom', $prenom);

$mail = new Email('mail');
$form->addLine('E-mail', $mail);

$adress = new Text('adress');
$form->addLine('Adresse', $adress);

$cp = new Hidden('customer_cp', 'customer_cp');
$cpac = new Text('auto_v', 'autocomplete-cp');
$city = new Hidden('customer_city', 'customer_city');
$form->addLine('Code postal - Ville', $cpac.$city.$cp);


$modal->content($form);

echo $modal;