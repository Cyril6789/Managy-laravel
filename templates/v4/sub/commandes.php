<?php session_start();


$orders_widget = new WidgetBox('Tableau des commandes en cours');

$table_orders = new HtmlTable('', 'table table-bordered table-hover table-responsive table-datatable');
$table_orders->addTSection('thead');
$table_orders->addRow();
$table_orders->addCell('Bon de commande', '', 'thead');
$table_orders->addCell('Intervention / Client', '', 'thead');
$table_orders->addCell('Fournisseur', '', 'thead');
$table_orders->addCell('N° de Colis', '', 'thead');
$table_orders->addCell('Date de réception estimée', '', 'thead');
$table_orders->addCell('Action', '', 'thead');

$table_orders->addTSection('tbody');

foreach ($tab_cdes AS $cde)
{
    $table_orders->addRow();

    $table_orders->addCell($cde['bdc'].'<br />Commandé le : '.$cde['date_cde']);
    $table_orders->addCell('<a href="./i'.$cde['id_inter'].'">Intervention N°'.$cde['id_inter'].'</a><br />'.$cde['titre'].' '.$cde['nom'].' '.$cde['prenom']);
    $table_orders->addCell($cde['fournisseur'].'<br />N° de commande fournisseur : '.$cde['num_cde']);

    $colis = new SuiviColis($cde['colis']);
    $table_orders->addCell($cde['colis'].'<br />'.$colis->getMessage(). ' ('. $colis->getDate().' : '. $colis->getLieu().')');

    if($cde['date_reception_t'] < time())
        $rouge = 'style="color: #AE4F4F; font-weight: bold;"';
    else
        $rouge = '';
    $table_orders->addCell('<span '.$rouge.'>'.$cde['date_reception'].'</span>');

    if (right('commandes', 5)) //Réception
        $reception = new Button(new Font('check'), '?reception_cde=' . $cde['id'].'&id_inter='.$cde['id_inter'], 'Commande reçue');
    else
    {
        $reception = new Button(new Font('check'), 'javascript:void();', 'Commande reçue');
        $reception->disable();
    }

    $reception->setClasse('btn-xs btn-primary');
    $reception->setFullWidth();

    if(right('commandes', 3)) //Modifier la commande en cours
    {
        $modal_edit_order = new modalAjax('intervention_informatique', 'edit_order');
        $modal_edit_order->settings(array('id_order' => $cde['id']));
        $modal_edit_order->oppenButton(new Font('pencil'), 'btn-xs btn-warning', true, 'Modifier la commande');
    }

    $suppr = new Button(new Font('remove'), 'javascript:void();', 'Supprimer la commande');
    $suppr->setClasse('btn-danger btn-xs');
    $suppr->setFullWidth();

    if(right('commandes', 4)) //Supprimer la commande en cours
        $suppr->onClick('if(confirm(\'Voulez-vous supprimer cette commande ?\'))window.location.href = \'?suppr_cde='.$cde['id'].'&id_inter='.$cde['id_inter'].'\';');
    else
        $suppr->disable();

    if(is_object($modal_edit_order))
        $table_orders->addCell($reception.$modal_edit_order->getModalOpen().$suppr.$modal_edit_order->getModalHtml());
    else
    {
        $modifier = new Button(new Font('pencil'), 'javascript:void;', 'Modifier la commande');
        $modifier->setClasse('btn-xs btn-warning');
        $modifier->setFullWidth();
        $modifier->disable();
        $table_orders->addCell($reception.$modifier.$suppr);
    }
}

$orders_widget->setContent($table_orders);

$row = new Row($orders_widget);
echo $row;
?>