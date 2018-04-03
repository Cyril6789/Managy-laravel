<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 16/08/2017
 * Time: 22:32
 */


$modal = new jsonModal('Modifier un automatisme');
$modal->form_id('edit_modal_'.$_POST['id_auto']);
$modal->width('70%');

if(right('automatismes', 1))
{

    $id = $db->SQLFix($_POST['id_auto']);

    $sql = 'SELECT automatismes.id, id_sms, id_mail, type_atelier_rdv, action, j, h, m, heurefixe, htime, mtime, immediat, dest_client_staff, mails_types.nom, sms_types.titre
                    FROM automatismes
                    LEFT JOIN sms_types
                    ON (id_sms = sms_types.id)
                    AND (sms_types.compte_principal = "' . $_SESSION['compte_principal'] . '")
                    LEFT JOIN mails_types 
                    ON (id_mail = mails_types.id)
                    AND (mails_types.compte_principal = "' . $_SESSION['compte_principal'] . '")
                    WHERE automatismes.compte_principal = "' . $_SESSION['compte_principal'] . '"
                    AND automatismes.id = "' . $id . '"
            ';

    $db->Query($sql);

    $i = 0;
    $auto = Array();
    $row = $db->Row();

    $auto['id'] = $row->id;
    $auto['id_sms'] = $row->id_sms;
    $auto['id_mail'] = $row->id_mail;
    $auto['type_atelier_rdv'] = $row->type_atelier_rdv;
    $auto['action'] = $row->action;
    $auto['j'] = $row->j;
    $auto['h'] = $row->h;
    $auto['m'] = $row->m;
    $auto['heurefixe'] = $row->heurefixe;
    $auto['htime'] = $row->htime;
    $auto['mtime'] = $row->mtime;
    $auto['immediat'] = $row->immediat;
    $auto['dest'] = $row->dest_client_staff;
    $auto['nom_mail'] = $row->nom;
    $auto['nom_sms'] = $row->titre;

//print_r($auto);


    /*
     * Liste des SMS types
     */
    $sql = 'SELECT id, titre FROM sms_types WHERE compte_principal="' . $_SESSION['compte_principal'] . '" ';
    $db->Query($sql);
    $i = 0;
    $tab_sms_types = Array();
    while ($row = $db->Row()) {
        $tab_sms_types[$i]['id'] = $row->id;
        $tab_sms_types[$i]['titre'] = $row->titre;
        $i++;
    }

    /*
     * Liste des mails types
     */
    $sql = 'SELECT id, nom FROM mails_types WHERE compte_principal="' . $_SESSION['compte_principal'] . '" ';
    $db->Query($sql);
    $i = 0;
    $tab_mails_types = Array();
    while ($row = $db->Row()) {
        $tab_mails_types[$i]['id'] = $row->id;
        $tab_mails_types[$i]['nom'] = $row->nom;
        $i++;
    }


    $form_edit = new FormLayout('Saisie');
    $form_edit->setFormControls('edit_modal_' . $id);

    $atelier_rdv = New Select('type_atelier_rdv');
    $atelier_rdv->setSelected($auto['type_atelier_rdv']);
    $atelier_rdv->addOption('1', 'Atelier');
    $atelier_rdv->addOption('2', 'Sur site');
    $hidden = new Hidden('id');
    $hidden->setValue($auto['id']);
    $form_edit->addLine('Intervention', $atelier_rdv . $hidden, true);


    $actionedit = new Select('action');
    $actionedit->setSelected($auto['action']);
    $actionedit->addOption('creation', 'Création');
    $actionedit->addOption('cloture', 'Clôture');
    $actionedit->addOption('sauvegarde', 'Sauvegarde d\'éléments');
    $actionedit->addOption('pec', 'Prise en charge par un technicien');
    $actionedit->addOption('commande', 'Ajout commande');
    $actionedit->addOption('rcommande', 'Commande reçue');
    $actionedit->addOption('stt', 'Mise en sous-traitance');
    $actionedit->addOption('rstt', 'Retour de sous-traitance');
    $actionedit->addOption('heure', 'Heure du rendez-vous (Si intervention sur site)');
    $actionedit->addOption('change_rdv', 'Décalage d\'un rendez-vous (intervention sur site)');
    $form_edit->addLine('Sur l\'action', $actionedit, true);

    $display_mail = '';
    $display_sms = '';
    $display_dest = '';
    $sms_mail = new Select('type_notif');
    if ($auto['id_sms'] > 0) {
        $sms_mail->setSelected('liste_sms');
        $display_mail = 'display: none;';
        $display_dest = 'display: none;';
    }
    if ($auto['id_mail'] > 0) {
        $sms_mail->setSelected('liste_mail');
        $display_sms = 'display: none;';
    }
    $sms_mail->onChange("$('#liste_sms_" . $auto['id'] . "').toggle('slow');$('#liste_mail_" . $auto['id'] . "').toggle('slow');$('#liste_destinataires_" . $auto['id'] . "').toggle('slow');");
    $sms_mail->addOption('liste_sms', 'SMS');
    $sms_mail->addOption('liste_mail', 'Mail');
    $form_edit->addLine('Type de notification', $sms_mail, true);

    $destinataire = new Select('dest');
    $destinataire->setSelected($auto['dest']);
    $destinataire->addOption('1', 'Client');
    $destinataire->addOption('2', 'Technicien en charge');
    $form_edit->addLine('Destinataire de la notification', $destinataire, true, 'liste_destinataires_' . $auto['id'], $display_dest);

    $sms = new Select('id_sms');
    if ($auto['id_sms'] > 0)
        $sms->setSelected($auto['id_sms']);
    foreach ($tab_sms_types AS $smst)
        $sms->addOption($smst['id'], $smst['titre']);
    $form_edit->addLine('SMS', $sms, true, 'liste_sms_' . $auto['id'], $display_sms);

    $mails = new Select('id_mail');
    if ($auto['id_mail'] > 0)
        $mails->setSelected($auto['id_mail']);
    foreach ($tab_mails_types AS $mailst)
        $mails->addOption($mailst['id'], $mailst['nom']);
    $form_edit->addLine('Mail', $mails, true, 'liste_mail_' . $auto['id'], $display_mail);

    $coche_planned = new CheckBox('planned', 'planned_' . $auto['id']);
    if (!$auto['immediat']) {
        $coche_planned->checked();
        $display_j = '';
        $display_onaction = 'display: none;';
    } else {
        $display_j = 'display: none;';
        $display_h = 'display: none;';
        $display_m = 'display: none;';
        $display_onaction = '';
    }

    $coche_planned->onChange("planned_chk('_" . $auto['id'] . "');");
    $form_edit->addLine('Notification planifiée', $coche_planned);

    $col = new Col();
    $col->setContent('Sur l\'action');
    $form_edit->addLine('Déclenchement', $col, false, 'on_action_' . $auto['id'], $display_onaction);

    $j = new Text('j', 'j_' . $auto['id']);
    $j->spinner();
    $j->setValue($auto['j']);
    $form_edit->addLine('Jour', $j, true, 'day_' . $auto['id'], $display_j);

    $coche_heure = new CheckBox('heurefixe', 'heurefixe_' . $auto['id']);


    if (!$auto['heurefixe']) {
        if (!$auto['immediat']) {
            $display_h = '';
            $display_m = '';
        }
        $display_time = 'display: none;';
    } else {
        $coche_heure->checked();
        $display_h = 'display: none;';
        $display_m = 'display: none;';
        $display_time = '';
    }
    $coche_heure->onChange("heurefixecoche('_" . $auto['id'] . "');");
    $form_edit->addLine('Heure fixe', $coche_heure, false, 'heure_fixe_' . $auto['id'], $display_j);

    $h = new Text('h', 'h_' . $auto['id']);
    $h->setValue($auto['h']);
    $h->spinner(-23, 23, 1);
    $form_edit->addLine('Heure', $h, true, 'hour_' . $auto['id'], $display_h);

    $m = new Text('m', 'm_' . $auto['id']);
    $m->setValue($auto['m']);
    $m->spinner(-55, 55, 5);
    $form_edit->addLine('Minute', $m, true, 'minutes_' . $auto['id'], $display_m);

    $time = new Text('time');
    $time->timePicker();
    if ($auto['heurefixe'])
        $time->setValue($auto['htime'] . ':' . $auto['mtime']);
    else
        $time->setValue('17:30');

    $form_edit->addLine('Heure', $time, true, 'timer_' . $auto['id'], $display_time);


}
else
{
    $danger = new Danger('Votre accès ne permet pas la modification d\'un automatisme. Contactez votre gérant', false);
    $form_edit = $danger;
    $modal->error();
}

$modal->content($form_edit);
echo $modal;