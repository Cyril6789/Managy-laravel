<?php session_start();

$widget_mails = new WidgetBox(MAIL_TAB_MAILS);

$modal_add_mail = new Modal(MAIL_ADD_MAIL, 'modal_add_mail');
$modal_add_mail->openButton(new Font('plus'), 'btn-primary');

$form_add_mail = new FormLayout('Saisie');
$form_add_mail->setFormControls('form_add_mail');

$nom = new Text('mail_nom');
$nom->required();
$form_add_mail->addLine(MAIL_NAME, $nom);

$sujet = new Text('mail_sujet');
$sujet->required();
$form_add_mail->addLine(MAIL_SUBJECT, $sujet);

$titre = new Text('mail_titre');
$titre->required();
$form_add_mail->addLine(MAIL_TITRE, $titre);

$message = new Textarea('mail_message');
$message->setRows(7);
$message->Wysiwyg();
$form_add_mail->addLine(MAIL_MESSAGE, $message);

$widget_help_add_mail = new WidgetBox('Aide');
$widget_help_add_mail->setContent('<p>Utilisez les éléments suivant : %titre% pour M, Mme, Mlle, Sté,<br />
                        %nom%, %prenom%, %id_inter% pour le numero de l\'intervention.<br />%lien_public% pour partager le lien public de l\'intervention.</p>', false);

$modal_add_mail->setContent($form_add_mail.$widget_help_add_mail);
$modal_add_mail->setSubmitButton('form_add_mail', 'Ajouter le mail type');

$widget_mails->addToolbarButtons($modal_add_mail->getOpenHtml());


$table_mails = new HtmlTable();
$table_mails->addTSection('thead');
$table_mails->addRow();
$table_mails->addCell(MAIL_NAME, '', 'thead');
$table_mails->addCell(MAIL_SUBJECT, '', 'thead');
$table_mails->addCell(MAIL_TITRE, '', 'thead');
$table_mails->addCell(MAIL_MESSAGE, '', 'thead');
$table_mails->addCell(MAIL_ACTIONS, '', 'thead');
$table_mails->addTSection('tbody');

foreach ($tab_mails AS $value_mail)
{
    $table_mails->addRow();
    $table_mails->addCell($value_mail['nom']);
    $table_mails->addCell($value_mail['sujet']);
    $table_mails->addCell($value_mail['titre']);
    $table_mails->addCell($value_mail['message']);


    $modal_edit_mail = new Modal('Modifier le mail type', 'modal_edit_mail_'.$value_mail['id']);
    //$modal_edit_mail->openButton('Modifier');

    $form_edit_mail = new FormLayout('Saisie');
    $form_edit_mail->setFormControls('form_edit_mail_'.$value_mail['id']);

    $mail_id = new Hidden('mail_id');
    $mail_id->setValue($value_mail['id']);

    $nom = new Text('mail_nom');
    $nom->required();
    $nom->setValue($value_mail['nom']);
    $form_edit_mail->addLine(MAIL_NAME, $nom.$mail_id);

    $sujet = new Text('mail_sujet');
    $sujet->required();
    $sujet->setValue($value_mail['sujet']);
    $form_edit_mail->addLine(MAIL_SUBJECT, $sujet);

    $titre = new Text('mail_titre');
    $titre->required();
    $titre->setValue($value_mail['titre']);
    $form_edit_mail->addLine(MAIL_TITRE, $titre);

    $message = new Textarea('mail_message');
    $message->setRows(7);
    $message->Wysiwyg();
    $message->setValue($value_mail['message']);
    $form_edit_mail->addLine(MAIL_MESSAGE, $message);

    $modal_edit_mail->setContent($form_edit_mail.$widget_help_add_mail);
    $modal_edit_mail->setSubmitButton('form_edit_mail_'.$value_mail['id'], 'Modifier le mail type');


    $dropdown = new DropdownButton();
    $dropdown->addSubButton(new Font('pencil').' Modifier', $modal_edit_mail->getAhref());
    $dropdown->addSubButton(new Font('remove').' Supprimer', 'javascript:void();', "if(confirm('Êtes vous sûr de vouloir supprimer ce mail ?')) document.location.href='./mail?id_suppr=".$value_mail['id']."';");

    $table_mails->addCell($dropdown.$modal_edit_mail->getModalHtml());


}

$widget_mails->setContent($table_mails.$modal_add_mail->getModalHtml(), false);

$widget_mails = new Row($widget_mails);

echo $widget_mails;

?>

