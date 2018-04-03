<?php session_start();

    if(right('inter', 4))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/matieres.php'))
        {
            global  $db;
            $db->Query('SELECT * FROM matieres WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id'); //toutes les matières
            $i=0;
            $tab_matieres = Array();
            while ($row = $db->Row()) 
            {
                $tab_matieres[$i]['id'] = $row->id;
                $tab_matieres[$i]['name'] = $row->nom;
                $i++;
            }
                    
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/matieres.php');
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