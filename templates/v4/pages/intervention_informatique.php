<?php session_start();

/*
 * Sellsy
 */

if(AccesActivableModul('sellsy'))
{
    $client_sellsy = new checkCustomer($id_c);
    $right_title = $client_sellsy;
}
?>

<script>
    function cloturer(solde_pm)
    {
        //alert('ok');
        if(0) //$('#inter_busy').val() != 0)
        {
            $('#modale_interdit_cloture2').modal('show');
        }
        else {
            var ok = false;
            for (var i = 0; i < 10; i++)
                if ($('#presta_' + i).val() != '0_0')
                    ok = true;

            var total = 0;

            for (var i = 0; i < 10; i++) {
                if ($('#duree_' + i).val() > 0)
                    total = total + parseFloat($('#duree_' + i).val());
            }

            total = total - 2 * total;
            $('#duree_pm').val(total);

            $('#type').val('cloture_<?php echo $id_inter;?>')
            if (ok) {
                if (solde_pm > 0)
                    $('#modal_pm').modal('show');
                else
                    entry.submit()
            }
            else
                $('#confirm_cloture').modal('show');
        }

    }

    function save()
    {
        $('#type').val('save_<?php echo $id_inter;?>')
        entry.submit();
    }

    $(window).bind('keydown', function(event) {
        if (event.ctrlKey || event.metaKey) {
            switch (String.fromCharCode(event.which).toLowerCase()) {
                case 's':
                    event.preventDefault();
                    save();
                    break;
            }
        }
    });

    function pm_cloture() {

        if($('#add_pm_chk').prop('checked')) {
            $('#pm_add_form').val('1');
            $('#pm_desi_form').val($('#designation_pm').val());
            $('#pm_duree_form').val($('#duree_pm').val());

        } else {
            $('#pm_add_form').val('0');
            $('#pm_desi_form').val('');
            $('#pm_duree_form').val('');
        }

        entry.submit();
    }

    function change_statut_inter(id, value, color)
    {
        $('#dropdown_statut_value').html('<span style="color: '+color+';"><?php echo new Font('bookmark');?></span> '+value+' <span class="caret"></span>');
        klient = new XMLHttpRequest();
        klient.open("GET", "./ajax/change_statut_inter.php?id_inter=<?php echo $id_inter;?>&id_statut="+id);
        klient.send(null);

        if(color == 'green')
            $('#inter_busy').val(0);
        else
            $('#inter_busy').val(1);
    }
</script>
<?php

$heure_pm = floor($solde_pm);
$minutes_pm = round(($solde_pm - $heure_pm) * 60);
if($minutes_pm < 10)
    $minutes_pm = '0'.$minutes_pm;

$modal_pack_maitenance = new Modal('Pack maintenance', 'modal_pm');

$add_pm_chk = new CheckBox('add_pm_chk');
$add_pm_chk->checked();
$add_pm_chk->onChange("$('#action_pm').toggle('slow');");

$tableau_pm = new HtmlTable();
$tableau_pm->addTSection('thead');
$tableau_pm->addRow();
$tableau_pm->addCell('Designation', '', 'thead');
$tableau_pm->addCell('Durée (h)', '', 'thead');
$tableau_pm->addTSection('tbody');
$tableau_pm->addRow();
$desi_pm = new Text('designation_pm');
if($type_atelier_rdv == 1)
    $desi_pm->setValue('Intervention N°'.$prefix.$id_inter);
else
    $desi_pm->setValue('Intervention sur site N°'.$prefix.$id_inter);
$tableau_pm->addCell($desi_pm);
$duree_pm = new Text('duree_pm');
$tableau_pm->addCell($duree_pm);

$force_facture = new CheckBox('force_facture');
$force_facture->onChange("$('#pm_force_form').val(1);");

$md_pm = new Col();
$md_pm->setContent('Ce client possède un solde de pack maintenance positif<br /><br />Solde restant : <strong>'.$heure_pm.'h'.$minutes_pm.'</strong><br /><br />'.$add_pm_chk.' Soustraire les prestations du pack ?<br /><br /><div id="action_pm">'.$tableau_pm.'<br />'.$force_facture.' Des éléments sont à facturer à part</div>');

$modal_pack_maitenance->setContent($md_pm);
$modal_pack_maitenance->setOnclickButton('Clôturer', 'pm_cloture();');
echo $modal_pack_maitenance->getModalHtml();

$modal = new Modal('Êtes vous sûr ?', 'confirm_cloture');
$modal->setContent('Cette intervention ne contient aucune prestation. Êtes vous sûr de vouloir la clôturer quand-meme ?');
$modal->setOnclickButton('Clôturer quand même', "entry.submit()");
echo $modal->getModalHtml();



if($cloture) {
    $message = new Success('Intervention clôturée '.parse_date($time_cloture), false);
    echo $message;
    if ($rdv_annule) {
        $message = new Danger('Intervention sur site supprimée', false);
        echo $message;
    }
}

if($type_atelier_rdv == 1)
{
    $titre_materiel = $nom_materiel;
}
else
{
    $titre_materiel = 'Intervention sur site';
}

$change_customer = '';
if(right('inter', 23) AND !$cloture)
{
    $change_customer_modal = new modalAjax('intervention_informatique', 'change_customer');
    $change_customer_modal->settings(Array('id_inter' => $id_inter));

    $change_customer = '<a href="'.$change_customer_modal->getHref().'" data-placement="top" data-original-title="Changer le client">'.new Font('pencil').'</a>';
    echo $change_customer_modal->getModalHtml();

}


