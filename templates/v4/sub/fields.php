<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 25/01/2017
 * Time: 13:06
 */
?>
<script>

    function remove_field(id)
    {
        $('#onclick_button_id').attr('onclick', '$(location).attr(\'href\',\'?suppr_field='+id+'\');');
        $('#confirm_delete_field').modal('show');
    }


    function remove_radio(id, id_field=0)
    {
        var lg = id.length;

        if(id_field) {
            if ($('#radio_' + id_field + '_' + id[lg - 1] + ':checked').val() == id[lg - 1])
                $('#line_to_append_radio_' + id_field + ' input[name=radio_default_' + id_field + ']').val(['1']);
        }
        else {
            if ($('#radio_' + id[lg - 1] + ':checked').val() == id[lg - 1])
                $('#line_to_append_radio input[name=radio_default]').val(['1']);
        }

        $('#'+id).remove();
    }

    function add_radio(id_field=0) {

        <?php
            $radio_text = new Text('radio_text_add', 'radio_text_add', 8);
            $radio_text->placeHolder('valeur');

            $default_radio = new Radio('radio_default', 3, 'radio_add');
            $col_radio = new Col(4, 'md');
            $col_radio->setContent($default_radio.' Défaut <a href="javascript:void()" onclick="remove_radio($(this).parent().parent().attr(\\\'id\\\'), \'+id_field+\')"><i class="fa fa-trash"></i></a>');

            $html = str_replace(CHR(10),"",$radio_text.$col_radio);
            $html = str_replace(CHR(13),"",$html);
            $html = '<div id="div_add">'.$html.'</div>';
                        ?>
        if(id_field){
            $('#line_to_append_radio_'+id_field).append('<?php echo $html;?>');
            $('#radio_text_add').attr('name', 'radio_text_' + $('#nb_id_radio_'+id_field).val());
            $('#radio_text_add').attr('id', 'radio_text_' + $('#nb_id_radio_'+id_field).val());
            $('#radio_add').val($('#nb_id_radio_'+id_field).val());
            $('#radio_add').attr('name', 'radio_default_'+id_field);
            $('#radio_add').attr('id', 'radio_'+id_field+'_' + $('#nb_id_radio_'+id_field).val());
            $('#div_add').attr('id', 'div_add_'+id_field+'_' + $('#nb_id_radio_'+id_field).val());

            $('#nb_id_radio_'+id_field).val( parseInt($('#nb_id_radio_'+id_field).val()) + 1);
        }
        else {
            $('#line_to_append_radio').append('<?php echo $html;?>');
            $('#radio_text_add').attr('name', 'radio_text_' + $('#nb_id_radio').val());
            $('#radio_text_add').attr('id', 'radio_text_' + $('#nb_id_radio').val());
            $('#radio_add').val($('#nb_id_radio').val());
            $('#radio_add').attr('id', 'radio_' + $('#nb_id_radio').val());
            $('#div_add').attr('id', 'div_add_' + $('#nb_id_radio').val());

            $('#nb_id_radio').val( parseInt($('#nb_id_radio').val()) + 1);
        }



    }
    function remove_select(id, id_field=0)
    {
        var lg = id.length;

        if(id_field) {
            if ($('#select_' + id_field + '_' + id[lg - 1] + ':checked').val() == id[lg - 1])
                $('#line_to_append_select_' + id_field + ' input[name=select_default_' + id_field + ']').val(['1']);
        }
        else {
            if ($('#select_' + id[lg - 1] + ':checked').val() == id[lg - 1])
                $('#line_to_append_select input[name=select_default]').val(['1']);
        }

        $('#'+id).remove();
    }

    function add_select(id_field=0) {

        <?php
        $select_text = new Text('select_text_add', 'select_text_add', 8);
        $select_text->placeHolder('valeur');

        $default_select = new Radio('select_default', 3, 'select_add');
        $col_select = new Col(4, 'md');
        $col_select->setContent($default_select.' Défaut <a href="javascript:void()" onclick="remove_select($(this).parent().parent().attr(\\\'id\\\'), \'+id_field+\')"><i class="fa fa-trash"></i></a>');

        $html = str_replace(CHR(10),"",$select_text.$col_select);
        $html = str_replace(CHR(13),"",$html);
        $html = '<div id="div_add">'.$html.'</div>';
        ?>
        if(id_field){
            $('#line_to_append_select_'+id_field).append('<?php echo $html;?>');
            $('#select_text_add').attr('name', 'select_text_' + $('#nb_id_select_'+id_field).val());
            $('#select_text_add').attr('id', 'select_text_' + $('#nb_id_select_'+id_field).val());
            $('#select_add').val($('#nb_id_select_'+id_field).val());
            $('#select_add').attr('name', 'select_default_'+id_field);
            $('#select_add').attr('id', 'select_'+id_field+'_' + $('#nb_id_select_'+id_field).val());
            $('#div_add').attr('id', 'div_add_'+id_field+'_' + $('#nb_id_select_'+id_field).val());

            $('#nb_id_select_'+id_field).val( parseInt($('#nb_id_select_'+id_field).val()) + 1);
        }
        else {
            $('#line_to_append_select').append('<?php echo $html;?>');
            $('#select_text_add').attr('name', 'select_text_' + $('#nb_id_select').val());
            $('#select_text_add').attr('id', 'select_text_' + $('#nb_id_select').val());
            $('#select_add').val($('#nb_id_select').val());
            $('#select_add').attr('id', 'select_' + $('#nb_id_select').val());
            $('#div_add').attr('id', 'div_add_' + $('#nb_id_select').val());

            $('#nb_id_select').val( parseInt($('#nb_id_select').val()) + 1);
        }



    }

    function remove_checkbox(id)
    {
        $('#'+id).remove();
    }

    function add_checkbox(id_field=0) {
        <?php
        $checkbox_text = new Text('checkbox_text_add', 'checkbox_text_add', 8);
        $checkbox_text->placeHolder('valeur');

        $default_checkbox = new CheckBox('checkbox_add');
        $col_checkbox = new Col(4, 'md');
        $col_checkbox->setContent($default_checkbox.' Coché <a href="javascript:void()" onclick="remove_checkbox($(this).parent().parent().attr(\\\'id\\\'))"><i class="fa fa-trash"></i></a>');

        $html = str_replace(CHR(10),"",$checkbox_text.$col_checkbox);
        $html = str_replace(CHR(13),"",$html);
        $html = '<div id="div_add">'.$html.'</div>';
        ?>
        if(id_field){
            $('#line_to_append_checkbox_'+id_field).append('<?php echo $html;?>');
            $('#checkbox_text_add').attr('name', 'checkbox_text_' + $('#nb_id_checkbox_'+id_field).val());
            $('#checkbox_text_add').attr('id', 'checkbox_text_' + $('#nb_id_checkbox_'+id_field).val());
            $('#checkbox_add').attr('name', 'checkbox_'+id_field+'_' + $('#nb_id_checkbox_'+id_field).val());
            $('#checkbox_add').attr('id', 'checkbox_'+id_field+'_' + $('#nb_id_checkbox_'+id_field).val());
            $('#div_add').attr('id', 'div_add_'+id_field+'_' + $('#nb_id_checkbox_'+id_field).val());

            $('#nb_id_checkbox_'+id_field).val( parseInt($('#nb_id_checkbox_'+id_field).val()) + 1);
        }
        else {
            $('#line_to_append_checkbox').append('<?php echo $html;?>');
            $('#checkbox_text_add').attr('name', 'checkbox_text_' + $('#nb_id_checkbox').val());
            $('#checkbox_text_add').attr('id', 'checkbox_text_' + $('#nb_id_checkbox').val());
            $('#checkbox_add').attr('name', 'checkbox_' + $('#nb_id_checkbox').val());
            $('#checkbox_add').attr('id', 'checkbox_' + $('#nb_id_checkbox').val());
            $('#div_add').attr('id', 'div_add_' + $('#nb_id_checkbox').val());

            $('#nb_id_checkbox').val( parseInt($('#nb_id_checkbox').val()) + 1);
        }



    }

    function change_choice(type)
    {
        if(type != 0) {

            switch(type)
            {
                case 'varchar':
                    <?php $text = new Text('field_default_text'); $text->placeHolder('Valeur par défaut (facultatif)');?>
                    $('#values_content').html('<?php echo $text;?>');
                    break;
                case 'radio':
                    <?php $widget_radio = new WidgetBox('Liste des valeurs');

                        $nb_id_radio = new hidden('nb_id_radio');
                        $nb_id_radio->setValue('3');

                        $radio_text1 = new Text('radio_text_1', 'radio_text_1', 8);
                        $radio_text1->placeHolder('valeur');

                        $default_radio1 = new Radio('radio_default', 1, 'radio_1');
                        $default_radio1->checked();
                        $col_radio1 = new Col(4, 'md');
                        $col_radio1->setContent($default_radio1.' Défaut');

                        $radio_text2 = new Text('radio_text_2', 'radio_text_2', 8);
                        $radio_text2->placeHolder('valeur');

                        $default_radio2 = new Radio('radio_default', 2, 'radio_2');
                        $col_radio2 = new Col(4, 'md');
                        $col_radio2->setContent($default_radio2.' Défaut');

                        $col_add_button = new Col(12, 'md');
                        $add_button = new Button('Ajouter une valeur', 'javascript:void();');
                        $add_button->setClasse('btn-primary');
                        $add_button->onClick('add_radio();');
                        $col_add_button->setContent($add_button);
                        
                        $line = new Col(12, 'md', 'line_to_append_radio');
                        $line->scrollable();
                        $line->setContent($radio_text1.$col_radio1.$radio_text2.$col_radio2);

                        $widget_radio->setContent($line.$col_add_button.$nb_id_radio, false);
                    ?>
                    $('#values_content').html('<?php echo $widget_radio->nonl();?>');
                    break;
                case 'textarea':
                    <?php $textarea = new textarea('field_default_textarea'); $textarea->elastic(); $textarea->placeholder('Valeur par défaut (facultatif)');?>
                    $('#values_content').html('<?php echo $textarea;?>');
                    break;
                case 'textarearich':
                    <?php $textarearich = new Textarea('field_default_textarearich'); $textarearich->Wysiwyg(); $textarea->elastic();?>
                    $('#values_content').html('<?php echo $textarearich;?>');
                    break;
                case 'select':
                    <?php $widget_select = new WidgetBox('Liste des valeurs');

                    $nb_id_select = new hidden('nb_id_select');
                    $nb_id_select->setValue('3');

                    $select_text1 = new Text('select_text_1', 'select_text_1', 8);
                    $select_text1->placeHolder('valeur');

                    $default_select1 = new Radio('select_default', 1, 'select_1');
                    $default_select1->checked();
                    $col_select1 = new Col(4, 'md');
                    $col_select1->setContent($default_select1.' Défaut');

                    $select_text2 = new Text('select_text_2', 'select_text_2', 8);
                    $select_text2->placeHolder('valeur');

                    $default_select2 = new Radio('select_default', 2, 'select_2');
                    $col_select2 = new Col(4, 'md');
                    $col_select2->setContent($default_select2.' Défaut');

                    $col_add_button = new Col(12, 'md');
                    $add_button = new Button('Ajouter une valeur', 'javascript:void();');
                    $add_button->setClasse('btn-primary');
                    $add_button->onClick('add_select();');
                    $col_add_button->setContent($add_button);

                    $line = new Col(12, 'md', 'line_to_append_select');
                    $line->scrollable();
                    $line->setContent($select_text1.$col_select1.$select_text2.$col_select2);

                    $widget_select->setContent($line.$col_add_button.$nb_id_select, false);
                    ?>
                    $('#values_content').html('<?php echo $widget_select->nonl();?>');
                    break;
                case 'checkbox':
                    <?php $widget_checkbox = new WidgetBox('Liste des valeurs');

                    $nb_id_checkbox = new hidden('nb_id_checkbox');
                    $nb_id_checkbox->setValue('3');

                    $checkbox_text1 = new Text('checkbox_text_1', 'checkbox_text_1', 8);
                    $checkbox_text1->placeHolder('valeur');

                    $default_checkbox1 = new CheckBox('checkbox_1');
                    $col_checkbox1 = new Col(4, 'md');
                    $col_checkbox1->setContent($default_checkbox1.' Coché');

                    $checkbox_text2 = new Text('checkbox_text_2', 'checkbox_text_2', 8);
                    $checkbox_text2->placeHolder('valeur');

                    $default_checkbox2 = new CheckBox('checkbox_2');
                    $col_checkbox2 = new Col(4, 'md');
                    $col_checkbox2->setContent($default_checkbox2.' Coché <a href="javascript:void()" onclick="remove_checkbox(\\\'div_add_2\\\')"><i class="fa fa-trash"></i></a>');

                    $col_add_button = new Col(12, 'md');
                    $add_button = new Button('Ajouter une valeur', 'javascript:void();');
                    $add_button->setClasse('btn-primary');
                    $add_button->onClick('add_checkbox();');
                    $col_add_button->setContent($add_button);

                    $line = new Col(12, 'md', 'line_to_append_checkbox');
                    $line->scrollable();
                    $line->setContent($checkbox_text1.$col_checkbox1.'<div id="div_add_2">'.$checkbox_text2.$col_checkbox2.'</div>');

                    $widget_checkbox->setContent($line.$col_add_button.$nb_id_checkbox, false);
                    ?>
                    $('#values_content').html('<?php echo $widget_checkbox->nonl();?>');
                    break;
                case 'bool':
                    <?php $oui = new Radio('bool', 1); $oui->checked(); $non = new Radio('bool', 0); ?>
                    $('#values_content').html('Valeur par défaut : <?php echo $oui;?> Oui, <?php echo $non;?> Non');
                    break;
            }

        }
    }


    </script>
    


