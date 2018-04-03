<?php session_start();

    if(right('sellsy', 1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/sellsy.php'))
        {


            $db = new MySQL();

            $sql = 'SELECT id_diag_sellsy FROM comptes_principaux WHERE id="'.$_SESSION['compte_principal'].'" ';
            $db->Query($sql);
            $row = $db->Row();

            $id_diag_sellsy = $row->id_diag_sellsy;


            

            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/pages/sellsy.php');
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