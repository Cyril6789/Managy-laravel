<?php session_start();
function loadRightsSettings()
{
	$Directory = './moduls';
	$MyDirectory = opendir($Directory) or die('Modul access Error');
 	while($Entry = @readdir($MyDirectory)) {
        if (is_dir($Directory . '/' . $Entry) && $Entry[0] != '_' && $Entry != '.' && $Entry != '..' && $Entry != 'virgin') {
            if (AccesActivableModul($Entry)) {
                if (is_file($Directory . '/' . $Entry . '/rights.settings.inc'))
                    include($Directory . '/' . $Entry . '/rights.settings.inc');

                $SubDirectory = $Directory . '/' . $Entry . '/sub';

                if (is_dir($SubDirectory)) {
                    $MySubDirectory = opendir($SubDirectory) or die('Modul access Error');
                    while ($SubEntry = @readdir($MySubDirectory))
                        if (is_dir($SubDirectory . '/' . $SubEntry) && $SubEntry[0] != '_' && $SubEntry != '.' && $SubEntry != '..' && $SubEntry != 'virgin')
                            if (is_file($SubDirectory . '/' . $SubEntry . '/rights.settings.inc'))
                                include($SubDirectory . '/' . $SubEntry . '/rights.settings.inc');
                }
            }
        }
    }
    return $rights;
}
?>