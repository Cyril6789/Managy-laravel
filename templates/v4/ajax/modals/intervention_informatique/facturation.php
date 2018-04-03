<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 01/09/2017
 * Time: 14:34
 */

$modal = new jsonModal('Facturation matériel');
$modal->width('50%');

if(right('inter', '20'))
{
    $inter = new DataObject('interventions');
    $inter->find($_POST['id_inter'], 'id_inter');

    if($inter->id)
    {
        $client = new GetInfosCustomer($inter->id_client);

        $form_fact = new FormLayout('Facturation - Intervention '.$inter->prefix.$_POST['id_inter']);
        $form_fact->setFormControls('form_fact_'.$_POST['id_inter']);
        $hidden = new Hidden('id_inter');
        $hidden->setValue($_POST['id_inter']);
        $form_fact->addLine('Client', $client->getFullNameWithLink().$hidden);

        $modal->form_id('form_fact_'.$_POST['id_inter']);

        $roui = new Radio('paye', 'oui');
        $roui->checked();
        $rnon = new Radio('paye', 'non');
        $form_fact->addLine('Payé ?', $roui.' Oui<br />'.$rnon.' Non');

        $content = $form_fact;
    }
    else {
        $content = new Danger('Cette intervention n\'existe pas !', false);
        $modal->error();
    }
}
else {
    $content = new Danger('Vous n\'avez pas les droits necessaires pour effectuer la facturation de ce matériel', false);
    $modal->error();
}



$modal->content($content);

echo $modal;