<?php

$modal = new Modal('Êtes vous sûr ?', 'confirm_delete_field');
$modal->setWidth('20%');
$modal->setContent('Si vous supprimez ce champs personnalisé, il ne sera plus visible à aucune endroit où il est utilisé !');
$modal->setOnclickButton('Supprimer', '', 'onclick_button_id');
echo $modal->getModalHtml();


$modal = new Modal('Ajouter un champs personnalisé', 'add_field');
$modal->setWidth("60%");
$modal->openLink('Ajouter un champs');
$modal->setSubmitButton('form_add_field', 'Ajouter le champs personnalisé', '');

$widget1 = new FormLayout('Informations générales', 12, false);
$widget1->setWidth(2, 10);
//$widget1->setFormControls('first');

$choices = new Select('choices');
$choices->withSearch();
$choices->onChange("change_choice(this.value);");
$choices->addOption('varchar', 'Texte simple');
$choices->addOption('radio', 'Choix unique (radio)');
$choices->addOption('textarea', 'Texte libre (plusieurs lignes)');
$choices->addOption('select', 'Liste de choix');
$choices->addOption('checkbox', 'Choix multiple');
$choices->addOption('bool', 'Oui / Non');
//$choices->addOption('textarearich', 'Texte riche (avec editeur de texte)');
$widget1->addLine('Type', $choices);

