<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 18/07/2016
 * Time: 16:26
 */


if($_SESSION['id'] == '1') {
    $cloture = false;
    $pris_en_charge = true;
    $forfait = false;
    $disabled = false;
}

if($cloture) {
    $message = new Success('Intervention clôturée '.parse_date($time_cloture), false);
    echo $message;
}

$page = new WidgetBox( $nom_projet.' - '.$nom_couleur.' (Ral : '.$ral_couleur.') : '.$nom_aspect.' - '. $titre . ' ' . $name . ' ' . $lname);
$tabs = new Tab('', 'intervention');
$tabs->fullWidth();

//Saisies
$traitements_etapes = new WidgetBox('Traitements / étapes', 9);


if($rm)
{
    if ($link)
        $the_link = '?undo=rm';
    else
    {
        $the_link = '#';
        $onclick = 'jacascript:void();';
    }

    $class = 'btn-success';
    $icon = new Font('check').' ';
}
else
{
    if ($link)
        $the_link = '?do=rm';
    else
    {
        $the_link = '#';
        $onclick = 'javascript:void();';
    }

    if($pris_en_charge)
        $class = 'btn-primary';
    else
        $class = '';
    $icon = '';
}

$reception_materiel = new Button($icon.'Réception matériel', $the_link);
$reception_materiel->onClick($onclick);
$reception_materiel->setClasse($class);
$reception_materiel->setFullWidth();

$traitementstab = '';
$etapestab = '';

if(!$forfait)
{
    //traitements
    foreach ($tab_traitements AS $traitement)
    {
        if($traitement['done'])
        {
            $class = 'btn-success';
            $action = '?undo=t'.$traitement['id'];
            $icon = new Font('check').' ';
        }
        else
        {

            if($pris_en_charge)
                $class = 'btn-primary';
            else
                $class = '';
            $action = '?do=t'.$traitement['id'];
            $icon = '';
        }

        if($link)
        {
            $the_link = $action;
            $onclick = '';
        }
        else
        {
            $the_link = '#';
            $onclick='javascript:void();';
        }

        $traitement_btn = new Button($icon.'Traitement de surface : '.$traitement['name'], $the_link);
        $traitement_btn->setClasse($class);
        $traitement_btn->onClick($onclick);
        $traitement_btn->setFullWidth();

        $traitementstab .= $traitement_btn;
    }

    //steps
    foreach ($tab_etapes AS $etape)
    {

        if($etape['done'])
        {
            $class = 'btn-success';
            $action = '?undo=e'.$etape['id'];
            $icon = new Font('check');
        }
        else
        {
            if($pris_en_charge)
                $class = 'btn-primary';
            else
                $class = '';
            $action = '?do=e'.$etape['id'];
            $icon = '';
        }

        if($link)
        {
            $the_link = $action;
            $onclick = '';
        }
        else
        {
            $the_link = '#';
            $onclick='javascript:void();';
        }

        $etape_btn = new Button($icon.' '.$etape['name'], $the_link);
        $etape_btn->setClasse($class);
        $etape_btn->onClick($onclick);
        $etape_btn->setFullWidth();

        $etapestab .= $etape_btn;
    }
}

$traitements_etapes->setContent($reception_materiel.$traitementstab.$etapestab, false);

//Tous les boutons d'action
$actions = new WidgetBox('Actions', 3, 'md');

