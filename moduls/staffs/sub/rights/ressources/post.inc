<?php session_start();
 global $db;
 $redirection = false;
 if(isset($_POST['id_staff']) AND is_numeric($_POST['id_staff']))
 {


     $id_staff = $db->SQLfix($_POST['id_staff']);
    if(isset($_POST['apply_rights']))
    {

         $db->Query('DELETE FROM rights_staff WHERE id_staff = "'.$id_staff.'"');
         $redirection = true;
    }

    foreach ($_POST AS $value)
    {
        $tab = explode('_', $value);
        $modul_p = $tab[0];
        $num_p = $tab[1];
        if(is_numeric($num_p))
        {
            $modul_p = $db->SQLFix($modul_p);
            $num_p = $db->SQLFix($num_p);

            $db->Query('INSERT INTO rights_staff (id_staff, modul, num) Values ("'.$id_staff.'", "'.$modul_p.'", "'.$num_p.'")');

        }

    }
    if($redirection)
        Header('Location: ./staffs-rights-'.$id_staff);
 }
?>