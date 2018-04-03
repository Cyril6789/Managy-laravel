<?php session_start();

    if(right('lesmodules', -1))
    {
        if($_GET['module'] AND !empty($_GET['module']))
        {
            global $db;
            $nom_module = $db->SQLfix($_GET['module']);
            

            $db->Query('SELECT cp.id, cp.nom_societe, mc.type, mc.alias, mc.prix, mc.nom_propre
                        FROM comptes_principaux AS cp
                        LEFT JOIN modules_comptes AS mc
                        ON (mc.compte_principal = cp.id)
                        AND (mc.nom = "'.$nom_module.'")
                        ORDER BY cp.id
                        ');

            //echo $db->getHTML();
            $tab_societe = Array();
            $i=0;
            while($row = $db->Row())
            {
                $tab_societe[$i]['id'] = $row->id;
                $tab_societe[$i]['nom'] = $row->nom_societe;
                $tab_societe[$i]['nom_propre'] = $row->nom_propre;
                $tab_societe[$i]['alias'] = $row->alias;
                $tab_societe[$i]['type'] = $row->type;
                $tab_societe[$i]['prix'] = $row->prix;
                $i++;
            }

            if(is_file('./templates/'.TEMPLATE_NAME.'/sub/edit_module.php'))
            {
                    ob_start();
                    include('./templates/'.TEMPLATE_NAME.'/sub/edit_module.php');
                    $ModulContent = ob_get_contents();
                    ob_end_clean();
            }
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