if(right('inter', 3))
{
    $save = new Button('Sauvegarder', 'javascript:void();');
    $save->onClick("$('#type').val('save_$id_inter'); entry.submit();");

    if (!$pris_en_charge)
    {
        $pec = new Button('Prendre en charge', '?action=pec');
        $save->disable();
        if ($cloture)
        {
            $pec->disable();
            $cloture_btn = new Button('Décloturer', '?action=decloture');
            if(right('inter', 8))
                $cloture_btn->setClasse('btn-danger');
            else
                $cloture_btn->disable();
            $cloture_btn->setFullWidth();
        }
        else
        {
            $cloture_btn = new Button('Cloturer', 'javascript:void();');
            $cloture_btn->setFullWidth();
            $cloture_btn->disable();

            $pec->setClasse('btn-danger');
        }
        $pec->setFullWidth();
    }
    else
    {
        $pec = new Button('Ne plus prendre en charge', '?action=nppec');

        if ($cloture)
        {
            $pec->disable();
            $save->disable();
            $cloture_btn = new Button('Décloturer', '?action=decloture');
            if(right('inter', 8))
                $cloture_btn->setClasse('btn-danger');
            else
                $cloture_btn->disable();
            $cloture_btn->setFullWidth();
        }
        else
        {
            $pec->setClasse('btn-danger');
            $save->setClasse('btn-primary');
            $cloture_btn = new Button('Cloturer', 'javascript:void();');
            $cloture_btn->onClick("$('#type').val('cloture_$id_inter'); entry.submit()");
            $cloture_btn->setClasse('btn-primary');
            $cloture_btn->setFullWidth();
        }
        $pec->setFullWidth();
    }
    $save->setFullWidth();
}

if($cloture)
{
    $fiche_of = new Button('Fiche OF', 'javascript:void();');
    $fiche_of->onClick("window.open('./fiche-of-peinture-n-".$id_inter.".pdf', 'inscr','width=700,height=680,left=100,top=10,scrollbars=yes');");
    $fiche_of->setClasse('btn-warning');
    $fiche_of->setFullWidth();
}

$actions->setContent($pec.' '.$save.' '.$cloture_btn.' '.$fiche_of, false);

$saisies = new FormLayout(INTERVENTION_ENTRY, 12, false);
$saisies->setFormControls('entry');
$saisies->setWidth(2, 10);


$type = new Hidden('type');

$spinner1 = new Spinner('i'.$id_inter.'_qtecouleur', 'i'.$id_inter.'_qtecouleur');
$spinner1->setBornes(0, $max_couleur);
$spinner1->setStep(0.5);
$spinner1->setValue($quantite_couleur);
if($disabled)
    $spinner1->disabled();
$saisies->addLine('Quantité de couleur utilisée (Facturable)<br /><strong>'.$stock_couleur.'Kg en stock</strong>', $spinner1.$type);

if(!$forfait)
{
    foreach ($tab_traitements AS $traitement)
    {
        $spinner = new Spinner('i'.$id_inter.'_traitement'.$traitement['id']);
        $spinner->setValue($traitement['time']);
        $spinner->Time();
        $spinner->setStep(15);
        if($disabled)
            $spinner->disabled();
        $saisies->addLine('Temps de traitement : <strong>'.$traitement['name'].'</strong>', $spinner);
    }

    foreach ($tab_etapes AS $etape)
    {
        if($etape['is_time'])
        {
            $spinner = new Spinner('i'.$id_inter.'_etape'.$etape['id']);
            $spinner->setValue($etape['time']);
            $spinner->Time();
            $spinner->setStep(15);
            if($disabled)
                $spinner->disabled();

            $texte = new Text('i'.$id_inter.'_typeetape'.$etape['id']);
            $texte->setValue($etape['type']);
            $texte->placeHolder('Type');
            if($disabled)
                $texte->disabled();
            $saisies->addLine('Temps de traitement : <strong>'.$etape['name'].'</strong>', $spinner.$texte);
        }
    }
}

$balancelles = new Select('i'.$id_inter.'_balancelle');
$balancelles->setSelected($nb_balancelle);
$balancelles->withSearch();
if($disabled)
    $balancelles->disabled();
for($i=1; $i<21; $i++)
{
    $balancelles->addOption($i, $i);
}

$saisies->addLine('Nombre de balancelles', $balancelles);

$accroche = new Spinner('i'.$id_inter.'_accroche');
$accroche->setValue($temps_ad);
$accroche->setStep(15);
$accroche->Time();
if($disabled)
    $accroche->disabled();
$saisies->addLine('Temps d\'accroche', $accroche);

$application = new Spinner('i'.$id_inter.'_application');
$application->setValue($temps_application);
$application->setStep(15);
$application->Time();
if($disabled)
   $application->disabled();
$saisies->addLine('Temps d\'application', $application);

