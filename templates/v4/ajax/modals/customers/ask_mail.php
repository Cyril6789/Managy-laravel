<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 28/08/2017
 * Time: 12:06
 */
$modal = new jsonModal('Ajouter l\'adresse mail à ce client');
$modal->width('30%');

if(right('customers', 2))
{
    $client = new GetInfosCustomer($_POST['id_client']);
    if($client->getId())
    {
       $form_layout = new FormLayout('Ajouter l\'adresse mail');
       $form_layout->setFormControls('ask_mail_form');
       $modal->form_id('ask_mail_form');

       $mail = new Email('mail_ask_mail');
       if($client->GetMail())
           $mail->setValue($client->GetMail());
        $form_layout->addLine('Adresse mail ', $mail);

        $content = $form_layout;
    }
    else
    {
        $content = new Danger('Ce client n\'existe pas', false);
        $modal->error();
    }
}
else
{
    $content = new Danger('Vous n\'avez pas les privilèges nécessaires pour modifier ce client', false);
    $modal->error();
}



$modal->content($content);
echo $modal;