<?php session_start();

    if(right('inter', 10))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/prestations.php'))
        {
            $prestations = new DatabaseWorker('prestations');
            $prestations->displayedFields(Array('designation','duree_defaut'));
            $prestations->labelsDisplayedFields(Array('Désignation', 'Durée par défaut (heure)'));
            $prestations->activeModify('', 'ModifyPrestation');
            $prestations->activateAdd('', 'addPrestation');
            $prestations->activeDelete('', 'DeletePrestation');
                    
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/prestations.php');
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