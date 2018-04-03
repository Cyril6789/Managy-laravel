<?php session_start();

$soustraitance_widget = new WidgetBox('Tableau des sous-traitances en cours');

$table_sst = new HtmlTable('', 'table table-bordered table-hover table-responsive table-datatable');
$table_sst->addTSection('thead');
$table_sst->addRow();
$table_sst->addCell('Devis', '', 'thead');
$table_sst->addCell('Nom du sous-traitance', '', 'thead');
$table_sst->addCell('Intervention / Client', '', 'thead');
$table_sst->addCell('N° de colis', '', 'thead');
$table_sst->addCell('Date de retour estimée', '', 'thead');
$table_sst->addCell('Action', '', 'thead');

$table_sst->addTSection('tbody');

foreach ($tab_ssts AS $sst)
{
    $table_sst->addRow();
    $table_sst->addCell('Devis : '.$sst['devis']);
    $table_sst->addCell($sst['nom_st'].'<br />Envoyé le : '.$sst['date_sst'].'<br />N° Commande : '.$sst['num_cde']);
    $table_sst->addCell('<a href="./i'.$sst['id_inter'].'">Intervention N°'.$sst['id_inter'].'</a><br />'.$sst['titre'].' '.$sst['nom'].' '.$sst['prenom']);

    $colis_a = new SuiviColis($sst['colis_alle']);
    $colis_r = new SuiviColis($sst['colis_retour']);

    $table_sst->addCell('Allée : '.$sst['colis_alle'].'<br />'.$colis_a->getMessage(). ' ('. $colis_a->getDate().' : '. $colis_a->getLieu().')<br />Retour : '.$sst['colis_retour'].'<br />'.$colis_r->getMessage(). ' ('. $colis_r->getDate().' : '. $colis_r->getLieu().')');

    if($sst['date_retour_t'] < time())
        $rouge = 'style="color: #AE4F4F; font-weight: bold;"';
    else
        $rouge = '';
    $table_sst->addCell('<span '.$rouge.'>'.$sst['date_retour'].'</span>');


    if (right('soustraitances', 5)) //Retour
    {
        $retour = new Button(new Font('check'), '?retour_sst=' . $sst['id'].'&id_inter='.$sst['id_inter'], 'Retour de sous-traitance');
        $retour->setClasse('btn-xs');
        $retour->setFullWidth();
    }


    if(right('soustraitances', 3)) //Modifier la sous-traitance en cours
    {
        $modal_edit_sst = new Modal('Modifier la sous-traitance', 'sst_modal_edit_'.$sst['id']);
        $modal_edit_sst->openButton(new Font('pencil'), 'btn-xs', true, 'Modfier la sous-traitance');
        $modal_edit_sst->setSubmitButton('add_sst_form_'.$sst['id'], 'Modifier la sous-traitance');

        $sst_form = New FormLayout('Saisie');
        $sst_form->setFormControls('add_sst_form_'.$sst['id']);
        $sst_form->setWidth(5, 7);

        $id_sst = new Hidden('id_sst');
        $id_sst->setValue($sst['id']);

        $id_inter = new Hidden('id_inter');
        $id_inter->setValue($sst['id_inter']);

        $devis = new Text('devis');
        $devis->setValue($sst['devis']);
        $sst_form->addLine('Devis', $id_sst.$id_inter.$devis);

        $sous_traitant = new Text('nom');
        $sous_traitant->setValue($sst['nom_st']);
        $sst_form->addLine('Sous-traitant', $sous_traitant);

        $num_cde_sst = new Text('num_sst');
        $num_cde_sst->setValue($sst['num_cde']);
        $sst_form->addLine('Numéro de commande sous-traitant', $num_cde_sst);

        $colis_alle = new Text('colis_alle');
        $colis_alle->setValue($sst['colis_alle']);
        $sst_form->addLine('Numéro colis aller', $colis_alle);

        $colis_retour = new Text('colis_retour');
        $colis_retour->setValue($sst['colis_retour']);
        $sst_form->addLine('Numéro colis retour', $colis_retour);

        $date_envoie = new Text('date_sst');
        $date_envoie->datePicker();
        $date_envoie->setValue($sst['date_sst']);
        $sst_form->addLine('Date d\'envoie', $date_envoie);

        $date_retour = new Text('date_retour');
        $date_retour->datePicker();
        $date_retour->setValue($sst['date_retour']);
        $sst_form->addLine('Date de retour estimé', $date_retour);

        $modal_edit_sst->setContent($sst_form);

        $line = new Row($sst_form);

        $modal_edit_sst->setContent($line);
    }

    if(right('soustraitances', 4)) //Supprimer la commande en cours
    {
        $suppr = new Button(new Font('remove'), 'javascript:void();', 'Supprimer la sous-traitance');
        $suppr->setClasse('btn-danger btn-xs');
        $suppr->onClick('if(confirm(\'Voulez-vous supprimer cette sous-traitance ?\'))window.location.href = \'?suppr_sst='.$sst['id'].'&id_inter='.$sst['id_inter'].'\';');
        $suppr->setFullWidth();
    }


    $table_sst->addCell($retour.$modal_edit_sst->getOpenHtml().$suppr.$modal_edit_sst->getModalHtml());
}
$soustraitance_widget->setContent($table_sst);

$row = new Row($soustraitance_widget);
echo $row;
?>
