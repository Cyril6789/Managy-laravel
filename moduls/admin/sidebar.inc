<?php session_start();
    
    if(!is_array($sidebar['admin']))
        $sidebar['admin'] = Array();
    $sidebar['admin']['first'] = 'gear|Administration';
    $sidebar['admin'][] = 'admin|-1|'.ADMIN_NOTIFS_MAIL_SB_SEE.'|admin-notifys_mail';
    $sidebar['admin'][] = 'admin|-1|'.ADMIN_CGU_SB_SEE.'|admin-cgu';
    $sidebar['admin'][] = 'admin|-1|'.ADMIN_DESCRIPTION_MODULES_SB_SEE.'|admin-modules';


?>
