<?php session_start();

    if(right('inter', 4))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/statuts.php'))
        {


            $statuts = new DatabaseWorker('statuts');
            $statuts->displayedFields(Array('nom', 'intervention_occupee'));
            $statuts->labelsDisplayedFields(Array('Nom du statut', 'Intervention occupée ?'));
            $statuts->activeModify('');
            $statuts->activateAdd('');
            $statuts->activeDelete('');


            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/statuts.php');
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