$contact_r = '';
if($pro_part == "1") {
    $contact = new GetInfosCustomer($contact_ref, false, false);

    $modal = new Modal('Changer le contact référent', 'change_contact_ref');
    $modal->openLink(new Font('pencil'));

    $form_contact = new FormLayout('Choix du contact');
    $form_contact->setFormControls('form_change_contact');
    $cons = new Select('contact_ref');
    if($contact_ref)
        $cons->setSelected($contact_ref);
    $cons->addOption('0', 'Aucun');
    foreach($tab_contacts AS $con)
        $cons->addOption($con['id'], $con['prenom'].' '.$con['nom']);

    $form_contact->addLine('Contact',$cons);
    $modal->setContent($form_contact);
    $modal->setSubmitButton('form_change_contact');


    $contact_r = ' - Contact référent : '.$contact->getFullName().' '.$modal->getOpenHtml();

    echo $modal->getModalHtml();

}
$page = new WidgetBox($titre_materiel.' - '. $titre . ' ' . $name . ' ' . $lname.' '.$change_customer.$contact_r, 12);
$tabs = new Tab('Top', 'inter');
$tabs->fullWidth();


if($type_atelier_rdv == '2')
    $size_client = 3;
else
    $size_client = 8;

if ($pro_part == "1") // pro
{
    $client = new Accordion('Coordonnées', $size_client);
    $client->addContent($titre . ' ' . $name . ' ' . $lname, CUSTOMERS_SOCIETY_NAME.' : <a href="./customers-customer-'.$id_c.'">'.$name.'</a><br /<'.INTERVENTION_ADRESS . ' : ' . $cpy_adress . ' ' . $cpy_adress_suite . ' ' . $cpy_cp . ' ' . $cp_city . '<br />' . INTERVENTION_FIXED_PHONE . ' : ' . $cpy_tel1 . '<br>' . INTERVENTION_MOBILE_PHONE . ' : ' . $cpy_tel2 . '<br />' . INTERVENTION_EMAIL . ' : ' . $cpy_mail);


    $i = 1;
    foreach($tab_contacts AS $contact)
    {
        $client->addContent(CUSTOMERS_CONTACT.' '.$i.' : '.$contact['nom'].' '.$contact['prenom'], CUSTOMERS_CUSTOMER_NAME.' : <a href="./customers-customer-'.$contact['id'].'">'.$contact['titre'].' '.$contact['nom'].' '.$contact['prenom'].'</a>');
        $i++;
    }

}
else //particulier
{
    $client = new WidgetBox($titre . ' ' . $name . ' ' . $lname, $size_client);
    $client->setLinkOnTitle('./c'.$id_c);
    $client->setContent(INTERVENTION_ADRESS . ' : ' . $cpy_adress . ' ' . $cpy_adress_suite . ' ' . $cpy_cp . ' ' . $cp_city . '<br />' . INTERVENTION_FIXED_PHONE . ' : ' . $cpy_tel1 . '<br>' . INTERVENTION_MOBILE_PHONE . ' : ' . $cpy_tel2 . '<br />' . INTERVENTION_EMAIL . ' : ' . $cpy_mail, false);
}

if($type_atelier_rdv == 1)
{
    $details_widgetBox = new WidgetBox('Détails', 4);
    $details_widgetBox->scrollable();

    if(right('inter', 13) AND !$disabled)
    {


        $modify_details_modal = new modalAjax('intervention_informatique', 'modify_details');
        //$modify_details_modal->setDebug();
        $modify_details_modal->openButton(new Font('pencil'), 'btn-xs btn-info', false, 'Modifier les détails de l\'intervention');
        $modify_details_modal->settings(Array('id_inter' => $id_inter));




        if($id_statut) {
            $color = 'green';
            if($busy_statut)
                $color = '#CE3F38';
            $drop1 = new DropdownButton('<span style="color: '.$color.';">'.new Font('bookmark').'</span> '.$nom_statut, 'dropdown_statut_value');
        }
        else
            $drop1 = new DropdownButton('<span style="color: green;">'.new Font('bookmark').'</span> En cours', 'dropdown_statut_value');
        //$drop1->setDropUp();
        $drop1->addSubButton('<span style="color: green;">'.new Font('bookmark').'</span> En cours', 'javascript:void()', 'change_statut_inter(0, \'En cours\', \'green\');');
        foreach($tab_statuts AS $stat)
        {
            if($stat['busy'])
                $color = '#CE3F38';
            else
                $color = 'green';
            $drop1->addSubButton('<span style="color: '.$color.';">'.new Font('bookmark').'</span> '.$stat['nom'], 'javascript:void()', 'change_statut_inter('.$stat['id'].', \''.addslashes($stat['nom']).'\', \''.$color.'\');');
        }

        $details_widgetBox->addToolbarButtons($drop1.$modify_details_modal->getModalOpen());


        }

    $hidden_busy = new Hidden('inter_busy');

    $hidden_busy->setValue($busy_statut);

    $html_detail = $hidden_busy."Type de matériel : <strong>$nom_materiel</strong><br />
                        Mot de passe - codes : <strong>$codes</strong><br />
                        Système d'exploitation : <strong>$nom_se</strong><br />
Antivirus : <strong>$nom_antivirus</strong><br />
Prise en charge sous garantie : <strong>";
    if ($garantie)
        $html_detail .= 'Oui';
    else
        $html_detail .= 'Non';
    $html_detail .= "</strong><br />
Urgente : <strong>";
    if ($urgente)
        $html_detail .= 'Oui';
    else
        $html_detail .= 'Non';
    $html_detail .= "</strong><br />
Ouverture : <strong>Le " . date('d/m/Y à H:i', $time_ouverture) . "</strong>";
    if($tarif_estimatif)
        $html_detail .= '<br />Tarif estimatif : <strong>'.$tarif_estimatif .'€</strong>';


    $pattern = new Pattern('p', 'h', $pattern_value);
    $pattern->disable();

    $md = new Modal('Schéma de dévérouillage', 'pattern');
    $md->setContent('<p class="text-center">'.$pattern.'</p>');
    $md->openLink('Cliquer pour voir le schéma');




    if($pattern_value)
        $html_detail .= '<br />Schéma dévérouillage : '.$md->getOpenHtml().$md->getModalHtml();

    if(right('inter', 13) AND !$cloture)
        if(is_object($modify_details_modal))
            $details_widgetBox->setContent($modify_details_modal->getModalHtml().$html_detail);
        else
            $details_widgetBox->setContent($html_detail);
    else
        $details_widgetBox->setContent($html_detail);
}


