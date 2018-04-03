<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 18/08/2017
 * Time: 18:46
 */
if(0) //$_SESSION['id'] == 1)
    die('Travaux en cours');
foreach ($_POST AS $key => $value)
    $_POST['key'] = urldecode($value);

$modal = new jsonModal($_POST['titre']);
$modal->width('70%');
$modal->form_id('form_edit_' . $_POST['table_name'].'_'.$_POST['modal_id'].$_POST['id_mod']);

/*
 * Settings
 */

$edit = $_POST['edit'];
$id_form = 'form_edit_' . $_POST['table_name'].'_'.$_POST['modal_id'].$_POST['id_mod'];
$table_name = $_POST['table_name'];
$nb_columns = $_POST['nb_columns'];
$tab_columns = unserialize($_POST['tab_columns']);
$RecordsArray = unserialize($_POST['RecordsArray']);
$tab_data_type_form = unserialize($_POST['tab_data_type_form']);
$displayed_fields = unserialize($_POST['displayed_fields']);
$tab_innerjoin = unserialize($_POST['tab_innerjoin']);
$compte_principal = $_POST['compte_principal'];
$tab_color_picker = unserialize($_POST['tab_color_picker']);
$tab_date = unserialize($_POST['tab_date']);
$tab_date_time = unserialize($_POST['tab_date_time']);
$no_modify_fields = unserialize($_POST['no_modify_fields']);
$password_fields = unserialize($_POST['password_fields']);
$labels_table = unserialize($_POST['labels_table']);
$maxcountvalue = unserialize($_POST['maxcountvalue']);
$db_sets = unserialize($_POST['db']);
$tab_ifvalue = unserialize($_POST['tab_ifvalue']);
$tab_wysiwyg = unserialize($_POST['tab_wysiwyg']);
$if_zero_replace_by_today = $_POST['if_zero_replace_by_today'];
$tab_hiddens = unserialize($_POST['tab_hiddens']);
$tab_hiddens_add = unserialize($_POST['tab_hiddens_add']);
$db_tab = unserialize($_POST['db_tab']);



//print_r($db_tab);

$form = new FormLayout('Saisie');
$form->setFormControls($id_form);
$hiddens = '';
$db = new MySQL();

for ($j = 0; $j < $nb_columns; $j++) {
    if (!(count($tab_data_type_form[$tab_columns[$j]]))) {
        $db->Query('SELECT DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name="' . $table_name . '" AND COLUMN_NAME="' . $tab_columns[$j] . '" AND TABLE_SCHEMA="gi67_managy" ');
        $row = $db->Row();
        $tab_data_type_form[$tab_columns[$j]]['data'] = $row->DATA_TYPE;
        $tab_data_type_form[$tab_columns[$j]]['possibilities'] = $row->COLUMN_TYPE;
    }
}


