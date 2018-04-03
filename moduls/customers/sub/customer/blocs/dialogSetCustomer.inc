<?php session_start();
$dialog_box = DIALOG_BOX_CUSTOMER;

if($dialog_box == 'true')
{
    
    if(right('customers', 2))
    {
        if($_GET['id_customer'] AND !empty($_GET['id_customer']) AND is_numeric($_GET['id_customer'])) //Un client séléctionné
        {
            global $db;
            $id_customer = $db->SQLfix($_GET['id_customer']);
            
            $db->QUERY('SELECT COUNT(*) AS nb FROM clients WHERE id ="'.$id_customer.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            
            if($row->nb) //client existant
            {
            
                $db->QUERY('SELECT * FROM clients WHERE id="'.$id_customer.'"');
                $row = $db->Row();
                
                if(is_file('./templates/'.TEMPLATE_NAME.'/blocs/dialogSetCustomer.php'))
                    include('./templates/'.TEMPLATE_NAME.'/blocs/dialogSetCustomer.php');
                
            }
            
        }
    }
    
    
    
    
    
    
    
    
}

?>