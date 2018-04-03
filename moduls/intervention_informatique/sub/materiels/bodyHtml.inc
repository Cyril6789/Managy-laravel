<?php session_start();

    if(right('inter', 4))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/materiels.php'))
        {


            $materiel = new DatabaseWorker('materiels');
            $materiel->displayedFields('nom');
            $materiel->labelsDisplayedFields('Nom');
            $materiel->activeModify('', 'ModifyMateriel');
            $materiel->activateAdd('', 'addMateriel');


            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/materiels.php');
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