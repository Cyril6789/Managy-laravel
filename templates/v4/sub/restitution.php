<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/10/2016
 * Time: 20:05
 */

$widget = new WidgetBox('Tableau des restitutions');
$tableau = new HtmlTable('', 'table table-bordered table-hover table-responsive table-datatable');
$tableau->addTSection('thead');
$tableau->addRow();
$tableau->addCell('#', '', 'thead');
$tableau->addCell('Client', '', 'thead');
$tableau->addCell('Matériel déposé', '', 'thead');
$tableau->addCell('Panne constatée, travaux demandés', '', 'thead');
$tableau->addCell('Matériel ajouté, remplacé', '', 'thead');
$tableau->addCell('Date de clôture', '', 'thead');
$tableau->addCell('Restitutions', '', 'thead');

$tableau->addTSection('tbody');
$tab_modal = Array();
foreach ($tab_inters AS $inter)
{
    $tableau->addRow();

    $label = new Label('success', $inter['prefix'].$inter['id_inter'], './i'.$inter['id_inter']);
    $tableau->addCell($label);

    $client = new GetInfosCustomer($inter['id_client']);

    //$lien = ;

    //$lien = '<a href="'.$client->GetProfileLink().'">'.$client->getFullName().'</a>';

    $pop = new PopOver('Détails contact', $client->getFullName().'<br />'.$client->getFullAdress().'<br />Fixe : '.$client->getFixe().'<br />Portable : '.$client->getPort().'<br />Mail : '.$client->GetMail());
    $pop->setLink($client->getFullNameWithLink());


    $tableau->addCell($pop);

    $tableau->addCell(nl2br($inter['matos']));
    $tableau->addCell(nl2br($inter['panne']));
    $tableau->addCell(nl2br($inter['materiel_ajoute']));

    $fiche = '<a href="javascript:void();" onclick="window.open(\'./imprimer-'.$inter['id_inter'].'-s\', \'inscr\',\'width=700,height=680,left=100,top=10,scrollbars=yes\');">'.new Font('file-text').' Fiche sortie</a>';

    $tableau->addCell(parse_date($inter['time_cloture']).'<br />'.$fiche);

    $modal = new modalAjax('intervention_informatique', 'restitution', $inter['id_inter']);
    $modal->oppenButton(new Font('share').' Restituer', 'btn-success', 'true', 'Effectuer la réstitution du matériel au client');


    $settings = Array(
        'id_inter'  => $inter['id_inter']
    );

    $modal->settings($settings);

    $tableau->addCell($modal->getModalOpen());
    $tab_modal[] = $modal->getModalHtml();
}





$widget->setContent($tableau, false);
echo $widget;

foreach ($tab_modal As $mod)
    echo $mod;




$row = new Row($inters);
echo $row;

?>