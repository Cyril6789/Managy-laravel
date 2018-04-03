<?php session_start();

    if(right('inter', 11))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/rapports_types.php'))
        {

            $rapports_types = new DatabaseWorker('rapports_types');
            $rapports_types->displayedFields(Array('titre','texte'));
            $rapports_types->labelsDisplayedFields(Array('Titre du rapport type', 'Rapport'));
            $rapports_types->activeModify('', 'ModifyRapportType');
            $rapports_types->activateAdd('', 'AddRapportType');
            $rapports_types->activeDelete('', 'DeleteRapportType');
            
            
           
                 
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/rapports_types.php');
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