<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 13/08/2017
 * Time: 15:07
 */

$modal = new jsonModal('Ajouter une sous-traitance');
$modal->width('40%');

$modal->form_id('add_sst_form');

$sst_form = New FormLayout('Saisie');
$sst_form->setFormControls('add_sst_form');
$sst_form->setWidth(5, 7);

$devis = new Text('devis');
$sst_form->addLine('Devis', $devis);

$sous_traitant = new Text('nom');
$sst_form->addLine('Sous-traitant', $sous_traitant);

$num_cde_sst = new Text('num_sst');
$sst_form->addLine('Numéro de commande sous-traitant', $num_cde_sst);

$colis_alle = new Text('colis_alle');
$sst_form->addLine('Numéro colis aller', $colis_alle);

$colis_retour = new Text('colis_retour');
$sst_form->addLine('Numéro colis retour', $colis_retour);

$date_envoie = new Text('date_envoie');
$date_envoie->datePicker();
$date_envoie->setValue(date('d/m/Y'));
$sst_form->addLine('Date d\'envoie', $date_envoie);

$date_retour = new Text('date_retour');
$date_retour->datePicker();
$sst_form->addLine('Date de retour estimé', $date_retour);

$modal->content($sst_form);
echo $modal;