$emballage = new Spinner('i'.$id_inter.'_emballage');
$emballage->setValue($temps_emballage);
$emballage->setStep(15);
$emballage->Time();
if($disabled)
    $emballage->disabled();
$saisies->addLine('Temps d\'emballage', $emballage);

$commentaire = new Textarea('i'.$id_inter.'_comments');
$commentaire->setValue($commentaires);
$commentaire->elastic();
$saisies->addLine('Commentaires :', $commentaire);

$size_client= 8;
if ($pro_part == "1") // pro
{
    $client = new Accordion('Coordonnées', $size_client);
    $client->addContent($titre . ' ' . $name . ' ' . $lname, CUSTOMERS_SOCIETY_NAME.' : <a href="./customers-customer-'.$id_c.'">'.$name.'</a><br /<'.INTERVENTION_ADRESS . ' : ' . $cpy_adress . ' ' . $cpy_adress_suite . ' ' . $cpy_cp . ' ' . $cp_city . '<br />' . INTERVENTION_FIXED_PHONE . ' : ' . $cpy_tel1 . '<br>' . INTERVENTION_MOBILE_PHONE . ' : ' . $cpy_tel2 . '<br />' . INTERVENTION_EMAIL . ' : ' . $cpy_mail);

    $i = 1;
    foreach($tab_contacts AS $contact)
        $client->addContent(CUSTOMERS_CONTACT.' '.$i++.' : '.$contact['nom'].' '.$contact['prenom'], CUSTOMERS_CUSTOMER_NAME.' : <a href="./customers-customer-'.$contact['id'].'">'.$contact['titre'].' '.$contact['nom'].' '.$contact['prenom'].'</a>');
}
else //particulier
{
    $client = new WidgetBox($titre . ' ' . $name . ' ' . $lname, $size_client);
    $client->setLinkOnTitle('./c'.$id_c);
    $client->setContent(INTERVENTION_ADRESS . ' : ' . $cpy_adress . ' ' . $cpy_adress_suite . ' ' . $cpy_cp . ' ' . $cp_city . '<br />' . INTERVENTION_FIXED_PHONE . ' : ' . $cpy_tel1 . '<br>' . INTERVENTION_MOBILE_PHONE . ' : ' . $cpy_tel2 . '<br />' . INTERVENTION_EMAIL . ' : ' . $cpy_mail, false);
}

$col = new WidgetBox('Actions', 4);

if(AccesActivableModul('mail')) {
    $mail = new Mail_Form('Envoyer un mail', 'mail_modal', $id_inter);
    $mail->openButton(new Font('envelope').' Envoyer un mail', 'btn-primary btn-block');
    $mail->setSubmitButton('mail_form', 'Envoyer le mail');
    $mail->displayListe();
    $mail->addListDest($liste_mails);
    $mail->putContent('mail_form');
}

if(AccesActivableModul('sms')) {
    $sms = new SMS_form('Envoyer un SMS', 'sms_modal', $id_inter);
    $sms->openButton(new Font('comments-alt').' Envoyer un SMS', 'btn-primary btn-block');
    $sms->setSubmitButton('sms_form', 'Envoyer le SMS');
    $sms->displayListe();
    $sms->addListDest($liste_smss);
    $sms->putContent('sms_form');
}

$col->setContent($mail.'<br />'.$sms, false);


/*
 * ELEMENTS DE BL
 */
if(right('bl', 3) AND AccesActivableModul('bl'))
{
    $widget_bl = new DatabaseWorker('elements_bls');
    $widget_bl->setWidget(BL_TAB_ELEMENTS);
    $widget_bl->displayedFields(Array('description', 'id_unite', 'qte'));
    $widget_bl->labelsDisplayedFields(Array(BL_DESCRIPTION, BL_UNITS, BL_QTY));
    $widget_bl->addWhere('id_inter="'.$id_inter.'"');
    $widget_bl->innerJoin('unites_bls', 'id_unite', 'nom');
    if(!$disabled)
    {
        $widget_bl->activateAdd('i'.$id_inter.'#tab_intervention_bl', 'AddElementBl');
        $widget_bl->activeModify('i'.$id_inter.'#tab_intervention_bl', 'ModifyElementBl', Array('id_inter' => $id_inter));
        $widget_bl->activeDelete('i'.$id_inter.'#tab_intervention_bl', 'DeleteElementBl');
    }

}
/*
 * FIN ELEMENTS DE BL
 */


