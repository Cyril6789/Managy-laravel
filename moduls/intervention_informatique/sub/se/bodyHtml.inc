<?php session_start();

    if(right('inter', 5))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/ses.php'))
        {
            $se = new DatabaseWorker('se');
            $se->displayedFields('nom');
            $se->labelsDisplayedFields('Nom');
            $se->activeModify('', 'ModifySe');
            $se->activateAdd('', 'addSe');


            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/ses.php');
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