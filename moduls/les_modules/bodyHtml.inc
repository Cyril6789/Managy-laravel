<?php session_start();

    if(right('les_modules', -1))
    {
        $Directory = './moduls';
	$MyDirectory = opendir($Directory) or die('Modul access Error');
        $tab_modules = Array();
 	while($Entry = @readdir($MyDirectory)) 
 	{
  	
  		if(is_dir($Directory.'/'.$Entry) && $Entry[0] != '_' && $Entry != '.' && $Entry != '..' && $Entry != 'virgin') 
  		{
                    
                        
                    $tab_modules[] = $Entry;
                    
  		}
  	}
        
        sort($tab_modules);
         
        
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/les_modules.php'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/les_modules.php');
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