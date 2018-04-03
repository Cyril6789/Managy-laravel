<?php

session_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$activated_moduls = new WidgetBox(MODULES_ACTIVADED_MODULS);

$contenu = '';
$modals = '';
foreach ($tab_mods_active AS $mod)
{

    if ($mod['prix'] > 0)
        $prix = '<strong>' . $mod['prix'] . ' € HT</strong>
            <small>' . MODULES_PER_MONTH . '</small>';
    else
        $prix = '<strong>' . MODULES_FREE . '</strong>';

    $module = new WidgetBox($prix, 2);
    $module->forceBox();

    $activer = new Button(new Font('remove'), '?desactiver='.$mod['link'], 'Désactiver le module');
    $activer->setClasse('btn-xs btn-warning');

    $module->addToolbarButtons($activer);
    $text = $mod['nom'].'<br /><br />';

    if($mod['prix'] > 0)
    {

        if($mod['fin_abo']) {
            $label = new Label('success', new Font('check').' Accès complet');
        }
        else {
            $prolongement_modal = new Modal('Prolonger un module', 'prolonger_'.$mod['id']);
            $prolongement_modal->noButton();

            $start = date('Y-m-d', $fin_abo);
            $end = date('Y-m-d');
            $datetime1 = new DateTime($start);
            $datetime2 = new DateTime($end);
            $interval = $datetime1->diff($datetime2);
            $nbmonth= $interval->format('%y')*12 +$interval->format('%m') + 1; //Retourne le nombre de mois



            $prix = $nbmonth * $mod['prix'];

            $form_pro = new FormLayout('Prolonger un module');
            $form_pro->setFormControls('prolon_'.$mod['id'], './Paypal/prepareModule.php', 'get');
            $form_pro->setValueButton('Payer avec PayPal');
            $form_pro->setWidth(6, 6);
            $hidden = new Hidden('id_module');
            $hidden->setValue($mod['id']);
            $form_pro->addLine('Prolongement du module :', $prix.$hidden.'€ HT');

            $prolongement_modal->setContent($form_pro);


            
            if ($mod['date_activation'] + (15 * 24 * 60 * 60) > time()) {
                $label = new Label('warning', new Font('clock-o').' Accès jusqu\'au ' . date('d/m/Y', $mod['date_activation'] + (15 * 24 * 60 * 60)).' - Prolonger', $prolongement_modal->getAhref());
            }
            else {
                $label = new Label('danger', new Font('minus-circle').' Accès restreint - Prolonger', $prolongement_modal->getAhref());
            }
        }
    }
    else
        $label = new Label('success', new Font('check').' Accès complet');

    if(is_object($prolongement_modal))
        $modal = $prolongement_modal->getModalHtml();
    else
        $modal = '';

    $info = '';
    if($mod['texte'])
    {
        $modal_info = new Modal('Description du module "'.$mod['nom'].'"', 'modal_info_'.$mod['id']);
        $modal_info->setWidth('50%');

        $col_gauche = new Col('3', 'md');

        $desactiver = new Button(new Font('remove').' Désactiver', '?desactiver='.$mod['link']);
        $desactiver->setClasse('btn-warning');
        $desactiver->setFullWidth();

        $faq = '';
        if($mod['faq']) {
            $faq = new Button(new Font('question-circle').'</i> FAQ', $mod['faq']);
            $faq->setClasse('btn-info');
            $faq->setFullWidth();
        }
        $col_gauche->setContent($desactiver.$faq);

        $col_droite = new Col('9', 'md');
        $col_droite->setContent($mod['texte']);

        $ligne = new Row($col_gauche.$col_droite);

        $modal_info->setContent($ligne);
        $modal_info->noButton();
        $modal_info->openButton(new Font('question-circle').' Info', 'btn-info', true);
        $col1 = new Col(6, 'md');
        $col1->setContent($modal_info->getOpenHtml());

        $col2 = new Col(6, 'md');
        if($mod['faq']) {
            $faq = new Button(new Font('question-circle').' FAQ', $mod['faq']);
            $faq->setClasse('btn-info');
            $faq->setFullWidth();
            $col2->setContent($faq);
        }
        $row = new Row($col1.$col2);
        $info = '<br />'.$row;
        $modals .= $modal_info->getModalHtml();
    }

    $module->setContent('<div style="text-align: center;">'.$text.$label.'</div>'.$modal.$info);

    $contenu .= $module;

}
$activated_moduls->setContent($contenu, false);
$row = new Row($activated_moduls);
//echo $row;

$not_activated_moduls = new WidgetBox(MODULES_NOT_ACTIVADED_MODULS);

$contenu = '';
foreach ($tab_mods_not_active AS $mod)
{

    if ($mod['prix'] > 0)
        $prix = '<strong>' . $mod['prix'] . ' € HT</strong>
            <small>' . MODULES_PER_MONTH . '</small>';
    else
        $prix = '<strong>' . MODULES_FREE . '</strong>';

    $module = new WidgetBox($prix, 2);
    $module->forceBox();

    $activer = new Button(new Font('plus'), '?activer='.$mod['link'], 'Activer le module');
    $activer->setClasse('btn-xs btn-success');

    $module->addToolbarButtons($activer);
    $text = '<div style="text-align: center;">'.$mod['nom'].'</div>';


    $info = '';
    if($mod['texte'])
    {
        $modal_info = new Modal('Description du module "'.$mod['nom'].'"', 'modal_info_'.$mod['id']);


        $modal_info->setWidth('50%');

        $col_gauche = new Col('3', 'md');

        $desactiver = new Button(new Font('plus').' Activer', '?activer='.$mod['link']);
        $desactiver->setClasse('btn-success');
        $desactiver->setFullWidth();

        $faq = '';
        if($mod['faq']) {
            $faq = new Button(new Font('question-circle').' FAQ', $mod['faq']);
            $faq->setClasse('btn-info');
            $faq->setFullWidth();
        }
        $col_gauche->setContent($desactiver.$faq);

        $col_droite = new Col('9', 'md');
        $col_droite->setContent($mod['texte']);

        $ligne = new Row($col_gauche.$col_droite);

        $modal_info->setContent($ligne);
        $modal_info->noButton();
        $modal_info->openButton(new Font('info-circle').' Info', 'btn-info', true);
        $col1 = new Col(6, 'md');
        $col1->setContent($modal_info->getOpenHtml());

        $col2 = new Col(6, 'md');
        if($mod['faq']) {
            $faq = new Button(new Font('question-circle').' FAQ', $mod['faq']);
            $faq->setClasse('btn-info');
            $faq->setFullWidth();
            $col2->setContent($faq);
        }
        $row = new Row($col1.$col2);
        $info = '<br />'.$row;
        $modals .= $modal_info->getModalHtml();
    }

    $module->setContent('<div style="text-align: center;">'.$text.'</div>'.$modal.$info);

    //$module->setContent($text);

    $contenu .= $module;

}
$not_activated_moduls->setContent($contenu, false);

$row = new Row($activated_moduls.$not_activated_moduls);
echo $row;

echo $modals;
?>