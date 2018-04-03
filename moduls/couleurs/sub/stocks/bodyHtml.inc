<?php session_start();

    if(right('colors', 2))
    {
        global  $db;
        $db->Query('SELECT ral FROM couleurs WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');
            $tab_ral = Array();
            while($row = $db->Row())
            {
                $r = floor($row->ral/1000);
                if(!in_array($r, $tab_ral))
                        $tab_ral[] = $r;
            }
            
            
            if(isset($_GET['ral']) AND is_numeric($_GET['ral']) AND $_GET['ral'] < 10)
            {
                if($_GET['ral'] > 0)
                {
                    $ral = $db->SQLfix($_GET['ral']);
                    $get_ral = $ral;
                    $ral = $ral * 1000;

                    $ral_d = $ral - 1;
                    $ral_f = $ral + 999;
                }
                else
                {
                    $ral_d = 0;
                    $ral_f = 0;
                    $get_ral = 0;
                }
            }
            else
            {
                $ral_d = 999;
                $ral_f = 1999;
                $get_ral = 1;
            }
           
            
            $db->Query('SELECT * FROM couleurs  WHERE  compte_principal="'.$_SESSION['compte_principal'].'" AND ral BETWEEN "'.$ral_d.'" AND "'.$ral_f.'" ORDER BY ral');
              
            $i=0;
                $tab_colors = Array();
                while ($row = $db->Row()) 
                {
                    $tab_colors[$i]['id'] = $row->id;
                    $tab_colors[$i]['name'] = $row->nom;
                    $tab_colors[$i]['ral'] = $row->ral;
                    $i++;
                }
                
                $db->Query('SELECT * FROM aspects WHERE compte_principal="'.$_SESSION['compte_principal'].'"  ORDER BY nom');
                $i=0;
                $tab_aspects[$i] = array();
                while ($row = $db->Row()) 
                {
                    $tab_aspects[$i]['id'] = $row->id;
                    $tab_aspects[$i]['name'] = $row->nom;
                    $i++;
                }
                
                $db->Query('SELECT * FROM stocks_couleurs WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');
                
                while ($row = $db->Row()) 
                {
                    $tab_stocks[$row->id_couleur][$row->id_aspect] = $row->stock;
                }


                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/sub/colors_stocks.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
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