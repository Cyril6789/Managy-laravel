<?php session_start();

    if(!is_array($sidebar['lesmodules']))
        $sidebar['lesmodules'] = Array();
    $sidebar['lesmodules']['first'] = 'sitemap|'.LES_MODULES_SB_MODULES;
    $sidebar['lesmodules'][] = 'osef|-1|'.LES_MODULES_SB_MODULES.'|les_modules';
    $sidebar['lesmodules'][] = 'osef|-1|Compte en cours|les_modules-local';
    $sidebar['lesmodules'][] = 'osef|-1|Compte type|les_modules-type';
    $sidebar['lesmodules'][] = 'osef|-1|Droits types|les_modules-rights';
    $sidebar['lesmodules'][] = 'osef|-1|Matériels types|intervention-materiels_types';
    $sidebar['lesmodules'][] = 'osef|-1|OS types|intervention-se_types';
    $sidebar['lesmodules'][] = 'osef|-1|Antivirus types|intervention-antivirus_types';
    $sidebar['lesmodules'][] = 'osef|-1|Préstations types|intervention-prestations_types';
?>
