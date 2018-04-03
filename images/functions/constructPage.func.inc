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
  			include($Directory.'/'.$Entry.'/settings.mod.inc');
  			include($Directory.'/'.$Entry.'/classes/construct.class.inc');
			if(is_file($Directory.'/'.$Entry.'/langs/'.HL.'.inc'))
				include($Directory.'/'.$Entry.'/langs/'.HL.'.inc');

				
  		}
  	}
}

?>