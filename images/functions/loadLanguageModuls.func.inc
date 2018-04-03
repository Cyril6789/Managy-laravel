<?php session_start();

function loadLanguageModuls()
{
	$Directory = './moduls';
	$MyDirectory = opendir($Directory) or die('Modul access Error');
 	while($Entry = @readdir($MyDirectory)) 
 	{
  	
  		if(is_dir($Directory.'/'.$Entry) && $Entry[0] != '_' && $Entry != '.' && $Entry != '..' && $Entry != 'virgin') 
  		{
                    if(is_file($Directory.'/'.$Entry.'/langs/'.HL.'.inc'))
                        include($Directory.'/'.$Entry.'/langs/'.HL.'.inc');
			
                    
                    $SubDirectory = './moduls/'.$Entry.'/sub';
                    $MySubDirectory = opendir($SubDirectory) or die('Modul access Error');
                    while($SubEntry = @readdir($MySubDirectory)) 
                    {

                            if(is_dir($SubDirectory.'/'.$SubEntry) && $SubEntry[0] != '_' && $SubEntry != '.' && $SubEntry != '..' && $SubEntry != 'virgin') 
                            {
                                if(is_file($Directory.'/'.$SubEntry.'/langs/'.HL.'.inc'))
                                    include($Directory.'/'.$SubEntry.'/langs/'.HL.'.inc');

                            }
                    }
  		}
  	}
}

?>