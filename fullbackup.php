<?php

function zipDir($path,&$zip, $firstcall = true)
{
            if (!is_dir($path)) return;
 
           if (!($dh = @opendir($path)))
           {
                  echo("<b>ERREUR: Une erreur s'est produite sur ".$path."</b><br />");
                  return;
           }
                     while ($file = readdir($dh))
                     {
 
                              if ($file == "." || $file == "..") continue;
                              if (is_dir($path."/".$file))
                              {       // fonction recursive
                                      zipDir($path."/".$file,$zip, false);
 
                              }
                              elseif (is_file($path."/".$file))
                               {      // c'est si un fichier, on le rajoute au zip
                                      $zip->addFile(file_get_contents($path."/".$file),$path."/".$file);
                               }
                     }
 
          // si on est dans l'appel parent (premier appel)
          if($firstcall == true)
          {
               echo "Sauvegarde effectuee";
          }
}


$zip= new zipfile;
$path = '../';            // repertoire que l'on veut zipper
 
zipDir($path,$zip);
$filezipped=$zip->file();       // On recupere le contenu du zip dans la variable $filezipped
$open = fopen($fichier_zip, "w");    // On la sauvegarde dans le meme repertoire que les fichiers a zipper
fwrite($open, $filezipped);
fclose($open);

?>