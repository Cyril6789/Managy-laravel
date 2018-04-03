<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 01/09/2017
 * Time: 14:34
 */

$modal = new jsonModal('Restitution matériel');
$modal->width('50%');

if(right('inter', '22'))
{
    $inter = new DataObject('interventions');
    $inter->find($_POST['id_inter'], 'id_inter');

    if($inter->id)
    {

        $client = new GetInfosCustomer($inter->id_client);

        $form_rest = new FormLayout('Détails de la réstitution - Intervention '.$inter->prefix.$_POST['id_inter']);
        $form_rest->setFormControls('form_rest_'.$_POST['id_inter']);

        $modal->form_id('form_rest_'.$_POST['id_inter']);

        $hidden = new Hidden('id_inter');
        $hidden->setValue($_POST['id_inter']);
        $form_rest->addLine('Client', '<div class="row">'.$client->getFullNameWithLink().'</div>'.$hidden);

        $date = new Text('date_restitution');
        $date->datePicker();
        $date->setValue(date('d/m/Y'));
        $form_rest->addLine('Date de restitution', $date);

        $roui = new Radio('facture', 'oui');
        $roui->checked();
        $rnon = new Radio('facture', 'non');
        $form_rest->addLine('Facturé ?', $roui.' Oui<br />'.$rnon.' Non');

        $roui = new Radio('paye', 'oui');
        $roui->checked();
        $rnon = new Radio('paye', 'non');
        $form_rest->addLine('Payé ?', $roui.' Oui<br />'.$rnon.' Non');

        $comment = new Textarea('commentaire');
        $form_rest->addLine('Commentaire', $comment);


        $content = $form_rest;
    }
    else {
        $content = new Danger('Cette intervention n\'existe pas !', false);
        $modal->error();
    }

}
else {
    $content = new Danger('Vous n\'avez pas les droits necessaires pour effectuer la restitution de ce matériel', false);
    $modal->error();
}




$modal->content($content);

echo $modal;