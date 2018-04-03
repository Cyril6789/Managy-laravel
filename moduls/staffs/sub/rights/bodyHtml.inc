<?php session_start();


if(right('rights', 1))
{
    if(isset($_GET['id_staff']) AND !empty($_GET['id_staff']) AND is_numeric($_GET['id_staff']))
    {

        global $db;

        $id_staff = $db->SQLfix($_GET['id_staff']);

        $db->Query('SELECT COUNT(*) AS nb FROM staffs WHERE id = "'.$id_staff.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
        $row = $db->Row();

        if($row->nb)
        {

            $db->Query('SELECT * AS nb FROM staffs WHERE id = "'.$id_staff.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();


            $acces = false;

            if($_GET['id_staff'] == 1) //cyril
            {
                if(right('empty', -1)) //C'est cyril, seul moi peux voir
                    $acces = TRUE;
                else
                    $acces = FALSE;

            }
            else
                 $acces = true;



            if($acces)
            {
                $rights = loadRightsSettings();
                $tab_rights = array();



                 foreach ($rights AS $krights => $vrights) // droits par modules
                 {
                     foreach ($vrights AS $kvrights => $vvrights)
                     {
                        $tab_rights[$krights][$kvrights]['value'] = $rights[$krights][$kvrights];


                        if(right($krights, $kvrights, $id_staff))
                            $tab_rights[$krights][$kvrights]['checked'] = true;
                        else
                            $tab_rights[$krights][$kvrights]['checked'] = false;  
                     }
                 }


                $template_name = TEMPLATE_NAME;
                if(is_file('./templates/'.TEMPLATE_NAME.'/sub/rights.php'))
                {
                        ob_start();
                        include('./templates/'.TEMPLATE_NAME.'/sub/rights.php');
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

        }
        else //Aucun staff de ce numéro
        {
            if(is_file('./templates/'.TEMPLATE_NAME.'/pages/no_staff.php'))
            {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/no_staff.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
            }
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