$name = new Text('field_name');
$name->placeHolder('Ex : Type de matériel');
$name->required();
$widget1->addLine('Nom du champs', $name);

$description = new Textarea('description');
$description->elastic();
$description->placeholder('Expliquez ici le fonctionnement ou l\'utilité de ce champs à vos collaborateurs');
$widget1->addLine('Description', $description);

$widget2 = new WidgetBox('Paramètres du type de champs', 12, 'md', 'widget_values');
$text = new Text('field_default_text');
$text->placeHolder('Valeur par défaut (facultatif)');
$widget2->setContent($text, false, 'values_content');

$col1 = new Col(7);
$col1->setContent($widget1);

$col2 = new Col(5);
$col2->setContent($widget2);

$form_global = new Form('form_add_field');
$form_global->setContent($col1.$col2);

$modal->setContent($form_global);

//echo $modal->openNow();
echo $modal->getModalHtml();



/*
 * Affichage du tableau
 */

$tableau_fields = new HtmlTable();
$tableau_fields->addTSection('thead');
$tableau_fields->addRow();
$tableau_fields->addCell('Nom', '', 'thead');
$tableau_fields->addCell('Type', '', 'thead');
$tableau_fields->addCell('Description', '', 'thead');
$tableau_fields->addCell('Valeurs', '', 'thead');
$tableau_fields->addCell('Actions', '', 'thead', Array('style'=>'width:5%;'));

