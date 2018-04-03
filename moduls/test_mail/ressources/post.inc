<?php session_start();
 //GESTION DES POSTS
 if(isset($_POST['mail']))
 {
     $email = new Maileur($_POST['sujet']);
     $email->AddTitle($_POST['titre']);
     $email->addDest($_POST['mail']);
     $email->addDest('contact@depaninfo67.com');
     $email->addExpediteur('holtzmannetfils@wanadoo.fr', 'Société Holtzmann et Fils');
     $email->setLogo('di67');
     $email->addSignature('Cyril Heilmann', 'www.depaninfo67.com', 'contact@depaninfo67.com', '06.87.47.00.91');
     $email->body($_POST['texte']);
     $email->send();
 }
?>