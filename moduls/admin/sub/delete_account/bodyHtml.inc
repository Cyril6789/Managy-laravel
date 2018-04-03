<?php session_start();

    if($_SESSION['gerant'] AND $_SESSION['compte_principal'])
    {

        function rrmdir($dir) {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (is_dir($dir."/".$object))
                            rrmdir($dir."/".$object);
                        else
                            unlink($dir."/".$object);
                    }
                }
                rmdir($dir);
            }
        }

        $sql = 'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "gi67_managy" AND COLUMN_NAME = "compte_principal"';

        $db = new MySQL();
        $db->Query($sql);

        $db2 = new MySQL();
        while($row_shema = $db->Row())
        {
            $sql = 'DELETE FROM '.$row_shema->TABLE_NAME.' WHERE compte_principal="'.$_SESSION['compte_principal'].'" ';
            $db2->Query($sql);
        }

        $sql = 'DELETE FROM comptes_principaux WHERE id="'.$_SESSION['compte_principal'].'" ';
        $db->Query($sql);

        //delete files
        rrmdir('./../files_managy/'.sha1($_SESSION['cal']));

        if(is_file('./print/images/logos/'.$_SESSION['compte_principal'].'.png'))
            unlink('./print/images/logos/'.$_SESSION['compte_principal'].'.png');

        if(is_file('./print/images/logos/'.$_SESSION['compte_principal'].'.jpg'))
            unlink('./print/images/logos/'.$_SESSION['compte_principal'].'.jpg');

        if(is_file('./images/logos_mails/'.$_SESSION['compte_principal'].'.png'))
            unlink('./images/logos_mails/'.$_SESSION['compte_principal'].'.png');

        if(is_file('./images/logos_mails/'.$_SESSION['compte_principal'].'.jpg'))
            unlink('./images/logos_mails/'.$_SESSION['compte_principal'].'.jpg');

        if($_SESSION['id'] == '1')
            header('location: ./dashboard?compte=1');
        else
            header('location: ./login.html');

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