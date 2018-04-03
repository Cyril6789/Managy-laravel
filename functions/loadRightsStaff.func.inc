<?php session_start();
    function loadRightsStaff()
    {
        global $db;
        $db->Query("SELECT modul, num FROM rights_staff WHERE id_staff = '".$_SESSION['id']."' ");
        while ($row = $db->Row()) {
            $_SESSION['rights'][] = $row->modul . "|" . $row->num;
         }
    }
?>