<?php session_start();

include('../templates/v4/classes/includes.php');
include('../moduls/customers/classes/GetInfosCustomer.class.php');
include('../functions/right.func.inc');
include('../functions/parseDate.func.inc');
include('../moduls/staffs/classes/GetInfosStaff.class.php');



$table = new HtmlTable();
$table->addTSection('thead');
$table->addRow();
$table->addCell('Bon de livraison', '', 'thead');
$table->addCell('Client', '', 'thead');
$table->addCell('N° intervention', '', 'thead');
$table->addCell('Date', '', 'thead');
$table->addCell('Historique', '', 'thead');
$table->addCell('Actions', '', 'thead', Array('width' => "5%"));
$table->addTSection('tbody');


$tab_modals = Array();
foreach ($tab_bls AS $bl)
{

    $client = new GetInfosCustomer($bl['id_client']);

    $table->addRow();
    $table->addCell('<strong><a href="./bl-'.date('Y', $bl['timestamp']).$bl['id_bl'].'.pdf"><img src="./../templates/v4/img/icons/packs/fugue/16x16/document-pdf.png"/> '.date('Y', $bl['timestamp']).$bl['id_bl'].'.pdf</a></strong>');
    $table->addCell('<a href="'.$client->GetProfileLink().'">'.$client->getFullName().'</a>');
    $inters = '';
    foreach ($bl['inters'] AS $inter)
        $inters .= '<strong><a href="./i'.$inter.'" >Intervention N°'.$inter.'</a><br ></strong>';
    $table->addCell($inters);
    $table->addCell(date('d/m/Y', $bl['timestamp']));


    $histo = '';
    foreach ($bl['histo'] AS $his) {
        $staff = new GetInfosStaff($his['id_staff']);
        $histo .= '<i class="fa fa-history"></i> Mail envoyé par <a href="'.$staff->GetProfileLink().'">'.$staff->GetPrenom() . ' ' . $staff->GetNom().'</a> '.parse_date($his['timestamp']) . ' ('.$his['destinataire'].')<br />';
    }

    $pop = '';
    if($histo)
    {
        $pop = new Modal('Historique', 'histo_'.$bl['id_bl']);
        $pop->setContent($histo);
        $pop->openLink('<i class="fa fa-history"></i> Historique');
        $tab_modals[] = $pop->getModalHtml();
    }
    if(is_object($pop))
        $table->addCell($pop->getOpenHtml());
    else
        $table->addCell();

    $mail_modal = new Modal('Envoyer par mail', "mail_bl_".$bl['id_bl']);
    $mail_form = new FormLayout("Saisie");
    $mail_form->setFormControls("form_mail_".$bl['id_bl']);

    $mail = new Select("mail_".$bl['id_bl']);
    $mail->withSearch();
    if($client->GetMail())
        $email = $client->GetMail();
    else
        $email = 'Aucune adresse mail';
    $mail->addOption($bl['id_client'].'_'.$client->GetMail(), $client->getFullName().' ('.$email.')', 0, $client->GetMail() ? 0 : 1);
    foreach ($client->loadContacts() AS $contact)
    {
        if($contact->GetMail())
            $email = $contact->GetMail();
        else
            $email = 'Aucune adresse mail';
        $mail->addOption($contact->getId().'_'.$contact->getMail(), $contact->getFullName().' ('.$email.')', 0, $contact->GetMail() ? 0 : 1);
    }

    $mail_form->addLine("Destinataire", $mail);
    
    $sujet = new Text("sujet_".$bl['id_bl']);
    $sujet->setValue('Votre bon de livraison N°bl-'.date('Y', $bl['timestamp']).$bl['id_bl'].'.pdf');
    $mail_form->addLine('Sujet', $sujet);

    $text = new Textarea('text_'.$bl['id_bl']);
    $text->Wysiwyg();
    $text->setValue("Cher client,\n\nVeuillez trouvez ci-joint votre BL concernant l'intervention terminée dans nos ateliers\n\nCordialement");
    $text->setRows(8);

    $mail_form->addLine("Corps", $text);
    $mail_form->addLine("Pièce jointe", '<strong><img src="./../templates/v4/img/icons/packs/fugue/16x16/document-pdf.png"/> bl-'.date('Y', $bl['timestamp']).$bl['id_bl'].'.pdf</strong>');

    $mail_modal->setContent($mail_form);
    $mail_modal->setOnclickButton('Envoyer', 'mailing_ajax(\''.$bl['id_bl'].'\', \''.date('Y', $bl['timestamp']).$bl['id_bl'].'\');', '', false);
    $tab_modals[] = $mail_modal->getModalHtml();

    $bouton = new DropdownButton();
    $bouton->addSubButton('<i class="fa fa-envelope"></i> Mail', $mail_modal->getAhref());
    $bouton->addSubButton('<i class="fa fa-pencil"></i> Modifier', 'bl-edit-'.$bl['id_bl']);

    $edit = new Button('Modifier', 'bl-edit-'.$bl['id_bl']);
    $edit->setFullWidth();
    $table->addCell($bouton);

}

echo $table;

foreach ($tab_modals AS $mod)
    echo $mod;



?>
