<?php session_start();
 //GESTION DES POSTS


/*
 * Modify field
 */
if(isset($_POST['edit_id']) AND !empty($_POST['edit_id'])) {


    $_SESSION['notify'] = 'ModifyField';
    $db = new MySQL();

    $edit_id_post = $db->SQLFix($_POST['edit_id']);

    $tab = explode('_', $edit_id_post);

    $type = $tab[1];
    $id = $tab[0];

    switch ($type){
        case 'varchar':
            $default = $db->SQLFix($_POST['field_default_text_edit_'.$id]);
            break;
        case 'textarea':
            $default = $db->SQLFix($_POST['field_default_textarea_edit_'.$id]);;
            break;
        case 'bool':
            $default = $db->SQLFix($_POST['bool_edit_'.$id]);
            break;
        case 'radio':
            $default = '';

            $array_modify = Array();
            for($i=1; $i<$_POST['nb_id_radio_'.$id]; $i++)
            {
                if($_POST['id_'.$i]) // modification valeur déja existante
                {
                    $valeur = $db->SQLFix($_POST['radio_text_'.$i]);
                    if($valeur)
                    {
                        if ($_POST['radio_default_' . $id] == $i)
                            $selected = '1';
                        else
                            $selected = '0';
                        $id_v = $db->SQLFix($_POST['id_'.$i]);
                        $array_modify[] = $id_v;
                        $sql = 'UPDATE valeurs_champs_personnalises SET valeur="' . $valeur . '", selected="'.$selected.'" WHERE id="'.$id_v.'" AND id_champ_personnalise = "'.$id.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ';
                        $db->Query($sql);
                    }
                }
                else // AJOUT nouvelle valeur
                {
                    $value = $db->SQLFix($_POST['radio_text_'.$i]);
                    if(!empty($value))
                    {
                        if($_POST['radio_default_' . $id] == $i)
                            $selected = 1;
                        else
                            $selected = 0;

                        $sql = 'INSERT INTO valeurs_champs_personnalises (id_champ_personnalise, valeur, selected, compte_principal)
                            VALUES ("'.$id.'", "'.$value.'", "'.$selected.'", "'.$_SESSION['compte_principal'].'")';
                        $db->Query($sql);
                        $array_modify[] = $db->GetLastInsertID();
                    }
                }
            }


            //supprimer

            $join = join(',', $array_modify);
            $sql_delete = 'DELETE FROM valeurs_champs_personnalises WHERE id_champ_personnalise="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" AND id NOT IN ('.$join.') ';
            $db->Query($sql_delete);

            break;
        case 'select':
            $default = '';

            $array_modify = Array();
            for($i=1; $i<$_POST['nb_id_select_'.$id]; $i++)
            {
                if($_POST['id_'.$i]) // modification valeur déja existante
                {
                    $valeur = $db->SQLFix($_POST['select_text_'.$i]);
                    if($valeur)
                    {
                        if ($_POST['select_default_' . $id] == $i)
                            $selected = '1';
                        else
                            $selected = '0';
                        $id_v = $db->SQLFix($_POST['id_'.$i]);
                        $array_modify[] = $id_v;
                        $sql = 'UPDATE valeurs_champs_personnalises SET valeur="' . $valeur . '", selected="'.$selected.'" WHERE id="'.$id_v.'" AND id_champ_personnalise = "'.$id.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ';
                        $db->Query($sql);
                    }
                }
                else // AJOUT nouvelle valeur
                {
                    $value = $db->SQLFix($_POST['select_text_'.$i]);
                    if(!empty($value))
                    {
                        if($_POST['select_default_' . $id] == $i)
                            $selected = 1;
                        else
                            $selected = 0;

                        $sql = 'INSERT INTO valeurs_champs_personnalises (id_champ_personnalise, valeur, selected, compte_principal)
                            VALUES ("'.$id.'", "'.$value.'", "'.$selected.'", "'.$_SESSION['compte_principal'].'")';
                        $db->Query($sql);
                        $array_modify[] = $db->GetLastInsertID();
                    }
                }
            }


            //supprimer

            $join = join(',', $array_modify);
            $sql_delete = 'DELETE FROM valeurs_champs_personnalises WHERE id_champ_personnalise="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" AND id NOT IN ('.$join.') ';
            $db->Query($sql_delete);

            break;
        case 'checkbox':
            $default = '';

            $array_modify = Array();
            for($i=1; $i<$_POST['nb_id_checkbox_'.$id]; $i++)
            {
                if($_POST['id_'.$i]) // modification valeur déja existante
                {
                    $valeur = $db->SQLFix($_POST['checkbox_text_'.$i]);
                    if($valeur)
                    {
                        if ($_POST['checkbox_'.$id.'_'.$i] == 'on')
                            $selected = '1';
                        else
                            $selected = '0';
                        $id_v = $db->SQLFix($_POST['id_'.$i]);
                        $array_modify[] = $id_v;
                        $sql = 'UPDATE valeurs_champs_personnalises SET valeur="' . $valeur . '", selected="'.$selected.'" WHERE id="'.$id_v.'" AND id_champ_personnalise = "'.$id.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ';
                        $db->Query($sql);
                    }
                }
                else // AJOUT nouvelle valeur
                {
                    $value = $db->SQLFix($_POST['checkbox_text_'.$i]);
                    if(!empty($value))
                    {
                        if ($_POST['checkbox_'.$id.'_'.$i] == 'on')
                            $selected = 1;
                        else
                            $selected = 0;

                        $sql = 'INSERT INTO valeurs_champs_personnalises (id_champ_personnalise, valeur, selected, compte_principal)
                            VALUES ("'.$id.'", "'.$value.'", "'.$selected.'", "'.$_SESSION['compte_principal'].'")';
                        $db->Query($sql);
                        $array_modify[] = $db->GetLastInsertID();
                    }
                }
            }


            //supprimer

            $join = join(',', $array_modify);
            $sql_delete = 'DELETE FROM valeurs_champs_personnalises WHERE id_champ_personnalise="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" AND id NOT IN ('.$join.') ';
            $db->Query($sql_delete);

            break;
        default:
            $_SESSION['notify'] = 'errorModifyField';
            break;
    }

    $name = $db->SQLFix($_POST['field_name_edit_'.$id]);
    $description = $db->SQLFix($_POST['description_edit_'.$id]);

    $sql = 'UPDATE champs_personnalises SET nom = "'.$name.'", description="'.$description.'", valeur_defaut = "'.$default.'" WHERE id="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'"';
    $db->Query($sql);

    header('Location: ./intervention-fields');
    die();

}