/*
 * EDITION DE L'INTERVENTION
 */

if($forfait)
{
    $class = 'btn-success';
    $icon = ' '.new Font('check').' ';
    if ($link)
        $the_link = '?undo=forfait';
    else
        $the_link = 'javascript:void();';
}
else
{
    $class = 'btn-primary';
    $icon = '';
    if($link)
        $the_link = '?do=forfait';
    else
        $the_link = 'javascript:void()';
}
$btn_forfait = new Button($icon.'Forfait', $the_link);
$btn_forfait->setClasse($class);
$btn_forfait->setFullWidth();

$col_forfait = new Col();
$col_forfait->setContent($btn_forfait.$col_forfait);


$projetForm = new FormLayout(INTERVENTION_PROJET, 6, false);
$projetForm->setFormControls('projet_form');
if(!$disabled)
    $projetForm->setValueButton(INTERVENTION_MODIFY_BUTTON, 'submit_projet');

$id_intervention = new Hidden('id_inter');
$id_intervention->setValue($id_inter);
$projet_name = new Text('nom_projet');
$projet_name->setValue($nom_projet);
if($disabled)
    $projet_name->disabled();
$projetForm->addLine('Nom du projet', $id_intervention.$projet_name);

$referenece_chantier = new Text('ref_chantier');
$referenece_chantier->setValue($ref_chantier);
if($disabled)
    $referenece_chantier->disabled();
$projetForm->addLine('Référence du chantier', $referenece_chantier);

$matiere = new Select('id_matiere');
$matiere->withSearch();
$matiere->addOption('0', '--');
if($disabled)
    $matiere->disabled();
$matiere->setSelected($id_matiere);
foreach ($tab_matieres AS $value_matiere)
    $matiere->addOption($value_matiere['id'], $value_matiere['name']);
$projetForm->addLine('Matière', $matiere);

$colorForm = new FormLayout(INTERVENTION_COLOR, 6, false);
$colorForm->setFormControls('color_form');
if(!$disabled)
    $colorForm->setValueButton(INTERVENTION_MODIFY_BUTTON, 'submit_couleur');

$selectCouleur = new Select('id_couleur');
$selectCouleur->withSearch();
$selectCouleur->setSelected($id_couleur);
$selectCouleur->addOption('0', '--');
if($disabled)
    $selectCouleur->disabled();
foreach ($tab_colors AS $value_color)
    $selectCouleur->addOption($value_color['id'], $value_color['name'].' (Ral : '.$value_color['ral'].')');
$colorForm->addLine('Couleur', $id_intervention.$selectCouleur);

$selectAspect = new Select('id_finition');
$selectAspect->setSelected($id_aspect);
$selectAspect->withSearch();
$selectAspect->addOption('0', '--');
if($disabled)
    $selectAspect->disabled();
foreach ($tab_aspects AS $value_aspect)
    $selectAspect->addOption($value_aspect['id'], $value_aspect['name']);
$colorForm->addLine('Aspect', $selectAspect);

$checkbox_deconditionne = new CheckBox('deconditionne');

if($deconditionne)
    $checkbox_deconditionne->checked();
if($disabled)
    $checkbox_deconditionne->disabled();
$colorForm->addLine('Déconditionné', $checkbox_deconditionne);

