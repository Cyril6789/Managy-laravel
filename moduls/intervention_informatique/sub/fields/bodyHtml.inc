<?php session_start();

    if(right('inter', 16))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/fields.php'))
        {

            $db = new MySQL();

            $sql = 'SELECT id, nom, description, type, valeur_defaut FROM champs_personnalises WHERE compte_principal = "'.$_SESSION['compte_principal'].'" ORDER BY id ASC ';

            $db->Query($sql);
            //echo $db->Error();

            $tab_fields = Array();
            $i = 0;
            while($row = $db->Row())
            {
                $tab_fields[$i]['id'] = $row->id;
                $tab_fields[$i]['nom'] = $row->nom;
                $tab_fields[$i]['description'] = $row->description;
                $tab_fields[$i]['type'] = $row->type;
                $tab_fields[$i]['valeur_defaut'] = $row->valeur_defaut;

                switch ($row->type){
                    case 'radio':
                    case 'select':
                    case 'checkbox':
                        $db2 = new MySQL();
                        $sql2 = 'SELECT id, valeur, selected FROM valeurs_champs_personnalises WHERE id_champ_personnalise = "'.$row->id.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ORDER BY id ASC';
                        $db2->Query($sql2);
                        $tab_fields[$i]['valeurs'] = Array();
                        $j = 0;
                        while($row2 = $db2->Row())
                        {
                            $tab_fields[$i]['valeurs'][$j]['id'] = $row2->id;
                            $tab_fields[$i]['valeurs'][$j]['valeur'] = $row2->valeur;
                            $tab_fields[$i]['valeurs'][$j]['selected'] = $row2->selected;
                            $j++;
                        }
                        break;
                }



                $i++;
            }


            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/fields.php');
            $ModulContent = ob_get_contents();
            ob_end_clean();
        }
    }
    else
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/no_right.php'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/no_right.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
        }
    }
?>