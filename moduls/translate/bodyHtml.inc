<?php session_start();


    $trad_tab = Array();
    $titre_trad_page = Array();
    
    function fill_tab($lang, $path='moduls')
    {
        global $trad_tab;
        global $titre_trad_page;
        
        $Directory = './'.$path;
        $MyDirectory = opendir($Directory);
        while($Entry = @readdir($MyDirectory)) 
        {

            if(is_dir($Directory.'/'.$Entry) && $Entry[0] != '_' && $Entry != '.' && $Entry != '..' && $Entry != 'virgin') 
            {
                if(AccesActivableModul($Entry))
                {
                    if(is_file($Directory.'/'.$Entry.'/langs/'.$lang.'.inc'))
                    {
                        $file = $Directory.'/'.$Entry.'/langs/'.$lang.'.inc';
                        $f = fopen($file, r);
                        $tab = explode("\n", fread($f, filesize($file)));
                        foreach($tab AS  $value)
                        {   $value = str_replace('<?php', '', $value);
                            $value = str_replace('?>', '', $value);
                            $value = str_replace('define(', '', $value);
                            $value = str_replace(');', '', $value);

                            $value_array = str_split($value);
                            if(!in_array('$', $value_array)) // Suppression de la derniere ligne $page_title = '';
                            {

                                list($before, $after) = explode(',', $value, 2);
                                $valeur = substr(trim(stripcslashes($after)), 1, -1);
                                $constante = str_replace('\'', '', trim($before));
                                $constante = str_replace('"', '', $constante);

                                if($constante != '')
                                   $trad_tab[$path.'/'.$Entry][$constante][$lang] = $valeur;

                            }
                            else //recupération du nom de la constante correspondant au titre de la page
                            {
                               if($lang == 'fr')
                                   $titre_trad_page[$path.'/'.$Entry] = str_replace(';', '', trim(str_replace('=', '', trim(str_replace('$page_title','', trim($value))))));
                            }
                        }
                        fclose($f);
                    }
                }

            }
        }
            
    }


    $template_name = TEMPLATE_NAME;
    if(is_dir('./templates/'.TEMPLATE_NAME.'/pages/'))
    {
            ob_start();
            
            
            //foreach ($_SESSION['tab_lang'] AS $value)
            //{
                fill_tab('fr');
                if(HL != 'fr')
                  fill_tab(strtolower(HL));
                
            //}
            
            //foreach ($_SESSION['tab_lang'] AS $value)
            //{
                fill_tab('fr', 'constructPage');
                if(HL != 'fr')
                  fill_tab(strtolower(HL), 'constructPage');
                
            //}
            global $trad_tab;
            global $titre_trad_page;
            
            
            
            
            
            
            include('./templates/'.TEMPLATE_NAME.'/pages/translate.php');
            $ModulContent = ob_get_contents();
            ob_end_clean();
    }
	

?>