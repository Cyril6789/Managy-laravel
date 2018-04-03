<?php session_start();

    if(right('inter', 6))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/antivirus.php'))
        {
            $antivirus = new DatabaseWorker('antivirus');
            $antivirus->displayedFields('nom');
            $antivirus->labelsDisplayedFields('Nom');
            $antivirus->activeModify('', 'ModifyAntivirus');
            $antivirus->activateAdd('', 'addAntivirus');
                    
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/antivirus.php');
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