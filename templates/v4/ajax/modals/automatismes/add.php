<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 16/08/2017
 * Time: 22:23
 */

/*
 * Liste des SMS types
 */

$modal = new jsonModal('Ajouter un automatisme');
$modal->width('70%');
$modal->form_id('add_automatisme');

if(right('automatismes', 1))
{

    $sql = 'SELECT id, titre FROM sms_types WHERE compte_principal="'.$_SESSION['compte_principal'].'" ';
    $db->Query($sql);
    $i = 0;
    $tab_sms_types  = Array();
    while($row = $db->Row())
    {
        $tab_sms_types[$i]['id'] = $row->id;
        $tab_sms_types[$i]['titre'] = $row->titre;
        $i++;
    }

    /*
     * Liste des mails types
     */
    $sql = 'SELECT id, nom FROM mails_types WHERE compte_principal="'.$_SESSION['compte_principal'].'" ';
    $db->Query($sql);
    $i = 0;
    $tab_mails_types  = Array();
    while($row = $db->Row())
    {
        $tab_mails_types[$i]['id'] = $row->id;
        $tab_mails_types[$i]['nom'] = $row->nom;
        $i++;
    }


    $form_add = new FormLayout('Saisie');
    $form_add->setFormControls('add_automatisme');

    $atelier_rdv = New Select('type_atelier_rdv');
    $atelier_rdv->addOption('1', 'Atelier');
    $atelier_rdv->addOption('2', 'Sur site');
    $form_add->addLine('Intervention', $atelier_rdv, true);


    $actionadd = new Select('action');
    $actionadd->addOption('creation', 'Création');
    $actionadd->addOption('cloture', 'Clôture');
    $actionadd->addOption('sauvegarde', 'Sauvegarde d\'éléments');
    $actionadd->addOption('pec', 'Prise en charge par un technicien');
    $actionadd->addOption('commande', 'Ajout commande');
    $actionadd->addOption('rcommande', 'Commande reçue');
    $actionadd->addOption('stt', 'Mise en sous-traitance');
    $actionadd->addOption('rstt', 'Retour de sous-traitance');
    $actionadd->addOption('heure', 'Heure du rendez-vous (Si intervention sur site)');
    $actionadd->addOption('change_rdv', 'Décalage d\'un rendez-vous (intervention sur site)');
    $form_add->addLine('Sur l\'action', $actionadd, true);

    $sms_mail = new Select('type_notif');
    $sms_mail->onChange("$('#liste_sms').toggle('slow');$('#liste_mail').toggle('slow');$('#liste_destinataires').toggle('slow');");
    $sms_mail->addOption('liste_sms', 'SMS');
    $sms_mail->addOption('liste_mail', 'Mail');
    $form_add->addLine('Type de notification', $sms_mail, true);

    $destinataire = new Select('dest');
    $destinataire->addOption('1', 'Client');
    $destinataire->addOption('2', 'Technicien en charge');
    $form_add->addLine('Destinataire de la notification', $destinataire, true, 'liste_destinataires', 'display: none;');

    $sms = new Select('id_sms');
    foreach ($tab_sms_types AS $smst)
        $sms->addOption($smst['id'], $smst['titre']);
    $form_add->addLine('SMS', $sms, true, 'liste_sms');

    $mails = new Select('id_mail');
    foreach ($tab_mails_types AS $mailst)
        $mails->addOption($mailst['id'], $mailst['nom']);
    $form_add->addLine('Mail', $mails, true, 'liste_mail', 'display: none;');

    $coche_planned = new CheckBox('planned');
    $coche_planned->onChange("planned_chk();");
    $form_add->addLine('Notification planifiée', $coche_planned);

    $col = new Col();
    $col->setContent('Sur l\'action');
    $form_add->addLine('Déclenchement', $col, false, 'on_action');

    $j = new Text('j');
    $j->spinner();
    $j->setValue('0');
    $form_add->addLine('Jour', $j, true, 'day', 'display: none;');

    $coche_heure = new CheckBox('heurefixe');
    $coche_heure->onChange("heurefixecoche();");
    $form_add->addLine('Heure fixe', $coche_heure, false, 'heure_fixe', "display: none;");

    $h = new Text('h');
    $h->spinner(-23, 23, 1);
    $h->setValue(0);
    $form_add->addLine('Heure', $h, true, 'hour', 'display: none;');

    $m = new Text('m');
    $m->spinner(-55, 55, 5);
    $m->setValue(0);
    $form_add->addLine('Minute', $m, true, 'minutes', 'display: none;');

    $time = new Text('time');
    $time->timePicker();
    $time->setValue('17:30');
    $form_add->addLine('Heure', $time, true, 'timer', 'display: none;');
}
else
{
    $danger = new Danger('Votre accès ne permet pas l\'ajout d\'un automatisme. Contactez votre gérant', false);
    $form_add = $danger;
    $modal->error();
}

$modal->content($form_add);

echo $modal;