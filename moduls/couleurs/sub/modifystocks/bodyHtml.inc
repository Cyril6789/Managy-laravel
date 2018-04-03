<?php session_start();

    if(right('colors', 3))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/modify_stocks.php'))
        {
            
            
            global $db;
            
            $db->Query('SELECT * FROM couleurs WHERE compte_principal="'.$_SESSION['compte_principal'].'"  ORDER BY ral');
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
            
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/modify_stocks.php');
            $ModulContent = ob_get_contents();
            ob_end_clean();
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