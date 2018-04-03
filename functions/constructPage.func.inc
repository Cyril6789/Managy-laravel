<?php session_start();
include('./constructPage/settings.gen.inc');
function constructPage()
{
	$Directory = './constructPage';
	$MyDirectory = opendir($Directory); // or die('Construct page access Error');
 	while($Entry = @readdir($MyDirectory)) 
 	{
  	
  		if(is_dir($Directory.'/'.$Entry)&& $Entry != '.' && $Entry != '..' && $Entry != 'virgin') 
  		{
  			require_once($Directory.'/'.$Entry.'/settings.mod.inc');
			require_once($Directory.'/'.$Entry.'/classes/construct.class.inc');
			if(is_file($Directory.'/'.$Entry.'/langs/'.HL.'.inc'))
				require_once($Directory.'/'.$Entry.'/langs/'.HL.'.inc');
                        if(is_file($Directory.'/'.$Entry.'/langs/fr.inc'))
							require_once($Directory.'/'.$Entry.'/langs/fr.inc');
                        if(is_file($Directory.'/'.$Entry.'/langs/'.HL.'.inc'))
							require_once($Directory.'/'.$Entry.'/langs/'.HL.'.inc');

				
  		}
  	}
}

?>