//commandes
if($type_atelier_rdv == 1 and right('commandes', 1))
{
    $commandes_widgetbox = new WidgetBox('Commandes', 4);
    $commandes_widgetbox->scrollable();

    if(right('commandes', 2) ANd !$disabled)
    {


        $modal_add_order = new modalAjax('intervention_informatique', 'add_order');
        $modal_add_order->oppenButton(new Font('plus'), 'btn-primary');

        $commandes_widgetbox->addToolbarButtons($modal_add_order->getModalOpen());
    }


    //les commandes effectuées
    $table_cdes = new HtmlTable('', '', array('style'=>'width: 100%'));

    foreach ($tab_cdes as $cde)
    {
        $table_cdes->addRow();
        $td1 = '
        <strong>BdC : </strong>'.$cde['bdc'].' - '.$cde['date_cde'].'<br />
        <strong>Fournisseur : </strong>'. $cde['fournisseur'].' ( '.$cde['num_cde'].' )<br />
        <strong>Réception ';
        if(!$cde['cde_recue'])
            $td1 .= 'estimée ';
        $td1 .= ': </strong>'.$cde['date_reception'].'<br />';

        if($cde['colis'] != '')
            $td1 .= '<strong>Numéro de colis : </strong>'.$cde['colis'];

        $table_cdes->addCell($td1);

        if(!$disabled) //boutons sur les commandes
        {
            if(right('commandes', 5))
            {
                $dereception = '';
                $reception = '';
                if ($cde['cde_recue']) //Déreception
                {
                    $dereception = new Button(new Font('check'), '?dereception_cde=' . $cde['id'], 'Supprimer la réception');
                    $dereception->setClasse('btn-primary btn-xs');
                    $dereception->setFullWidth();

                }
                else
                {
                    $reception = new Button(new Font('check'), '?reception_cde=' . $cde['id'], 'Commande reçue');
                    $reception->setClasse('btn-xs');
                    $reception->setFullWidth();

                }
            }

            if(right('commandes', 3)) //Modifier la commande en cours
            {


                $modal_edit_order = new modalAjax('intervention_informatique', 'edit_order');
                $modal_edit_order->settings(array('id_order' => $cde['id']));
                $modal_edit_order->oppenButton(new Font('pencil'), 'btn-xs', true);

                //$modal_edit_order->setContent($line);
            }

            if(right('commandes', 4)) //Supprimer la commande en cours
            {
                $suppr = new Button(new Font('remove'), 'javascript:void();', 'Supprimer la commande');
                $suppr->setClasse('btn-danger btn-xs');
                $suppr->onClick('if(confirm(\'Voulez-vous supprimer cette commande ?\'))window.location.href = \'?suppr_cde='.$cde['id'].'\';');
                $suppr->setFullWidth();
            }
        }

        if(is_object($modal_edit_order))
            $table_cdes->addCell($dereception.$reception.$modal_edit_order->getModalOpen().$suppr);
        else
            $table_cdes->addCell($dereception.$reception.$suppr);

        if(is_object($modal_edit_order))
            $table_cdes->addCell($modal_edit_order->getModalHtml());



        $table_cdes->addRow();
        $table_cdes->addCell('<hr />');


    }

    if(is_object($modal_add_order))
        $commandes_widgetbox->setContent($modal_add_order->getModalHtml().$table_cdes);
    else
        $commandes_widgetbox->setContent($table_cdes);

}

//Sous traitance

