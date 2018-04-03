<?php session_start();


    include('./moduls/translate/ressources/count_translate_missing.inc');
    
    if(!is_array($sidebar['admin']))
        $sidebar['admin'] = Array();
    $sidebar['admin'][] = 'translate|1|'.TRANS_SB_TRANSLATE.'|translate|'.$difference;

    

?>
