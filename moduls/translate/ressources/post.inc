<?php session_start();
 $trad_tab_post = Array();
  
 foreach ($_POST AS $key_p => $value_p)
 {
     
     $tab_key_p = explode('_', $key_p);
     if($tab_key_p[0] == 'title')//Verification "title_xxx";
         $modul_title[$tab_key_p[1]] = $value_p;
     else //c'est une definition de constante
     {
         $tab_key_p = explode('|', $key_p);
         $trad_tab_post[$tab_key_p[0]][$tab_key_p[1]][$tab_key_p[2]] = $value_p;
     }
     
     
 }
 
  //print_r($trad_tab_post);
 //Création des fichiers de langues
 
 
 foreach ($trad_tab_post AS $key_cc => $value_cc) //modules
    { 
        
        $file = './'.$key_cc.'/langs/'.HL.'.inc';
        if(is_dir('./'.$key_cc.'/langs/'))
        {
        
            $line_file = "<?php\n"
                    ;
            foreach ($value_cc AS $key_dd => $value_dd)
            {
                if($value_dd[HL] != '')
                    $line_file .= "define(".$key_dd.", '".addslashes (str_replace ('$', '', $value_dd[HL]))."');\n";

            }
            
            $temp = explode('/', $key_cc);
            
            if(trim($temp[0]) == 'moduls' AND !empty($modul_title[$key_cc]))
                $line_file .= '$page_title = '.$modul_title[$key_cc].";\n";
            
            $line_file .= "?>";

            file_put_contents($file, $line_file);
        }
        
    }

?>