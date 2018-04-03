<?php session_start();

    if(right('inter', 5))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/unites_bls.php'))
        {
            global  $db;
            $db->Query('SELECT * FROM unites_bls WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id'); //toutes les matières
            $i=0;
            $tab_unites = Array();
            while ($row = $db->Row()) 
            {
                $tab_unites[$i]['id'] = $row->id;
                $tab_unites[$i]['name'] = $row->nom;
                $i++;
            }
                    
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/unites_bls.php');
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