if(!$forfait)
{
    $traitementsForm = new FormLayout('Traitements', 6, false);
    $traitementsForm->setFormControls('traitements_form');
    if(!$disabled)
        $traitementsForm->setValueButton(INTERVENTION_MODIFY_BUTTON, 'submit_traitements');

    foreach ($traitements AS $value_traitement)
    {
        $checkbox_traitement = new CheckBox('traitement_'.$value_traitement['id']);
        if($disabled)
            $checkbox_traitement->disabled();
        foreach ($tab_traitements AS $t_t)
        {
            if($t_t['id'] == $value_traitement['id'])
                $checkbox_traitement->checked();
        }
        $traitementsForm->addLine($value_traitement['name'], $checkbox_traitement);
    }

    $etapesForm = new FormLayout('Etapes', 6, false);
    $etapesForm->setFormControls('etapes_form');
    if(!$disabled)
        $etapesForm->setFormControls(INTERVENTION_MODIFY_BUTTON, 'submit_etapes');

    foreach($etapes AS $value_etape)
    {
        $checkbox_etape = new CheckBox('etape_'.$value_etape['id']);
        if($disabled)
            $checkbox_etape->disabled();

        foreach($tab_etapes AS $t_e)
        {
            if($t_e['id'] == $value_etape['id'])
                $checkbox_etape->checked();
        }
        $etapesForm->addLine($value_etape['name'], $checkbox_etape);
    }
}







/*
 * FIN DE L'EDITION DE L'INTERVENTION
 */

$tabs->addPane('Intervention', $traitements_etapes.$actions.$saisies, 'intervention');
$tabs->addPane('Client', $client.$col, 'customer');
if(right('bl', 3) AND AccesActivableModul('bl'))
    $tabs->addPane('Elements du bon de livraison', $widget_bl, 'bl');



if(right('inter', 9))
{

    /*
     * Commentaires
     */
    $add_comment_widget = new WidgetBox('Ajouter un commentaire');
    $textarea = new Textarea('new_comment');
    $textarea->placeholder('Ajouter un commentaire');

    $form_add_comment = new Form('form_add_comment');
    $form_add_comment->setContent($textarea);

    $button_add_comment = new Button('Ajouter le commentaire', 'javascript:void()', 'Ajouter le commentaire en tant que '.$_SESSION['prenom']);
    $button_add_comment->setClasse('btn-primary');
    $button_add_comment->onClick("form_add_comment.submit();");


    $add_comment_widget->setContent($form_add_comment.'<br />'.$button_add_comment);

    $commentaires = '';

    foreach ($tab_commentaires AS $com)
    {
        $staff = new GetInfosStaff($com['id_staff']);
        $widget_com = new WidgetBox('<a href="'.$staff->GetProfileLink().'">'.$staff->GetPrenom().' '.$staff->GetNom().'</a> - '.parse_date($com['timestamp']));

        $button_suppr = new Button(new Font('remove'), '?suppr_comment='.$com['id'], 'Supprimer ce commentaire');
        $button_suppr->setClasse('btn-xs btn-danger');

        if($_SESSION['id'] == $com['id_staff'] OR $_SESSION['gerant'])
            $widget_com->addToolbarButtons($button_suppr);

        $texte = $com['texte'];

        $texte = preg_replace('#http://[a-z0-9._/-]+#i', '<a href="$0" target="_blank">$0</a>', $texte);
        $texte = preg_replace('#https://[a-z0-9._/-]+#i', '<a href="$0" target="_blank">$0</a>', $texte);

        $widget_com->setContent(nl2br($texte));

        $commentaires .= $widget_com;
    }


    if($nb_commentaires) {
        $badge = new Label('info', $nb_commentaires);
        $tabs->addPane('Commentaires ' . $badge, $add_comment_widget . $commentaires, 'comments');
    }
    else
        $tabs->addPane('Commentaires', $add_comment_widget.$commentaires, 'comments');

}


$hr = new Col();
$tabs->addPane('Modifier l\'intervention', $col_forfait.$projetForm.$colorForm.$hr.$traitementsForm.$etapesForm, 'edit');
if(right('logs', 1)) {
    $logs = new Feeds();

    foreach ($tab_logs AS $log)
    {
        $staff = new GetInfosStaff($log['id_staff']);
        $logs->addLine($staff->GetPrenom().' '.$staff->GetNom().' '.$log['texte'], parse_date($log['time']), $staff->GetProfileLink());
    }
    $tabs->addPane('Suivi employés', $logs, 'logs');
}
$page->setContent($tabs, false);

$row = new Row($page);

echo $row;
?>