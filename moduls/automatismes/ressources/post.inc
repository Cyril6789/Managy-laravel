<?php session_start();
 //GESTION DES POSTS

/*
 * Modification automatisme
 */


if(isset($_POST['type_atelier_rdv']) AND is_numeric($_POST['type_atelier_rdv']) AND (isset($_POST['id']))) {

    $type_atelier_rdv = $db->SQLFix($_POST['type_atelier_rdv']);
    $action = $db->SQLFix($_POST['action']);

    $id = $db->SQLFix($_POST['id']);

    $id_sms = 0;
    $id_mail = 0;
    if($_POST['type_notif'] == 'liste_sms')
    {
        $id_sms = $db->SQLFix($_POST['id_sms']);
        $dest = 1;
    }

    if($_POST['type_notif'] == 'liste_mail')
    {
        $id_mail = $db->SQLFix($_POST['id_mail']);
        if($_POST['dest'] == '1')
            $dest = 1;
        else
            $dest = 2;
    }

    if(!$_POST['planned'] == 'on')
    {
        $immediat = 1;
        $j = 0;
        $h = 0;
        $m = 0;
        $htime = '';
        $mtime = '';
    }
    else
    {
        $immediat = 0;
        $j = $db->SQLFix($_POST['j']);
        if($_POST['heurefixe'] == 'on')
        {
            $h = 0;
            $m = 0;
            $tab_time = explode(':', $_POST['time']);
            $htime = $tab_time[0];
            $mtime = $tab_time[1];
            $heure_fixe = 1;
        }
        else
        {
            $h = $db->SQLFix($_POST['h']);
            $m = $db->SQLFix($_POST['m']);
            $htime = '';
            $mtime = '';
            $heure_fixe = 0;
        }
    }

    $sql = 'UPDATE automatismes SET id_sms="'.$id_sms.'", id_mail="'.$id_mail.'", type_atelier_rdv="'.$type_atelier_rdv.'", action="'.$action.'", j="'.$j.'", h="'.$h.'", m="'.$m.'", heurefixe="'.$heure_fixe.'", htime="'.$htime.'", mtime="'.$mtime.'", immediat="'.$immediat.'", dest_client_staff="'.$dest.'"
            WHERE compte_principal="'.$_SESSION['compte_principal'].'"
            AND id ="'.$id.'"
            ';
    $db->Query($sql);
    header('location: ./automatismes');
    die();

}


/*
 * Ajout automastime
 */
if(isset($_POST['type_atelier_rdv']) AND is_numeric($_POST['type_atelier_rdv']) AND !(isset($_POST['id']))) {

    $type_atelier_rdv = $db->SQLFix($_POST['type_atelier_rdv']);
    $action = $db->SQLFix($_POST['action']);

    $id_sms = 0;
    $id_mail = 0;
    if($_POST['type_notif'] == 'liste_sms')
    {
        $id_sms = $db->SQLFix($_POST['id_sms']);
        $dest = 1;
    }

    if($_POST['type_notif'] == 'liste_mail')
    {
        $id_mail = $db->SQLFix($_POST['id_mail']);
        if($_POST['dest'] == '1')
            $dest = 1;
        else
            $dest = 2;
    }

    if(!$_POST['planned'] == 'on')
    {
        $immediat = 1;
        $j = 0;
        $h = 0;
        $m = 0;
        $htime = '';
        $mtime = '';
    }
    else
    {
        $immediat = 0;
        $j = $db->SQLFix($_POST['j']);
        if($_POST['heurefixe'] == 'on')
        {
            $h = 0;
            $m = 0;
            $tab_time = explode(':', $_POST['time']);
            $htime = $tab_time[0];
            $mtime = $tab_time[1];
            $heure_fixe = 1;
        }
        else
        {
            $h = $db->SQLFix($_POST['h']);
            $m = $db->SQLFix($_POST['m']);
            $htime = '';
            $mtime = '';
            $heure_fixe = 0;
        }
    }

    $sql = 'INSERT INTO automatismes (id_sms, id_mail, type_atelier_rdv, action, j, h, m, heurefixe, htime, mtime, immediat, dest_client_staff, compte_principal)
            VALUES ("'.$id_sms.'", "'.$id_mail.'", "'.$type_atelier_rdv.'", "'.$action.'", "'.$j.'", "'.$h.'", "'.$m.'", "'.$heure_fixe.'", "'.$htime.'", "'.$mtime.'", "'.$immediat.'", "'.$dest.'", "'.$_SESSION['compte_principal'].'")
            ';
    $db->Query($sql);
    header('location: ./automatismes');
    die();
}
?>