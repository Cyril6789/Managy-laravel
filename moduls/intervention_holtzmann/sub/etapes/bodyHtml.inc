<?php session_start();

    if(right('inter', 5))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/etapes.php'))
        {
            global  $db;
            $db->Query('SELECT * FROM etapes WHERE compte_principal="'.$_SESSION['compte_principal'].'"  ORDER BY id'); //toutes les matières
            $i=0;
            $tab_etapes = Array();
            while ($row = $db->Row()) 
            {
                $tab_etapes[$i]['id'] = $row->id;
                $tab_etapes[$i]['name'] = $row->nom;
                $tab_etapes[$i]['time'] = $row->temps;
                $i++;
            }
                    
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/etapes.php');
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