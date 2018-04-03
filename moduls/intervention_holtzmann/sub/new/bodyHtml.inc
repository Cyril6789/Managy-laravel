<?php session_start();


    
    
    if(right('inter', 2) AND (END_SUBSCRIPTION > time() - (15 * 24 * 60 * 60)))
    {
        if(empty($_GET['id_customer'])) //Aucun client selectionné, on demande l'appelle d'un client
        {
            if(is_file('./moduls/customers/classes/SelectCustomer.class.inc'))
            {
                require_once ('./moduls/customers/classes/SelectCustomer.class.inc');
                $selecteur = new SelectCustomer('intervention-new-customer-', 'intervention-new');
                $selecteur->no_parent();
                $ModulContent = $selecteur->getHTML();
            }
        }
        else //le client est choisi
        {
            if(is_file('./templates/'.TEMPLATE_NAME.'/sub/create_inter.php'))
            {
                
                global $db;
                $db->Query('SELECT * FROM etapes  WHERE compte_principal="'.$_SESSION['compte_principal'].'"  ORDER BY id ');
                $i=0;
                $tab_steps = Array();
                while ($row = $db->Row()) 
                {
                    $tab_steps[$i]['id'] = $row->id;
                    $tab_steps[$i]['name'] = $row->nom;
                    $i++;
                }
                
                $db->Query('SELECT * FROM traitements  WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id');
                $i=0;
                $tab_traitements=Array();
                while ($row = $db->Row()) 
                {
                    $tab_traitements[$i]['id'] = $row->id;
                    $tab_traitements[$i]['name'] = $row->nom;
                    $i++;
                }
                
                $db->Query('SELECT * FROM couleurs  WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY ral');
                $i=0;
                $tab_colors = Array();
                while ($row = $db->Row()) 
                {
                    $tab_colors[$i]['id'] = $row->id;
                    $tab_colors[$i]['name'] = $row->nom;
                    $tab_colors[$i]['ral'] = $row->ral;
                    $i++;
                }
                
                $db->Query('SELECT * FROM aspects  WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY nom');
                $i=0;
                $tab_aspects = Array();
                while ($row = $db->Row()) 
                {
                    $tab_aspects[$i]['id'] = $row->id;
                    $tab_aspects[$i]['name'] = $row->nom;
                    $i++;
                }

                $db->Query('SELECT * FROM matieres  WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY nom');
                $i=0;
                $tab_matieres = Array();
                while ($row = $db->Row()) 
                {
                    $tab_matieres[$i]['id'] = $row->id;
                    $tab_matieres[$i]['name'] = $row->nom;
                    $i++;
                }

                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/sub/create_inter.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
            }
        }
    }
    else
    {
        if(is_dir('./templates/'.TEMPLATE_NAME.'/pages/'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/no_right.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
        }
    }

?>