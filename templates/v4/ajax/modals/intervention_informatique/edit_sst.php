<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 13/08/2017
 * Time: 15:07
 */

$id_sst = $_POST['id_sst'];

$sst = new DataObject('sous_traitances');
$sst->find($id_sst);

$modal = new jsonModal('Editer une sous-traitance');
$modal->width('40%');
$modal->form_id('edit_sst_form_'.$id_sst);

$edit_sst_form = New FormLayout('Saisie');
$edit_sst_form->setFormControls('edit_sst_form_'.$id_sst);
$edit_sst_form->setWidth(5, 7);

$id_ssth = new Hidden('id_sst');
$id_ssth->setValue($id_sst);

$devis = new Text('devis');
$devis->setValue($sst->devis);
$edit_sst_form->addLine('Devis', $id_ssth.$devis);

$sous_traitant = new Text('nom');
$sous_traitant->setValue($sst->nom);
$edit_sst_form->addLine('Sous-traitant', $sous_traitant);

$num_cde_sst = new Text('num_sst');
$num_cde_sst->setValue($sst->num_cde);
$edit_sst_form->addLine('Numéro de commande sous-traitant', $num_cde_sst);

$colis_alle = new Text('colis_alle');
$colis_alle->setValue($sst->colis_alle);
$edit_sst_form->addLine('Numéro colis allée', $colis_alle);

$colis_retour = new Text('colis_retour');
$colis_retour->setValue($sst->colis_retour);
$edit_sst_form->addLine('Numéro colis retour', $colis_retour);

$date_envoie = new Text('date_envoie');
$date_envoie->datePicker();
$date_envoie->setValue(date('d/m/Y', $sst->date_sst));
$edit_sst_form->addLine('Date d\'envoie', $date_envoie);

$date_retour = new Text('date_retour');
$date_retour->datePicker();
$date_retour->setValue(date('d/m/Y', $sst->date_retour));
$edit_sst_form->addLine('Date de retour estimé', $date_retour);

$modal->content($edit_sst_form);
echo $modal;