if($type_atelier_rdv == 1 and right('soustraitances', 1))
{
    $sstraitance_widgetbox = new WidgetBox('Sous-Traitances', 4);
    $sstraitance_widgetbox->scrollable();

    if(right('soustraitances', 2) AND !$disabled)
    {

        $modal_add_sst = new modalAjax('intervention_informatique', 'add_sst');
        $modal_add_sst->oppenButton(new Font('plus'), 'btn-primary');

        $sstraitance_widgetbox->addToolbarButtons($modal_add_sst->getModalOpen());

    }


    //Toutes les sous-traitances
    $table_ssts = new HtmlTable('', '', array('style'=>'width: 100%'));
    $tab_modal_sst =  Array();
    foreach ($tab_ssts as $sst)
    {
        $table_ssts->addRow();
        $td1 = '<strong>Devis : </strong>'.$sst['devis'].'<br />
                <strong>Sous-traitant : </strong>'.$sst['nom'].' (Commande N°'.$sst['num_cde'].')<br />
                <strong>Date d\'envoie : </strong>'. $sst['date_sst'].'<br />
                <strong>Retour ';

        if(!$sst['retour'])
            $td1 .= 'estimé ';

        $td1 .= ': </strong>'.$sst['date_retour'].'<br />
        ';
        if($sst['colis_alle'] != '')
            $td1 .= '<strong>Numéro de colis allée : </strong>'.$sst['colis_alle'].'<br />';
        if($sst['colis_retour'] != '')
            $td1 .= '<strong>Numéro de colis retour : </strong>'.$sst['colis_retour'].'<br />';

        $table_ssts->addCell($td1);

        if(!$disabled) //boutons sur les sous traitances
        {
            $deretour = '';
            $retour = '';
            if ($sst['retour'] AND right('soustraitances', 5)) //Déretour
            {
                $deretour = new Button(new Font('check'), '?deretour_sst=' . $sst['id'], 'Supprimer le retour');
                $deretour->setClasse('btn-primary btn-xs');
                $deretour->setFullWidth();
            }
            else
            {
                if (right('soustraitances', 5)) //Retour
                {
                    $retour = new Button(new Font('check'), '?retour_sst=' . $sst['id'], 'Retour de sous-traitance');
                    $retour->setClasse('btn-xs');
                    $retour->setFullWidth();
                }
            }

            if(right('soustraitances', 3)) //Modifier la sous-traitance en cours
            {
                $modal_edit_sst = new modalAjax('intervention_informatique', 'edit_sst');
                $modal_edit_sst->settings(array('id_sst' => $sst['id']));
                $modal_edit_sst->oppenButton(new Font('pencil'), 'btn-xs', true);
            }

            if(right('soustraitances', 4)) //Supprimer la commande en cours
            {
                $suppr = new Button(new Font('remove'), 'javascript:void();', 'Supprimer la sous-traitance');
                $suppr->setClasse('btn-danger btn-xs');
                $suppr->onClick('if(confirm(\'Voulez-vous supprimer cette sous-traitance ?\'))window.location.href = \'?suppr_sst='.$sst['id'].'\';');
                $suppr->setFullWidth();
            }
        }

        if(is_object($modal_edit_sst))
            $table_ssts->addCell($deretour.$retour.$modal_edit_sst->getModalOpen().$suppr);
        else
            $table_ssts->addCell($deretour.$retour.$suppr);

        if(is_object($modal_edit_sst))
            $tab_modal_sst[] = $modal_edit_sst->getModalHtml();

        $table_ssts->addRow();
        $table_ssts->addCell('<hr />');

    }

    if(is_object($modal_add_sst))
        $sstraitance_widgetbox->setContent($modal_add_sst->getModalHtml().$table_ssts);
    else
        $sstraitance_widgetbox->setContent($table_ssts);
}




//Tous les boutons d'action
$actions = new WidgetBox('Actions', 3, 'md');


if(right('inter', 3))
{
    $save = new Button('Sauvegarder', 'javascript:void();');
    $save->onClick('save();');

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
            if(!right('inter', 17))
                $pec->disable();
            $pec->setClasse('btn-danger');
            $save->setClasse('btn-primary');
            $cloture_btn = new Button('Cloturer', 'javascript:void();');
            if($interdit_cloture)
            {
                $interdit_cloture_modale1 = new Modal('Vous ne pouvez pas clôturer cette intervention', 'modale_interdit_cloture');

                $message_error = new Danger('Vous ne pouvez pas cloturer cette intervention car une commande ou une sous-traitance est encore en cours.', false);
                $interdit_cloture_modale1->setContent($message_error);
                $interdit_cloture_modale1->noButton();



                //echo $interdit_cloture_modale->openNow();
                $cloture_btn->onClick("$('#modale_interdit_cloture').modal('show');");
            }
            else
                $cloture_btn->onClick("cloturer(".$solde_pm.")");







            $cloture_btn->setClasse('btn-primary');
            $cloture_btn->setFullWidth();
        }
        $pec->setFullWidth();


    }

    $save->setFullWidth();
}


if(!$cloture AND $type_atelier_rdv == 1)
{
    $ticket_client = new Button('Ticket client', 'javascript:void();');
    $ticket_client->onClick("window.open('./ticket-$id_inter', 'inscr','width=700,height=680,left=100,top=10,scrollbars=yes')");
    $ticket_client->setClasse('btn-warning');
    $ticket_client->setFullWidth();

    $ticket_machine = new Button('Ticket machine', 'javascript:void();');
    $ticket_machine->onClick("window.open('./scotch-$id_inter', 'inscr','width=700,height=680,left=100,top=10,scrollbars=yes');");
    $ticket_machine->setClasse('btn-warning');
    $ticket_machine->setFullWidth();

    $fiche_entree = new Button('Fiche Entrée', 'javascript:void();');
    $fiche_entree->onClick("window.open('./imprimer-$id_inter-e', 'inscr','width=700,height=680,left=100,top=10,scrollbars=yes');");
    $fiche_entree->setClasse('btn-warning');
    $fiche_entree->setFullWidth();
}
elseif ($type_atelier_rdv == 1)
{
    $fiche_sortie = new Button('Fiche sortie', 'javascript:void();');
    $fiche_sortie->onClick("window.open('./imprimer-$id_inter-s', 'inscr','width=700,height=680,left=100,top=10,scrollbars=yes');");
    $fiche_sortie->setClasse('btn-warning');
    $fiche_sortie->setFullWidth();
}

