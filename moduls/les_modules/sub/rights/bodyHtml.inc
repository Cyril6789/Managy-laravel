<?php session_start();

    if(right('lesmodules', -1))
    {

        $page_title_def = $page_title;
        global $db;
        $sql = 'SELECT nom FROM modules_comptes_types WHERE type > "0"';
        $db->Query($sql);
        
            echo $row->nom.' <br />';

        $Directory = './moduls';
        $MyDirectory = opendir($Directory) or die('Modul access Error');
        while($row = $db->Row()) {
            if (is_dir($Directory . '/' . $row->nom) && $row->nom[0] != '_' && $row->nom != '.' && $row->nom != '..' && $row->nom != 'virgin') {
                if (1){ //AccesActivableModul($row->nom)) {
                    if (is_file($Directory . '/' . $row->nom . '/rights.settings.inc'))
                        include($Directory . '/' . $row->nom . '/rights.settings.inc');
                    if (is_file($Directory . '/' . $row->nom . '/langs/fr.inc'))
                        include($Directory . '/' . $row->nom . '/langs/fr.inc');
                    $SubDirectory = $Directory . '/' . $row->nom . '/sub';

                    if (is_dir($SubDirectory)) {
                        $MySubDirectory = opendir($SubDirectory) or die('Modul access Error');
                        while ($SubEntry = @readdir($MySubDirectory))
                            if (is_dir($SubDirectory . '/' . $SubEntry) && $SubEntry[0] != '_' && $SubEntry != '.' && $SubEntry != '..' && $SubEntry != 'virgin')
                            {
                                if (is_file($SubDirectory . '/' . $SubEntry . '/rights.settings.inc'))
                                    include($SubDirectory . '/' . $SubEntry . '/rights.settings.inc');
                                if (is_file($SubDirectory . '/' . $SubEntry . '/langs/fr.inc'))
                                    include($SubDirectory . '/' . $SubEntry . '/langs/fr.inc');
                            }
                    }
                }
            }
        }

        $tab_rights = array();


        $sql = 'SELECT modul, num FROM rights_staff_types';
        $db->Query($sql);
        $tab_bdd = Array();
        While($row = $db->Row())
            $tab_bdd[] = $row->modul.'_'.$row->num;

        foreach ($rights AS $krights => $vrights) // droits par modules
        {
            foreach ($vrights AS $kvrights => $vvrights)
            {
                $tab_rights[$krights][$kvrights]['value'] = $rights[$krights][$kvrights];

                if(in_array($krights.'_'.$kvrights, $tab_bdd))// right($krights, $kvrights, $id_staff))
                    $tab_rights[$krights][$kvrights]['checked'] = true;
                else
                    $tab_rights[$krights][$kvrights]['checked'] = false;
            }
        }
        

        $page_title = $page_title_def;
        
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/rights_types.php'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/sub/rights_types.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
        }
        
    }
    else
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/no_right.php'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/no_right.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
        }
    }
?>