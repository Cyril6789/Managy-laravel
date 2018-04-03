<?php session_start();

    
    if(right('customers', 1))
    {
        if($_GET['id_customer'] AND !empty($_GET['id_customer']) AND is_numeric($_GET['id_customer'])) //Un client séléctionné
        {
            global $db;
            $id_customer = $db->SQLfix($_GET['id_customer']);
            
            $db->QUERY('SELECT COUNT(*) AS nb FROM clients WHERE id ="'.$id_customer.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            
            if($row->nb) //client existant
            {
            
                
                if(is_file('./moduls/customers/classes/SelectCustomer.class.inc'))
                {
                    include ('./moduls/customers/classes/SelectCustomer.class.inc');
                    $selecteur = new SelectCustomer('customers-addcontactoncreateinter-'.$id_customer.'-', 'customers-addcontactoncreateinter-'.$id_customer);
                    $selecteur->setType(2);
                    $ModulContent = $selecteur->getHTML();
                }
                
            }
            else //client innexistant
            {
                if(is_file('./templates/'.TEMPLATE_NAME.'/sub/no_customer.php'))
                {
                    ob_start();
                    include('./templates/'.TEMPLATE_NAME.'/sub/no_customer.php');
                    $ModulContent = ob_get_contents();
                    ob_end_clean();
                }
            }
            
        }
        else // Aucun employéslectionné, on dégage
        {
            if(is_file('./templates/'.TEMPLATE_NAME.'/sub/customer_nul.php'))
            {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/sub/customer_nul.php');
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