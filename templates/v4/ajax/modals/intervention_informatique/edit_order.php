<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 13/08/2017
 * Time: 15:07
 */

$id_order = $_POST['id_order'];

$order = new DataObject('commandes');
$order->find($id_order);


$modal = new jsonModal('Editer une commande');
$modal->width('40%');
$modal->form_id('edit_order_form_'.$id_order);

$edit_order_form = new FormLayout('Saisie');
$edit_order_form->setFormControls('edit_order_form_'.$id_order);
$edit_order_form->setWidth(5, 7);

$id_cde = new Hidden('id_cde');
$id_cde->setValue($id_order);

$bdc_fe = new Text('bdc', 'bdc');
$bdc_fe->setValue($order->bdc);
$edit_order_form->addLine('Bon de commande', $id_cde.$bdc_fe);

$fournisseur_fe = new Text('fournisseur', 'fournisseur');
$fournisseur_fe->setValue($order->fournisseur);
$edit_order_form->addLine('Fournisseur', $fournisseur_fe);

$num_cde_fe = new Text('num_cde', 'num_cde');
$num_cde_fe->setValue($order->num_cde);
$edit_order_form->addLine('Numéro de commande fournisseur', $num_cde_fe);

$colis_fe = new Text('colis', 'colis');
$colis_fe->setValue($order->colis);
$edit_order_form->addLine('Numéro de colis', $colis_fe);

$date_cde_fe = new Text('date_cde', 'date_cde');
$date_cde_fe->setValue(date('d/m/Y', $order->date_cde));
$date_cde_fe->datePicker();
$edit_order_form->addLine('Date de commande', $date_cde_fe);

$date_reception_fe = new Text('date_reception', 'date_reception');
$date_reception_fe->setValue(date('d/m/Y', $order->date_reception));
$date_reception_fe->datePicker();
$edit_order_form->addLine('Date de récéption estimée', $date_reception_fe);

$modal->content($edit_order_form);
echo $modal;