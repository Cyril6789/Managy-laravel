<?php session_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$widget = new WidgetBox(BL_INTER_INTERS);
$form = new Form('form', './bl-genere_bl');


$table = new HtmlTable('', 'table table-striped table-bordered table-checkable table-highlight-head table-no-inner-border table-hover');
$table->addTSection('thead');
$table->addRow();
$first_check = new CheckBox('check_all');
$first_check->setclass('uniform');
$first_check->checked();
$table->addCell($first_check, 'checkbox-column', 'thead');
$table->addCell(BL_INTER_NUM, '', 'thead');
$table->addCell(BL_INTER_INFORMATIONS, '', 'thead');
$table->addCell(BL_INTER_ELEMENTS, '', 'thead');
$table->addTSection('tbody');

foreach($tab_inters AS $inter)
{
    $table->addRow();
    $check = new CheckBox('inter'.$inter['id_inter']);
    $check->setclass('uniform');
    if($inter['checked'])
        $check->checked();
    $table->addCell($check, 'checkbox-column');
    $table->addCell($inter['id_inter']);
    $table->addCell($inter['projet'].' - '.$inter['matiere'].'<br />'.$inter['couleur'].' (Ral : '.$inter['ral'].') '.$inter['aspect']);

    $td = '';
    if($inter['no_element'])
    {
        $td = $inter['ref_chantier'].'  RAL '.$inter['ral'].' (1 ENSEMBLE)<br />';
    }
    else
    {
        foreach($tab_elements[$inter['id_inter']] AS $elt)
        {
            $td .=  '"'.$inter['ref_chantier'].'"'.$elt['description'].' RAL '.$inter['ral'].' ('.$elt['qte'].' '.$elt['unite'].')<br />';
        }
        $check_masquer = new CheckBox('masquer_ref_chantier_'.$inter['id_inter']);
        if($inter['masquer_ref_chantier'])
            $check_masquer->checked();
        $td .= $check_masquer.' Masquer la référence chantier';
    }
    $table->addCell($td);

}

$col = new Col();
$textarea = new Textarea('commentaire');
$textarea->setValue($descriptif);
$textarea->elastic();

$submit = new Button('Générer le PDF', '#');
$submit->setClasse('btn-primary');
$submit->onClick("$('#form').submit();");


$id_bl_hidden = new Hidden('id_bl');
$id_bl_hidden->setValue($id_bl);

$col->setContent('Descriptif des travaux :'.$textarea.'<br />'.$submit);

$form->setContent($table.$id_bl_hidden.$col);

$widget->setContent($form, false);

$row = new Row($widget);
echo $row;
?>