if($type_atelier_rdv == 2)
{
    $fiche_rdv = new Button('Fiche Rendez-vous', 'javascript:void();');
    $fiche_rdv->onClick("window.open('./inter-site-$id_inter', 'inscr','width=700,height=680,left=100,top=10,scrollbars=yes');");
    $fiche_rdv->setClasse('btn-warning');
    $fiche_rdv->setFullWidth();

    $supprimer_rdv = "
    <script>
        function suppr_rdv()
        {
            if(confirm(\"Êtes-vous sur de vouloir supprimer ce rendez-vous ? L'intervention ne sera pas supprimée mais juste clôturée et le rendez-vous sera supprimé du calendrier. Pour annuler cette action, déclôturer l'intervention.\"))
                document.location.href = '?action=suppr_rdv';

        }
        </script>
    ";

    $supprimer_rdv_btn = new Button('Supprimer le rendez-vous', 'javascript:void();');
    $supprimer_rdv_btn->onClick("suppr_rdv();");
    if($pris_en_charge AND !$cloture)
        $supprimer_rdv_btn->setClasse('btn-danger');
    else
        $supprimer_rdv_btn->disable();

    $supprimer_rdv_btn->setFullWidth();

}

if(AccesActivableModul('sellsy')) {
    $facturer_diag_sellsy = new Button(new Font('eur').' Facturer un diagnostic', './moduls/sellsy/redirect/diagnostic.php?id_inter='.$id_inter);
    if(1) //!$cloture)
        $facturer_diag_sellsy->setClasse('btn-primary');
    else
        $facturer_diag_sellsy->disable();
    $facturer_diag_sellsy->setTargetBlank();
    $facturer_diag_sellsy->setFullWidth();
}
$actions->setContent($pec.' '.$save.' '.$cloture_btn.' '.$ticket_client.' '.$ticket_machine.' '.$fiche_entree.' '.$fiche_sortie.' '.$fiche_rdv.' '.$supprimer_rdv.' '.$supprimer_rdv_btn.' '.$facturer_diag_sellsy, false);

$inter = new Col(9, 'md');

$wpanne = new WidgetBox('Panne constatée, travaux demandés', 6);

$type = new Hidden('type', 'type');

$tpanne = new Textarea('panne');
$tpanne->setRows(7);
$tpanne->setStyle('width : 100%');
$tpanne->setValue($panne);
$tpanne->elastic();
if($disabled)
    $tpanne->disable();
$wpanne->setContent($tpanne, false);


$wmessage_interne = new WidgetBox('Message Interne', 6);

$tmessage_interne = new Textarea('message_interne');
$tmessage_interne->setRows(7);
$tmessage_interne->setStyle('width : 100%;');
$tmessage_interne->setValue($message_interne);
$tmessage_interne->elastic();
if($disabled)
    $tmessage_interne->disable();
$wmessage_interne->setContent($tmessage_interne, false);


$wresolution = new WidgetBox('Resolution', 6);


if($link)
{
    $liste_reso = new Select('reso');
    $liste_reso->onChange("document.getElementById('resolution').value=this.value");
    $liste_reso->withSearch();
    $liste_reso->addOption('', '');
    foreach ($tab_rapports_types as $rapport)
        $liste_reso->addOption($rapport['texte'], $rapport['titre']);
    $liste_reso = $liste_reso.'<br /><br />';

}

$tresolution = new Textarea('resolution', 'resolution');
$tresolution->setStyle('width : 100%;');
$tresolution->setRows(7);
$tresolution->elastic();
if($disabled)
    $tresolution->disable();
$tresolution->setValue($resolution);



$wresolution->setContent($liste_reso.$tresolution, false);


$wmessage_client = new WidgetBox('Message à destination du client', 6);

if($link)
{
    $liste_message = new Select('mc');
    $liste_message->onChange('document.getElementById(\'message_client\').value+=this.value+\'\n\n\'');
    $liste_message->withSearch();
    $liste_message->addOption('', '');
    foreach ($tab_commentaires_types as $comm)
        $liste_message->addOption($comm['texte'], $comm['titre']);
    $liste_message = $liste_message.'<br /><br />';

}

$tmessage_client = new Textarea('message_client', 'message_client');
$tmessage_client->setStyle('width : 100%;');
$tmessage_client->elastic();
$tmessage_client->setRows(7);
if($disabled)
    $tmessage_client->disable();
$tmessage_client->setValue($message_client);

$wmessage_client->setContent($liste_message.$tmessage_client, false);


$wmateriel_ajoute = new WidgetBox('Materiel ajouté / remplacé', 6);

if($link)
{
    $liste_mat = new Select('mc');
    $liste_mat->onChange('document.getElementById(\'materiel_ajoute\').value+=\'-\'+this.value+\'\n\'');
    $liste_mat->withSearch();
    $liste_mat->addOption('', '');
    foreach ($tab_materiels_ajoutes_types as $mat)
        $liste_mat->addOption($mat['texte'], $mat['titre']);
    $liste_mat = $liste_mat.'<br /><br />';

}

$tmateriel_ajoute = new Textarea('materiel_ajoute');
$tmateriel_ajoute->setStyle('width : 100%;');
$tmateriel_ajoute->setRows(7);
$tmateriel_ajoute->elastic();
$tmateriel_ajoute->setValue($materiel_ajoute);
if($disabled)
    $tmateriel_ajoute->disable();

$wmateriel_ajoute->setContent($liste_mat.$tmateriel_ajoute, false);


$wmatos = new WidgetBox('Matériel déposé', 6);

$tmatos = new Textarea('materiel_depose');
$tmatos->setStyle('width : 100%;');
$tmatos->setRows(7);
$tmatos->elastic();
$tmatos->setValue($matos);
if($disabled)
    $tmatos->disable();

$wmatos->setContent($tmatos, false);


$wprestations = new WidgetBox('Prestations effectuées', 12);

$table_presta = new HtmlTable();
$table_presta->addTSection('thead');
$table_presta->addRow();
$table_presta->addCell('Designation', '', 'thead');
$table_presta->addCell('Durée', '', 'thead');
$table_presta->addTSection('tbody');

foreach($tab_prestations_effectuees as $presta)
{
    $table_presta->addRow();
    $table_presta->addCell($presta['designation']);
    $table_presta->addCell($presta['duree'].' h');
}

$wprestations->setContent($table_presta, false);

if($link)
{

    $script = '<script language="javascript">
                function duree_defaut(valeur, id)
                {
                        var tab = valeur.split(\'_\');
                        document.getElementById(id).value=tab[1];
                }
            </script>';


    $all_list = '';

    $table_presta = new HtmlTable();
    $table_presta->addTSection('thead');
    $table_presta->addRow();
    $table_presta->addCell('Designation', '', 'thead');
    $table_presta->addCell('Durée', '', 'thead');
    $table_presta->addTSection('tbody');

    for ($i = 0; $i < 10; $i++)
    {
        $table_presta->addRow();
        $prestas = new Select('presta_' . $i);
        $prestas->addOption('0_0', '--');
        $prestas->withSearch();
        $prestas->onChange('duree_defaut(this.value, \'duree_' . $i . '\')');

        foreach ($tab_prestations AS $presta) {
            if ($presta['id'] == $tab_prestations_effectuees[$i]['id'])
                $selected = true;
            else
                $selected = false;
            $prestas->addOption($presta['id'] . '_' . $presta['duree'], $presta['designation'] . ' (' . $presta['duree'] . 'h)', $selected);
        }

        $prestas_duree = new Text('duree_' . $i, 'duree_' . $i);
        $prestas_duree->setValue($tab_prestations_effectuees[$i]['duree']);

        $table_presta->addCell($prestas);
        $table_presta->addCell($prestas_duree);
        $all_list .= $prestas . ' '.$prestas_duree;
    }


    $wprestations->setContent($script . $table_presta, false);

}

$l1 = New Row($type.$wpanne.$wmessage_interne);
$l2 = new Row($wresolution.$wmessage_client);

if($type_atelier_rdv == '1')
    $l3 = new Row($wmateriel_ajoute.$wmatos);
else
    $l3 = new Row($wmateriel_ajoute);


$inter->setContent($l1.$l2.$l3, false);


//RDV



if($type_atelier_rdv == '2')
{
    $tab_months = Array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
    $wrdv = new WidgetBox('Rendez-vous', 2);
    $hidden_busy = new Hidden('inter_busy');
    $hidden_busy->setValue(0);
    if($rdv_debut)
    {
        $modal_delete_date = '';
        if(!$cloture) {
            $modal_delete_date = new Modal('Supprimer le créneau horaire', 'delete_date_modal');

            $delete_date_form = new FormLayout('Choisir la priorité du rendez-vous');
            $delete_date_form->setFormControls('delete_date_form');

            $priority = new Select('priorite');
            $priority->setSelected(0);
            $priority->addOption('-1', 'Basse');
            $priority->addOption('0', 'Normale');
            $priority->addOption('1', 'Urgente');

            $delete_date_form->addLine('Priorité', $priority);

            $modal_delete_date->setContent($delete_date_form);
            $modal_delete_date->openLink(new Font('calendar-times-o').' Supprimer le créneau horaire');
            //$modal_delete_date->setOnclickButton('test', 'alert("ok");');
            $modal_delete_date->setSubmitButton('delete_date_form', 'Supprimer le créneau horaire');
        }


        if(is_object($modal_delete_date))
            $wrdv->setContent($hidden_busy . '<h3>' . date('d', $rdv_debut) . ' ' . $tab_months[date('n', $rdv_debut) - 1] . ' ' . date('Y', $rdv_debut) . '</h3><h3>' . date('H\hi', $rdv_debut) . ' &rarr; ' . date('H\hi', $rdv_fin) . '</h3>'.$modal_delete_date->getOpenHtml(), false);
        else
            $wrdv->setContent($hidden_busy . '<h3>' . date('d', $rdv_debut) . ' ' . $tab_months[date('n', $rdv_debut) - 1] . ' ' . date('Y', $rdv_debut) . '</h3><h3>' . date('H\hi', $rdv_debut) . ' &rarr; ' . date('H\hi', $rdv_fin) . '</h3>', false);
    }
    else
    {
        if($priorite == -1)
            $color = 'default';
        if($priorite == 0)
            $color = 'primary';
        if($priorite == 1)
            $color = 'danger';

        $rdv_form = new FormLayout('Modifier la date d\'un rendez-vous');
        $rdv_form->setFormControls('form_rdv_edit');


        $ecart = new Hidden('ecart', 'ecart');
        $ecart->setValue(3600);

        $debut = new Text('date_heure', 'date_heure');
        $debut->dataMask('99/99/9999 99:99');
        $debut->dateTimePicker('set_end();');
        $debut->setValue(date('d/m/Y').' 10:00');
        $debut->onKeyUp("set_end();");
        $rdv_form->addLine('Début', $ecart.$debut, false, 'date_debut_rdv');

        $fin = new Text('date_heure_fin', 'date_heure_fin');
        $fin->dataMask('99/99/9999 99:99');
        $fin->dateTimePicker('set_ecart();');
        $fin->setValue(date('d/m/Y').' 11:00');
        $fin->onKeyUp("set_ecart();");
        $rdv_form->addLine('Fin', $fin, false, 'date_fin_rdv');

        $modal_rdv = new Modal('Modifier la date d\'un rendez-vous', 'edit_rdv');
        $modal_rdv->openButton(new Font('calendar').' Rendez-vous à définir', 'btn-'.$color.' btn-block');
        $modal_rdv->setContent($rdv_form);
        $modal_rdv->setSubmitButton('form_rdv_edit');
        $wrdv->setContent($hidden_busy.$modal_rdv->getOpenHtml() , false);
        //$wrdv->setContent($hidden_busy . '<h3><a href="./rdv">Rendez-vous à définir</a></h3>', false);
    }

    $wmap = new WidgetBox('Carte', 3);
    $wmap->setContent($gmap->getGoogleMap(), false);

    $iti = new WidgetBox('Itineraire', 4);
    $iti->setContent('<div id="route" style=" height: 200px;  overflow-y: auto;"></div>', false);

    $ligne0 = new Row($wrdv.$client.$wmap.$iti);
}


$staffs_pris_en_charge = new DatabaseWorker('prise_en_charge');
$staffs_pris_en_charge->setWidget('Tableau des techniciens en charge', 3);
$staffs_pris_en_charge->noDatatable();
$staffs_pris_en_charge->displayedFields(Array('id_staff', 'time'));
$staffs_pris_en_charge->addDateTimeFields('time');
$staffs_pris_en_charge->labelsDisplayedFields(Array('Techniciens', 'Depuis le'));
$staffs_pris_en_charge->addWhere("id_intervention=".$id_inter);
$staffs_pris_en_charge->innerJoin('staffs', 'id_staff', 'prenom');
if(right('inter', 19))
    $staffs_pris_en_charge->activeDelete('i'.$id_inter);
$tab_hiddens = Array('id_intervention' => $id_inter);
if(right('inter', 18))
    $staffs_pris_en_charge->activateAdd('i'.$id_inter, '', '', '', $tab_hiddens);
$staffs_pris_en_charge->noPrintModals();

if($type_atelier_rdv == 1 AND $cloture)
{
    $restitution = new DatabaseWorker('interventions');
    $restitution->setWidget('Restitution matériel', 3);
    $restitution->noDatatable();
    $restitution->displayedFields(Array('id_staff_restitution', 'time_restitution', 'paye', 'note'));
    $restitution->labelsDisplayedFields(Array('Technicien', 'Date de restitution', 'Payé ?', 'Notes'));
    $restitution->addWhere("id_inter=".$id_inter);
    $restitution->addIfValue('time_restitution', '0', 'Non restituée encore');
    $restitution->innerJoin('staffs', 'id_staff_restitution', 'prenom');
    $restitution->addDateFields('time_restitution');
    $restitution->noModifyFields('id_staff_restitution');
    $tab_hiddens = Array('id_staff_restitution' => $_SESSION['id']);
    $restitution->activeModify('i'.$id_inter, '', $tab_hiddens);
    $ligne1 = new Row($inter.$actions.$staffs_pris_en_charge.$restitution);
}
else
    $ligne1 = new Row($inter.$actions.$staffs_pris_en_charge);


$ligne2 = new Row($wprestations);

$form = new Form('entry');

$pm_add_form = new Hidden('pm_add_form');
$pm_desi_form = new Hidden('pm_desi_form');
$pm_duree_form = new Hidden('pm_duree_form');
$pm_force_form = new Hidden('pm_force_form');

$form->setContent($pm_add_form.$pm_desi_form.$pm_duree_form.$pm_force_form.$ligne0.$ligne1.$ligne2);



$tabs->addPane('Intervention', $details_widgetBox.$commandes_widgetbox.$sstraitance_widgetbox.$form.$staffs_pris_en_charge->printAllModals(), 'inter');






if(1) //$type_atelier_rdv == 1)
{



    //Boutons SMS et Mail

    $col = new WidgetBox('Actions', 4);

    //$sms = new Modal('sms', 'sms');
    //$sms->buttonValue('Envoyer un SMS');
    //$sms->setContent('En attente');

   // $mail = new Modal('mail', 'mail');
    //$/mail->buttonValue('Envoyer un mail');
    //$mail->setContent('En attente');

    //$col->setContent($sms.'<br />'.$mail, false);


    if(!$contact_ref)
        $contact_ref = $id_c;

    if(AccesActivableModul('mail')) {
        $mail = new Mail_Form('Envoyer un mail', 'mail_modal', $prefix.$id_inter);
        $mail->openButton(new Font('envelope').' Envoyer un mail', 'btn-primary btn-block');
        $mail->setSubmitButton('mail_form', 'Envoyer le mail');
        $mail->displayListe();
        $mail->addListDest($liste_mails, $contact_ref);
        $mail->putContent('mail_form');
    }

    if(AccesActivableModul('sms')) {



        $sms = new SMS_form('Envoyer un SMS', 'sms_modal', $prefix.$id_inter);
        $sms->openButton(new Font('commenting').' Envoyer un SMS', 'btn-primary btn-block');
        $sms->setSubmitButton('sms_form', 'Envoyer le SMS');
        //$sms->addDest($cpy_tel2);
        $sms->displayListe();

        $sms->addListDest($liste_smss, $contact_ref);
        $sms->putContent('sms_form');
    }


    $col->setContent($mail.'<br />'.$sms, false);



    $histo_sms = new DatabaseWorker('sms_histo');
    $histo_sms->setWidget('Historique des SMS envoyés pour cette intervention');
    $histo_sms->displayedFields(Array('message', 'timestamp'));
    $histo_sms->labelsDisplayedFields(Array('Messages', 'Envoyé'));
    $histo_sms->addDateTimeFields('timestamp');
    $histo_sms->setOrderBy('timestamp', 'DESC');
    $histo_sms->addWhere('id_inter="'.$id_inter.'"');



}



$tabs->addPane('Client', $client.' '.$col.$form_mail.$histo_sms, 'customer');
/*
$tab_files = new HtmlTable();
$tab_files->addTSection('thead');
$tab_files->addRow();
$tab_files->addCell('Nom du fichier', '', 'thead');
$tab_files->addCell('Action', '', 'thead');
$tab_files->addTSection('tbody');

$tab_files->addRow();
$upload = new File('fichier');

$form_upload = new Form('form_upload');
$form_upload->file();
$form_upload->setContent($upload);
$tab_files->addCell($form_upload);
$btn_submit_upload = new Button('Télécharger', 'javascript:void()');
$btn_submit_upload->setClasse('btn-primary');
$btn_submit_upload->onClick("$('#form_upload').submit();");
$tab_files->addCell($btn_submit_upload);

if(is_array($files))
{
    foreach($files AS $file)
    {
        if($file != '.' AND $file != '..')
        {
            $tab_files->addRow();
            $tab_files->addCell('<a href="./download.php?fo='.$id_inter.'&fi='.$file.'&ty=i">'.$file.'</a>');
            $tab_files->addCell('en cours');
        }
    }
}
*/



$elfinder = new elFinderPerso();

/*
 * Création dossier "public"
 */


$dir = './../files_managy/'; //.sha1($_SESSION['cal']).'/i/'.$id_inter.'/public';

if(!is_dir($dir.sha1($_SESSION['cal'])))
    mkdir($dir.sha1($_SESSION['cal']));

if(!is_dir($dir.sha1($_SESSION['cal']).'/i'))
    mkdir($dir.sha1($_SESSION['cal']).'/i');

if(!is_dir($dir.sha1($_SESSION['cal']).'/i/'.$id_inter))
    mkdir($dir.sha1($_SESSION['cal']).'/i/'.$id_inter);

if(!is_dir($dir.sha1($_SESSION['cal']).'/i/'.$id_inter.'/public'))
    mkdir($dir.sha1($_SESSION['cal']).'/i/'.$id_inter.'/public');

$elfinder->addPath('i/'.$id_inter, 'Intervention '.$id_inter);



$tabs->addPane('Fichiers', $elfinder, 'files');




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

        $texte = preg_replace('#http://[a-z0-9._/-?-{}&%\#]+#i', '<a href="$0" target="_blank">$0</a>', $texte);
        $texte = preg_replace('#https://[a-z0-9._/-?-{}&%\#]+#i', '<a href="$0" target="_blank">$0</a>', $texte);

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


/*
 * Logs
 */
if(right('logs', 1))
{
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


if($intervention_precedente_pec)
{
    $btn_left = new Button(new Font('chevron-left'), './i'.$intervention_precedente_pec, 'Intervention précédente, prise en charge, non clôturée ('.$intervention_precedente_pec.')');
}
else
{
    $btn_left = new Button(new Font('chevron-left'), 'javascript:void();', 'Aucune intervention précédente, prise en charge, non clôturée');
    $btn_left->disable();
}
$btn_left->setClasse('btn-xs btn-info');

if($intervention_suivante_pec)
{
    $btn_right = new Button(new Font('chevron-right'), './i'.$intervention_suivante_pec, 'Intervention suivante, prise en charge, non clôturée ('.$intervention_suivante_pec.')');
}
else
{
    $btn_right = new Button(new Font('chevron-right'), 'javascript:void();', 'Aucune intervention suivante, prise en charge, non clôturée');
    $btn_right->disable();
}
$btn_right->setClasse('btn-xs btn-info');

$stats_content = '<li>'.$btn_left.'<img src="./templates/v4/img/icons/packs/fugue/16x16/blue-document-attribute-c.png" width="20" />'.$btn_right.'</li>';



if($intervention_precedente)
{
    $btn_left = new Button(new Font('chevron-left'), './i'.$intervention_precedente, 'Intervention précédente ('.$intervention_precedente.')');
}
else
{
    $btn_left = new Button(new Font('chevron-left'), 'javascript:void();', 'Aucune intervention précédente');
    $btn_left->disable();
}
$btn_left->setClasse('btn-xs btn-info');

if($intervention_suivante)
{
    $btn_right = new Button(new Font('chevron-right'), './i'.$intervention_suivante, 'Intervention suivante ('.$intervention_suivante.')');
}
else
{
    $btn_right = new Button(new Font('chevron-right'), 'javascript:void();', 'Aucune intervention suivante');
    $btn_right->disable();
}
$btn_right->setClasse('btn-xs btn-info');

$stats_content .= '<li>'.$btn_left.'<img src="./templates/v4/img/icons/packs/fugue/16x16/blue-document-attribute-i.png" width="20" />'.$btn_right.'</li>';

//print_r($_POST);

$message_error = new Danger('Vous ne pouvez pas cloturer cette intervention car son statut est occupé.', false);

$interdit_cloture_modale2 = new Modal('Vous ne pouvez pas clôturer cette intervention', 'modale_interdit_cloture2');
$interdit_cloture_modale2->setContent($message_error);
$interdit_cloture_modale2->noButton();
echo $interdit_cloture_modale2->getModalHtml();
if(is_object($modal_delete_date))
    echo $modal_delete_date->getModalHtml();

if(is_array($tab_modal_sst))
    foreach ($tab_modal_sst AS $mod)
        echo $mod;
if(is_object($interdit_cloture_modale1))
    echo $interdit_cloture_modale1->getModalHtml();
if(is_object($modal_rdv))
    echo $modal_rdv->getModalHtml();
?>
