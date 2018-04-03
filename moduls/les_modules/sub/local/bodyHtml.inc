<?php session_start();

    if(right('lesmodules', -1))
    {
        $Directory = './moduls';
	$MyDirectory = opendir($Directory) or die('Modul access Error');
        $modules = Array();
 	while($Entry = @readdir($MyDirectory)) 
 	{
  	
  		if(is_dir($Directory.'/'.$Entry) && $Entry[0] != '_' && $Entry != '.' && $Entry != '..' && $Entry != 'virgin') 
  		{
                    
                    $modules[] = $Entry;
                    
  		}
  	}
        
        sort($modules);
        
        $i=0;
        $tab_modules = Array();
        foreach($modules AS $module)
        {
            $tab_modules[$i]['nom'] = $module;
            
            global $db;
            $db->Query('SELECT alias, nom_propre, prix, type, FROM modules_comptes WHERE nom="'.$module.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row=$db->Row();
            
            $tab_modules[$i]['alias'] = $row->alias;
            $tab_modules[$i]['nom_propre'] = $row->nom_propre;
            $tab_modules[$i]['prix'] = $row->prix;
            $tab_modules[$i]['type'] = $row->type;
            
            $i++;
        }
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/les_modules_compte_courant.php'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/sub/les_modules_compte_courant.php');
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