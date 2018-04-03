<?php session_start();
//Permet de verifier si l'utilisateur courant à les droit necessaires pour visualiser la page ou l'élément, ou effectuer une action
function right($modul, $num, $staff=0)
{

    $rights = array();

    if($staff)
    {
        global  $db;
        $sql = 'SELECT gerant FROM staffs WHERE id = "'.$staff.'" AND compte_principal="'.$_SESSION['compte_principal'].'"';
        $db->Query($sql);
        $row = $db->Row();
        $gerant = $row->gerant;
    }
    else
        $gerant = $_SESSION['gerant'];

    if(!$gerant)
    {
        if($staff)
        {
            $id_staff = $staff;

            $db->Query("SELECT modul, num FROM rights_staff WHERE id_staff = '".$id_staff."' ");
            while ($row = $db->Row()) {
                $rights[] = $row->modul . "|" . $row->num;
            }
        }
        else
        {
            $id_staff = $_SESSION['id'];
            $rights= $_SESSION['rights'];
        }
    }



    if($num == -1)
    {
        if($_SESSION['id'] == 1)
            return TRUE;
        else
            return FALSE;
    }
    else
    {
        if($gerant)
            return True;
        else
        {
            if($num == 0)
            {
                return TRUE;
            }
            else
            {
                if($staff == 0 AND $_SESSION['id'] == 1)
                {
                    return TRUE;
                }
                else
                {
                    if(in_array($modul.'|'.$num, $rights))
                        return true;
                    else
                        return FALSE;
                }
            }
        }  
    }
}
?>