<?php session_start();

    if(!is_array($sidebar['inter']))
        $sidebar['inter'] = Array();
    $sidebar['inter']['first'] = 'block|'.INTERVENTION_SB_INTERVENTIONS;
    $sidebar['inter'][] = 'inter|2|'.INTERVENTION_SB_NEW_INTER.'|intervention-new';
    $sidebar['inter'][] = 'inter|7|'.INTERVENTION_SB_INTER_ALL.'|intervention-all';

?>
