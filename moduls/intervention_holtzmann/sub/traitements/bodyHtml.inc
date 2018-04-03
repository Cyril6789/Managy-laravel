<?php session_start();

    if(right('inter', 6))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/traitements.php'))
        {
            global  $db;
            $db->Query('SELECT * FROM traitements  WHERE compte_principal="'.$_SESSION['compte_principal'].'"  ORDER BY id'); //toutes les matières
            $i=0;
            $tab_traitements = Array();
            while ($row = $db->Row()) 
            {
                $tab_traitements[$i]['id'] = $row->id;
                $tab_traitements[$i]['name'] = $row->nom;
                $i++;
            }
                    
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/traitements.php');
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