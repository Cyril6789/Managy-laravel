<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 13/08/2017
 * Time: 15:07
 */

$modal = new jsonModal('Ajouter une commande');
$modal->width('40%');

$modal->form_id('add_order_form');

$order_form = new FormLayout('Saisie');
$order_form->setFormControls('add_order_form');
$order_form->setWidth(5, 7);


$bdc_f = new Text('bdc', 'bdc');
$order_form->addLine('Bon de commande', $bdc_f);

$fournisseur_f = new Text('fournisseur', 'fournisseur');
$order_form->addLine('Fournisseur', $fournisseur_f);

$num_cde_f = new Text('num_cde', 'num_cde');
$order_form->addLine('Numéro de commande fournisseur', $num_cde_f);

$colis_f = new Text('colis', 'colis');
$order_form->addLine('Numéro de colis', $colis_f);

$date_cde_f = new Text('date_cde', 'date_cde');
$date_cde_f->datePicker();
$date_cde_f->setValue(date('d/m/Y'));
$order_form->addLine('Date de commande', $date_cde_f);

$date_reception_f = new Text('date_reception', 'date_reception');
$date_reception_f->datePicker();
$order_form->addLine('Date de récéption estimée', $date_reception_f);

$modal->content($order_form);
echo $modal;