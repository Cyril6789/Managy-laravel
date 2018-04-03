<?php session_start();
        
        
     
     $multi_inter = true;    
     
     
     //Titre
     $nums = $_GET['ids_inters'];
     $tab_num = explode('-', $nums);
     
     
     foreach ($tab_num as $id_inter)
     {
        include './moduls/intervention/bodyHtml.inc';
     }

	

?>