$tableau_fields->addTSection('body');

foreach($tab_fields AS $field)
{
    $tableau_fields->addRow();
    $tableau_fields->addCell($field['nom']);

    switch ($field['type']){
        case 'varchar':
            $type = 'Texte simple';
            break;
        case 'radio':
            $type = 'Choix unique (Radio)';
            break;
        case 'textarea':
            $type = 'Texte libre (Plusieurs lignes)';
            break;
        case 'select':
            $type = 'Liste de choix';
            break;
        case 'checkbox':
            $type = 'Choix multiple';
            break;
        case 'bool':
            $type = 'Oui / Non';
            break;
        default:
            $type = '';
            break;
    }

    $tableau_fields->addCell($type);
    $tableau_fields->addCell(nl2br($field['description']));
    switch ($field['type']){
        case 'varchar':
        case 'textarea':
            $default = $field['valeur_defaut'];
            break;
        case 'radio':
        case 'select':
        case 'checkbox':
            $select = new Select('valeurs', 'valeurs_'.$field['id']);
            $select->withSearch();
            foreach($field['valeurs'] AS $valeur)
            {
                if($valeur['selected'])
                    $select->addOption($valeur['id'], $valeur['valeur'].' (valeur activée)');
                else
                    $select->addOption($valeur['id'], $valeur['valeur']);
            }
            $default = $select;
            break;
        case 'bool':
            if($field['valeur_defaut'])
                $default = 'Oui';
            else
                $default = 'Non';
            break;
    }

    $tableau_fields->addCell($default);
    $dropdown = new DropdownButton();

    $modal_edit = new Modal('Modifier un champs personnalisé', 'edit_field_'.$field['id']);
    $modal_edit->setWidth("60%");
    $modal_edit->openLink('Modifier un champs');
    $modal_edit->setSubmitButton('form_edit_field_'.$field['id'], 'Modifier le champs personnalisé', '');

    $widget1 = new FormLayout('Informations générales', 12, false);
    $widget1->setWidth(2, 10);
//$widget1->setFormControls('first');


//$choices->addOption('textarearich', 'Texte riche (avec editeur de texte)');
    $widget1->addLine('Type', $type);

    $name = new Text('field_name_edit_'.$field['id']);
    $id_edit = new Hidden('edit_id', 'edit_'.$field['id']);
    $id_edit->setValue($field['id'].'_'.$field['type']);
    $name->placeHolder('Ex : Type de matériel');
    $name->setValue($field['nom']);
    $name->required();
    $widget1->addLine('Nom du champs', $name.$id_edit);

    $description = new Textarea('description_edit_'.$field['id']);
    $description->setValue($field['description']);
    $description->elastic();
    $description->placeholder('Expliquez ici le fonctionnement ou l\'utilité de ce champs à vos collaborateurs');
    $widget1->addLine('Description', $description);

    $widget2 = new WidgetBox('Paramètres du type de champs', 12, 'md', 'widget_values_'.$field['id']);

    switch ($field['type']){
        case 'varchar':
            $widget_2_content = new Text('field_default_text_edit_'.$field['id']);
            $widget_2_content->placeHolder('Valeur par défaut (facultatif)');
            $widget_2_content->setValue($field['valeur_defaut']);
            break;
        case 'textarea':
            $widget_2_content = new textarea('field_default_textarea_edit_'.$field['id']);
            $widget_2_content->elastic();
            $widget_2_content->placeholder('Valeur par défaut (facultatif)');
            $widget_2_content->setValue($field['valeur_defaut']);
            break;
        case 'bool':
            $oui = new Radio('bool_edit_'.$field['id'], 1);
            if($field['valeur_defaut'])
                $oui->checked();
            $non = new Radio('bool_edit_'.$field['id'], 0);
            if(!$field['valeur_defaut'])
                $non->checked();
            $widget_2_content = 'Valeur par défaut : '. $oui.' Oui, '. $non.' Non';
            break;
        case 'radio';
            $nb_id_radio = new hidden('nb_id_radio_'.$field['id']);
            $counteur = 1;
            $html = '';
            foreach($field['valeurs'] AS $valeur)
            {
                $hidden = new Hidden('id_'.$counteur);
                $hidden->setValue($valeur['id']);
                $radio_text = new Text('radio_text_'.$counteur, 'radio_text_'.$counteur, 8);
                $radio_text->setValue($valeur['valeur']);
                $radio_text->placeHolder('valeur');

                $default_radio  = new Radio('radio_default_'.$field['id'], $counteur, 'radio_'.$field['id'].'_'.$counteur);
                if($valeur['selected'])
                    $default_radio->checked();
                $col_radio = new Col(4, 'md');
                if($counteur>2)
                    $col_radio->setContent($default_radio.' Défaut <a href="javascript:void()" onclick="remove_radio(\'div_add_'.$field['id'].'_'.$counteur.'\', '.$field['id'].')"><i class="fa fa-trash"></i></a>');
                else
                    $col_radio->setContent($default_radio.' Défaut');

                $html .= '<div id="div_add_'.$field['id'].'_'.$counteur.'">'.$hidden.$radio_text.$col_radio.'</div>';
                $counteur++;
            }

            $nb_id_radio->setValue($counteur);


            $col_add_button = new Col(12, 'md');
            $add_button = new Button('Ajouter une valeur', 'javascript:void();');
            $add_button->setClasse('btn-primary');
            $add_button->onClick('add_radio('.$field['id'].');');
            $col_add_button->setContent($add_button);

            $line = new Col(12, 'md', 'line_to_append_radio_'.$field['id']);
            $line->scrollable();
            $line->setContent($html);

            $widget_2_content = $line.$col_add_button.$nb_id_radio;
            break;
        case 'select';
            $nb_id_select = new hidden('nb_id_select_'.$field['id']);
            $counteur = 1;
            $html = '';
            foreach($field['valeurs'] AS $valeur)
            {
                $hidden = new Hidden('id_'.$counteur);
                $hidden->setValue($valeur['id']);
                $select_text = new Text('select_text_'.$counteur, 'select_text_'.$counteur, 8);
                $select_text->setValue($valeur['valeur']);
                $select_text->placeHolder('valeur');

                $default_select  = new Radio('select_default_'.$field['id'], $counteur, 'select_'.$field['id'].'_'.$counteur);
                if($valeur['selected'])
                    $default_select->checked();
                $col_select = new Col(4, 'md');
                if($counteur>2)
                    $col_select->setContent($default_select.' Défaut <a href="javascript:void()" onclick="remove_select(\'div_add_'.$field['id'].'_'.$counteur.'\', '.$field['id'].')"><i class="fa fa-trash"></i></a>');
                else
                    $col_select->setContent($default_select.' Défaut');

                $html .= '<div id="div_add_'.$field['id'].'_'.$counteur.'">'.$hidden.$select_text.$col_select.'</div>';
                $counteur++;
            }

            $nb_id_select->setValue($counteur);


            $col_add_button = new Col(12, 'md');
            $add_button = new Button('Ajouter une valeur', 'javascript:void();');
            $add_button->setClasse('btn-primary');
            $add_button->onClick('add_select('.$field['id'].');');
            $col_add_button->setContent($add_button);

            $line = new Col(12, 'md', 'line_to_append_select_'.$field['id']);
            $line->scrollable();
            $line->setContent($html);

            $widget_2_content = $line.$col_add_button.$nb_id_select;
            break;
        case 'checkbox';
            $nb_id_checkbox = new hidden('nb_id_checkbox_'.$field['id']);
            $counteur = 1;
            $html = '';
            foreach($field['valeurs'] AS $valeur)
            {
                $hidden = new Hidden('id_'.$counteur);
                $hidden->setValue($valeur['id']);
                $checkbox_text = new Text('checkbox_text_'.$counteur, 'checkbox_text_'.$counteur, 8);
                $checkbox_text->setValue($valeur['valeur']);
                $checkbox_text->placeHolder('valeur');

                $default_checkbox  = new CheckBox('checkbox_'.$field['id'].'_'.$counteur);
                if($valeur['selected'])
                    $default_checkbox->checked();
                $col_checkbox = new Col(4, 'md');
                if($counteur>1)
                    $col_checkbox->setContent($default_checkbox.' Coché <a href="javascript:void()" onclick="remove_checkbox(\'div_add_'.$field['id'].'_'.$counteur.'\')"><i class="fa fa-trash"></i></a>');
                else
                    $col_checkbox->setContent($default_checkbox.' Coché');

                $html .= '<div id="div_add_'.$field['id'].'_'.$counteur.'">'.$hidden.$checkbox_text.$col_checkbox.'</div>';
                $counteur++;
            }

            $nb_id_checkbox->setValue($counteur);


            $col_add_button = new Col(12, 'md');
            $add_button = new Button('Ajouter une valeur', 'javascript:void();');
            $add_button->setClasse('btn-primary');
            $add_button->onClick('add_checkbox('.$field['id'].');');
            $col_add_button->setContent($add_button);

            $line = new Col(12, 'md', 'line_to_append_checkbox_'.$field['id']);
            $line->scrollable();
            $line->setContent($html);

            $widget_2_content = $line.$col_add_button.$nb_id_checkbox;
            break;
    }


    $widget2->setContent($widget_2_content, false, 'values_content_edit');

    $col1 = new Col(7);
    $col1->setContent($widget1);

    $col2 = new Col(5);
    $col2->setContent($widget2);

    $form_global = new Form('form_edit_field_'.$field['id']);
    $form_global->setContent($col1.$col2);

    $modal_edit->setContent($form_global);
    echo $modal_edit->getModalHtml();

    $dropdown->addSubButton('<i class="fa fa-pencil"></i> Modifier', $modal_edit->getAhref());
    $dropdown->addSubButton('<i class="fa fa-remove"></i> Supprimer','javascript:void();', 'remove_field('.$field['id'].');');




    $tableau_fields->addCell($dropdown);
}


$widget_tableau = new WidgetBox('Tableau des champs personnalisés');
$add_button = new Button('<i class="fa fa-plus"></i>', $modal->getAhref());
$add_button->setClasse('btn-primary');
$widget_tableau->addToolbarButtons($add_button);
$widget_tableau->setContent($tableau_fields, false);

$row = new Row($widget_tableau);
echo $row;


?>