<?php

session_start();

function changements_interventions($id_inter)
{
    global $db;
    
    $db->Query('SELECT id_staff FROM prise_en_charge WHERE id_intervention = "'.$id_inter.'" AND id_staff != "'.$_SESSION['id'].'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
    while($row = $db->Row())
    {
        $db2 = new MySQL();
        $db2->Query('SELECT COUNT(*) AS nb FROM changements_interventions WHERE id_staff_v ="'.$row->id_staff.'" AND id_inter="'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
        $row2 = $db2->Row();
        if(!$row2->nb)
        {
            $db2->Query('INSERT INTO changements_interventions (id_staff_c, id_staff_v, id_inter, compte_principal) VALUES ("'.$_SESSION['id'].'", "'.$row->id_staff.'", "'.$id_inter.'", "'.$_SESSION['compte_principal'].'") ');
        }
    }
            
}

?>