for ($i = 0; $i < $nb_columns; $i++) {
    if (false !== $key = array_search($tab_columns[$i], $displayed_fields)) {
        $change = false;
        $forced = true;
        $unique = false;
        foreach ($tab_innerjoin AS $keyinner => $inner)
            if ($inner['field_to_replace'] == $tab_columns[$i]) {
                $change = $keyinner + 1;
                $new_field = $inner['new_field'];
                $forced = $inner['forced'];
                if ($inner['unique'])
                    $unique = $inner['field_to_replace'];

            }
        if ($change) {
            $champs = new Select($tab_columns[$i]);
            $champs->withSearch();
            if (!$forced)
                $champs->addOption('0', '--');
            if ($edit)
                $champs->setSelected($RecordsArray[$edit - 1][$tab_columns[$i]]);
            if (is_array($tab_innerjoin[$change - 1]['content']))
                foreach ($tab_innerjoin[$change - 1]['content'] AS $inner) {
                    if ($unique) {
                        $db = new MySQL();
                        $sql = 'SELECT ' . $unique . ' FROM ' . $table_name . ' WHERE id != "' . $RecordsArray[$edit - 1]['id'] . '" ';
                        if ($compte_principal)
                            $sql .= ' AND compte_principal="' . $_SESSION['compte_principal'] . '" ';
                        $db->Query($sql);
                        $total = $db->RowCount();
                        $tab_licence_used = Array();
                        for ($u = 0; $u < $total; $u++) {
                            if ($db->RecordsArray()[$u][$unique] != 0)
                                $tab_licence_used[] = $db->RecordsArray()[$u][$unique];
                        }
                        if (in_array($inner['id'], $tab_licence_used))
                            $champs->addOption($inner['id'], $inner[$new_field], false, true);
                        else
                            $champs->addOption($inner['id'], $inner[$new_field]);
                    } else
                        $champs->addOption($inner['id'], $inner[$new_field]);
                }

        } else {
            $type_field = $tab_data_type_form[$tab_columns[$i]]['data'];
            $possibilites = $tab_data_type_form[$tab_columns[$i]]['possibilities'];
            switch ($type_field) {
                case 'varchar':
                    $champs = new Text($tab_columns[$i]);
                    if(in_array($tab_columns[$i], $tab_color_picker))
                        $champs->colorPicker();
                    $champs->noAutocomplete();
                    if ($edit)
                        $champs->setValue($RecordsArray[$edit - 1][$tab_columns[$i]]);
                    break;
                case 'enum':
                    $enums = str_replace('enum(', '', $possibilites);
                    $enums = str_replace(')', '', $enums);
                    $enums = str_replace("'", "", $enums);
                    $tab_enums = explode(',', $enums);
                    $count = count($tab_enums);
                    $champs = '';
                    if ($count == 2) {
                        if (in_array('0', $tab_enums) AND in_array('1', $tab_enums)) {

                            if ($maxcountvalue['field']) {
                                //echo 'ici';
                                $sql = 'SELECT COUNT(*) AS counter FROM ' . $table_name . ' WHERE ' . $maxcountvalue['field'] . '="' . $maxcountvalue['value'] . '" AND id != "' . $RecordsArray[$edit - 1]['id'] . '"';
                                if ($compte_principal)
                                    $sql .= ' AND compte_principal = "' . $_SESSION['compte_principal'] . '"';
                                $db->Query($sql);
                                $row = $db->Row();
                                $counter = $row->counter;

                                $sql = 'SELECT ' . $maxcountvalue['field_max'] . ' FROM ' . $maxcountvalue['table'] . ' WHERE ' . $maxcountvalue['where_field'] . '="' . $maxcountvalue['where_value'] . '"';

                                $db->Query($sql);
                                $max = $db->RecordsArray()[0][$maxcountvalue['field_max']];
                            }
                            $radio1 = new Radio($tab_columns[$i], '0');
                            if ($edit AND $RecordsArray[$edit - 1][$db_tab['GetColumnName'][$i]] == '0')
                                $radio1->checked();
                            if ($maxcountvalue['value'] == 0 AND $counter >= $max AND count($maxcountvalue) > 0 AND is_array($maxcountvalue))
                                $radio1->disabled();
                            $radio2 = new Radio($tab_columns[$i], '1');
                            if ($maxcountvalue['value'] == 1 AND $counter >= $max AND count($maxcountvalue) > 0)
                                $radio2->disabled();
                            if ($edit AND $RecordsArray[$edit - 1][$db_tab['GetColumnName'][$i]] == '1')
                                $radio2->checked();
                            $champs = $radio1 . ' Non<br />' . $radio2 . ' Oui';
                        } else {
                            foreach ($tab_enums AS $value) {
                                $radio = new Radio($tab_columns[$i], $value);
                                if ($edit AND $RecordsArray[$edit - 1][$db_tab['GetColumnName'][$i]] == $value)
                                    $radio->checked();
                                $champs .= $radio . ' ' . $value . '<br />';
                            }
                        }
                    } else {
                        if ($count > 3) {
                            $champs = new Select($tab_columns[$i]);
                            if ($edit)
                                $champs->setSelected($RecordsArray[$edit - 1][$db_tab['GetColumnName'][$i]]);
                            $champs->withSearch();
                            foreach ($tab_enums AS $value)
                                $champs->addOption($value, $value);
                        } else {
                            foreach ($tab_enums AS $value) {
                                $radio = new Radio($tab_columns[$i], $value);
                                if ($edit AND $RecordsArray[$edit - 1][$db_tab['GetColumnName'][$i]] == $value)
                                    $radio->checked();
                                $champs .= $radio . ' ' . $value . '<br />';
                            }
                        }
                    }
                    break;
                case 'text':
                    $champs = new Textarea($tab_columns[$i]);
                   /* if ($edit)
                        if (is_array($tab_ifvalue))
                            foreach ($tab_ifvalue AS $v)
                                if ($tab_columns[$i] == $v['field'] AND $RecordsArray[$i][$v['where_field']] == $v['where_value'] AND ($RecordsArray[$edit - 1][$tab_columns[$i]] == $v['value'] OR $v['value'] == '*'))
                                    $replace = $v['replace'];*/
                    if ($replace)
                        $champs->setValue($replace);
                    else
                        $champs->setValue($RecordsArray[$edit - 1][$tab_columns[$i]]);
                    if(in_array($tab_columns[$i], $tab_wysiwyg))
                    {
                        $champs->setRows(10);
                        $champs->Wysiwyg();
                    }
                    $champs->elastic();
                    break;
                default:
                    $champs = new Text($tab_columns[$i]);
                    $champs->noAutocomplete();
                    if (in_array($tab_columns[$i], $tab_date))
                        $champs->datePicker();
                    if (in_array($tab_columns[$i], $tab_date_time))
                        $champs->dateTimePicker();
                    if(in_array($tab_columns[$i], $tab_color_picker))
                        $champs->colorPicker();
                    if ($edit)
                        if (in_array($tab_columns[$i], $tab_date)) {
                            if ($RecordsArray[$edit - 1][$tab_columns[$i]] == '0' AND $if_zero_replace_by_today)
                                $champs->setValue(date('d/m/Y', time()));
                            else
                                $champs->setValue(date('d/m/Y', $RecordsArray[$edit - 1][$tab_columns[$i]]));
                        } else {
                            if (in_array($tab_columns[$i], $tab_date_time))
                                $champs->setValue(date('d/m/Y H:i', $RecordsArray[$edit - 1][$tab_columns[$i]]));
                            else
                                $champs->setValue($RecordsArray[$edit - 1][$tab_columns[$i]]);
                        }
            }
        }
        if (!in_array($tab_columns[$i], $no_modify_fields)) {
            if (in_array($tab_columns[$i], $password_fields)) {
                $champs = new Password($tab_columns[$i]);
                $champs->noAutocomplete();
                if ($labels_table[$key])
                    $form->addLine('Nouveau ' . $labels_table[$key], $champs);
                else
                    $form->addLine('Nouveau ' . $tab_columns[$i], $champs);
            } else {
                if ($labels_table[$key])
                    $form->addLine($labels_table[$key], $champs);
                else
                    $form->addLine($tab_columns[$i], $champs);
            }
        }
    }
}


if ($edit) {
    foreach ($tab_hiddens AS $k => $v) {
        $hidden = new Hidden($k);
        $hidden->setValue($v);
        $hiddens .= $hidden;
    }
}
else
{
    foreach ($tab_hiddens_add AS $k => $v) {
        $hidden = new Hidden($k);
        $hidden->setValue($v);
        $hiddens .= $hidden;
    }
}

$id_entry = '';
if ($edit) {
    $id_entry = new Hidden('id');
    $id_entry->setValue($RecordsArray[$edit - 1]['id']);
}
$table = new Hidden('table_name');
$table->setValue($table_name);
$form->addContent($hiddens . $id_entry . $table);




$modal->content($form);

echo $modal;
//mail('contact@depaninfo67.com', 'Erreur', $modal);