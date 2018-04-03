<?php session_start();
    if(!is_array($sidebar['dash']))
        $sidebar['dash'] = Array();
    $sidebar['dash']['first'] = 'dashboard|'.DASH_SB_GENERAL;    
    $sidebar['dash'][] = 'dash|1|'.DASH_SB_HOME.'|dashboard';
    

?>
