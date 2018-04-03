<?php session_start();

function count_constant_lang($lang)
    {
        
        $counter = 0;
        $path = 'moduls';
        
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

                                $constante = str_replace('\'', '', trim($before));
                                $constante = str_replace('"', '', $constante);

                                if($constante != '')
                                   $counter++;

                            }
                        }
                        fclose($f);
                    }
                }

            }
        }
        
        $path = 'constructPage';
        
        $Directory = './'.$path;
        $MyDirectory = opendir($Directory);
        while($Entry = @readdir($MyDirectory)) 
        {

            if(is_dir($Directory.'/'.$Entry) && $Entry[0] != '_' && $Entry != '.' && $Entry != '..' && $Entry != 'virgin') 
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
                           
                            $constante = str_replace('\'', '', trim($before));
                            $constante = str_replace('"', '', $constante);

                            if($constante != '')
                               $counter++;

                        }
                    }
                    fclose($f);
                }

            }
        }
        
        return $counter;
            
    }

    if(is_array($_SESSION['tab_lang'])) {
        if (in_array(strtoupper(HL), $_SESSION['tab_lang']))
            $difference = count_constant_lang('fr') - count_constant_lang(HL);
        else
            $difference = 0;
    }
?>