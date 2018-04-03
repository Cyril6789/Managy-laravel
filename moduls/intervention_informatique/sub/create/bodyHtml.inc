<?php session_start();

    if(right('inter', 2))
    {
        
$ModulContent = '        <object type="application/pdf" name="PDF" id="PDF"> 
        <param name="src" value="./fiche-of-peinture-n-5" /> 
        </object> ';
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