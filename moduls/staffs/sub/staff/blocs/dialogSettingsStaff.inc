<?php session_start();
$dialog_box = DIALOG_BOX;
if($dialog_box == 'true')
{
    if(right('staffs', 1))
    {
        if($_GET['id_staff'] AND !empty($_GET['id_staff']) AND is_numeric($_GET['id_staff'])) //Un employé séléctionné
        {
            global $db;
            $id_staff = $db->SQLfix($_GET['id_staff']);
            
            $db->QUERY('SELECT COUNT(*) AS nb FROM staffs WHERE id ="'.$id_staff.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            
            if($row->nb) //employé existant
            {
            
                $db->QUERY('SELECT * FROM staffs WHERE id="'.$id_staff.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                $row = $db->Row();
                if($_GET['id_staff'] == 1) //cyril
                {
                    if(right('empty', -1)) //C'est cyril, seul moi peux voir
                    {
                        
                        if(is_file('./templates/'.TEMPLATE_NAME.'/blocs/dialogSettingsStaff.php'))
                            include('./templates/'.TEMPLATE_NAME.'/blocs/dialogSettingsStaff.php');
                    }

                }
                else
                {
                    if(is_file('./templates/'.TEMPLATE_NAME.'/blocs/dialogSettingsStaff.php'))
                        include('./templates/'.TEMPLATE_NAME.'/blocs/dialogSettingsStaff.php');
                }
            }
            
        }
    }
    
    
    
    
    
    
    
    
}

?>