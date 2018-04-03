<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 18/11/2017
 * Time: 18:22
 */


$id_compte = $db->SQLFix($_POST['id_compte']);

$modal = new jsonModal(new Font('refresh').' Prolonger un abonnement');
$modal->width('90%');

if($_SESSION['compte_principal'] == 1)
{

    $cp = new DataObject('comptes_principaux');
    $cp->find($id_compte, 'id', false);

    $form = new FormLayout('Prolonger l\'abonnement de '.$cp->nom_societe.'  (compte '.$id_compte.')');
    $form->setFormControls('prol_'.$id_compte);

    $hidden = new Hidden('id_compte_prol_manu');
    $hidden->setValue($id_compte);
    $mois_souscrit = new Text('mois_s');
    $form->addLine('Mois souscrits', $mois_souscrit.$hidden);

    $mois_paye = new Text('mois_p');
    $form->addLine('Mois payés', $mois_paye);

    $prix_base = new Text('prix_b');
    $form->addLine('Prix de base', $prix_base);

    $prix_total = new Text('prix_t');
    $form->addLine('Prix total (avec options) sur la période', $prix_total);

    $commentaires = new Textarea('commentaire');
    $commentaires->setValue("Prolongement effectué par ".$_SESSION['prenom']."\nVirement bancaire N°");
    $commentaires->setRows(5);
    $form->addLine('Commentaire / Id de la transaction', $commentaires);

    $content = $form;

    $modal->form_id('prol_'.$id_compte);

}
else
{
    $content = new Danger('Votre accès ne permet pas de voir ce contenu', 'false');
    $modal->error();
}


$modal->content($content);
echo $modal;