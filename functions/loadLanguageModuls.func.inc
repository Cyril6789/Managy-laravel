<?php session_start();

function loadLanguageModuls()
{
	$Directory = './moduls';
	$MyDirectory = opendir($Directory) or die('Modul access Error');
 	while($Entry = @readdir($MyDirectory)) 
 	{
  	
  		if(is_dir($Directory.'/'.$Entry) && $Entry[0] != '_' && $Entry != '.' && $Entry != '..' && $Entry != 'virgin') //modules
  		{
                    if(AccesActivableModul($Entry))
                    {
                        if(is_file($Directory.'/'.$Entry.'/langs/'.HL.'.inc'))
                            include($Directory.'/'.$Entry.'/langs/'.HL.'.inc');
                        if(is_file($Directory.'/'.$Entry.'/langs/fr.inc'))
                            include($Directory.'/'.$Entry.'/langs/fr.inc');
                        if(is_file($Directory.'/'.$Entry.'/langs/'.HL.'.inc'))
                            include($Directory.'/'.$Entry.'/langs/'.HL.'.inc');

                        $SubDirectory = $Directory.'/'.$Entry.'/sub';    

                        if(is_dir($SubDirectory))
                        { 
                            $MySubDirectory = opendir($SubDirectory) or die('Modul access Error');
                            while($SubEntry = @readdir($MySubDirectory)) 
                            {

                                    if(is_dir($SubDirectory.'/'.$SubEntry) && $SubEntry[0] != '_' && $SubEntry != '.' && $SubEntry != '..' && $SubEntry != 'virgin') //sous_modules
                                    {
                                        if(is_file($SubDirectory.'/'.$SubEntry.'/langs/'.HL.'.inc'))
                                            include($SubDirectory.'/'.$SubEntry.'/langs/'.HL.'.inc');
                                        if(is_file($SubDirectory.'/'.$SubEntry.'/langs/fr.inc'))
                                            include($SubDirectory.'/'.$SubEntry.'/langs/fr.inc');
                                        if(is_file($SubDirectory.'/'.$SubEntry.'/langs/'.HL.'.inc'))
                                            include($SubDirectory.'/'.$SubEntry.'/langs/'.HL.'.inc');

                                    }
                            }
                        }
                    }
  		}
  	}
}

?>