/*
 * ADD field
 */
if(isset($_POST['choices']) AND !empty($_POST['choices']))
{

    $type = $db->SQLFix($_POST['choices']);

    $_SESSION['notify'] = 'AddField';


    switch ($type){
        case 'varchar':
            $name = $db->SQLFix($_POST['field_name']);
            $description = $db->SQLFix($_POST['description']);
            $field_default_text = $db->SQLFix($_POST['field_default_text']);
            $sql = 'INSERT INTO champs_personnalises (nom, description, type, valeur_defaut, compte_principal) 
                    VALUES ("'.$name.'", "'.$description.'", "varchar", "'.$field_default_text.'", "'.$_SESSION['compte_principal'].'")';
            $db->Query($sql);
            break;
        case 'textarea':
            $name = $db->SQLFix($_POST['field_name']);
            $description = $db->SQLFix($_POST['description']);
            $field_default_textarea = $db->SQLFix($_POST['field_default_textarea']);
            $sql = 'INSERT INTO champs_personnalises (nom, description, type, valeur_defaut, compte_principal) 
                    VALUES ("'.$name.'", "'.$description.'", "textarea", "'.$field_default_textarea.'", "'.$_SESSION['compte_principal'].'")';
            $db->Query($sql);
            break;
        case 'radio':
            $name = $db->SQLFix($_POST['field_name']);
            $description = $db->SQLFix($_POST['description']);
            $sql = 'INSERT INTO champs_personnalises (nom, description, type,  compte_principal) 
                    VALUES ("'.$name.'", "'.$description.'", "radio", "'.$_SESSION['compte_principal'].'")';
            $db->Query($sql);
            $last_id = $db->GetLastInsertID();

            for($i=1; $i<$_POST['nb_id_radio']; $i++)
            {
                $value = $db->SQLFix($_POST['radio_text_'.$i]);
                if(!empty($value))
                {
                    if($_POST['radio_default'] == $i)
                        $selected = 1;
                    else
                        $selected = 0;

                    $sql = 'INSERT INTO valeurs_champs_personnalises (id_champ_personnalise, valeur, selected, compte_principal)
                            VALUES ("'.$last_id.'", "'.$value.'", "'.$selected.'", "'.$_SESSION['compte_principal'].'")';
                    $db->Query($sql);
                }
            }

            break;
        case 'select':
            $name = $db->SQLFix($_POST['field_name']);
            $description = $db->SQLFix($_POST['description']);
            $sql = 'INSERT INTO champs_personnalises (nom, description, type,  compte_principal) 
                    VALUES ("'.$name.'", "'.$description.'", "select", "'.$_SESSION['compte_principal'].'")';
            $db->Query($sql);
            $last_id = $db->GetLastInsertID();

            for($i=1; $i<$_POST['nb_id_select']; $i++)
            {
                $value = $db->SQLFix($_POST['select_text_'.$i]);
                if(!empty($value))
                {
                    if($_POST['select_default'] == $i)
                        $selected = 1;
                    else
                        $selected = 0;

                    $sql = 'INSERT INTO valeurs_champs_personnalises (id_champ_personnalise, valeur, selected, compte_principal)
                            VALUES ("'.$last_id.'", "'.$value.'", "'.$selected.'", "'.$_SESSION['compte_principal'].'")';
                    $db->Query($sql);
                }
            }

            break;
        case 'checkbox':
            $name = $db->SQLFix($_POST['field_name']);
            $description = $db->SQLFix($_POST['description']);
            $sql = 'INSERT INTO champs_personnalises (nom, description, type,  compte_principal) 
                    VALUES ("'.$name.'", "'.$description.'", "checkbox", "'.$_SESSION['compte_principal'].'")';
            $db->Query($sql);
            $last_id = $db->GetLastInsertID();

            for($i=1; $i<$_POST['nb_id_checkbox']; $i++)
            {
                $value = $db->SQLFix($_POST['checkbox_text_'.$i]);
                if(!empty($value))
                {
                    if($_POST['checkbox_'.$i] == 'on')
                        $selected = 1;
                    else
                        $selected = 0;

                    $sql = 'INSERT INTO valeurs_champs_personnalises (id_champ_personnalise, valeur, selected, compte_principal)
                            VALUES ("'.$last_id.'", "'.$value.'", "'.$selected.'", "'.$_SESSION['compte_principal'].'")';
                    $db->Query($sql);
                }
            }
            break;
        case 'bool':
            $name = $db->SQLFix($_POST['field_name']);
            $description = $db->SQLFix($_POST['description']);
            $field_default_bool = $db->SQLFix($_POST['bool']);
            $sql = 'INSERT INTO champs_personnalises (nom, description, type, valeur_defaut, compte_principal) 
                    VALUES ("'.$name.'", "'.$description.'", "bool", "'.$field_default_bool.'", "'.$_SESSION['compte_principal'].'")';
            $db->Query($sql);
            break;
        default:
            $_SESSION['notify'] = 'errorAddField';
            break;
    }
    header('Location: ./intervention-fields');
    die();

}

?>