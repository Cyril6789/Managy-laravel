<?php session_start();

    if(!is_array($sidebar['inter']))
        $sidebar['inter'] = Array();
    $sidebar['inter']['first'] = 'wrench|'.INTERVENTION_SB_INTERVENTIONS;
    $sidebar['inter'][] = 'inter|2|'.INTERVENTION_SB_NEW_INTER.'|intervention-new';
    $sidebar['inter'][] = 'inter|7|'.INTERVENTION_SB_INTER_ALL.'|intervention-all';
    if(!is_array($sidebar['admin']))
        $sidebar['admin'] = Array();
    $sidebar['admin'][] = 'inter|4|'.INTERVENTION_SB_MATIERES.'|intervention-matieres';
    $sidebar['admin'][] = 'inter|5|'.INTERVENTION_SB_ETAPES.'|intervention-etapes';
    $sidebar['admin'][] = 'inter|6|'.INTERVENTION_SB_TRAITEMENTS.'|intervention-traitements';
    

?>
