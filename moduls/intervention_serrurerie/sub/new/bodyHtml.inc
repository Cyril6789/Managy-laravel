<?php session_start();

	
    
    
    
    if(right('inter', 2))
    {
        if(empty($_GET['id_customer'])) //Aucun client selectionné, on demande l'appelle d'un client
        {
            if(is_file('./moduls/customers/classes/SelectCustomer.class.inc'))
            {
                require_once ('./moduls/customers/classes/SelectCustomer.class.inc');
                $selecteur = new SelectCustomer('intervention-new-customer-', 'intervention-new');
                $selecteur->no_parent();
                $ModulContent = $selecteur->getHTML();
            }
        }
        else //le client est choisi
        {
            if(is_file('./templates/'.TEMPLATE_NAME.'/pages/create_inter_serrurerie.php'))
            {


                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/create_inter_serrurerie.php');
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