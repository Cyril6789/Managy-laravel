<?php session_start();
//mail
if(isset($_POST['mail']))
{
    $email = new Maileur($_POST['sujet']);
    $email->AddTitle($_POST['titre']);
    $email->addDest($_POST['mail']);
    $email->addDest('contact@depaninfo67.com');
    $email->addDest(MAIL_CONTACT);
    $email->addExpediteur(MAIL_CONTACT, NOM_SOCIETE);
    $email->setLogo(LOGO);
    $email->addSignature(NOM_SOCIETE, WEB, MAIL_CONTACT, TEL);
    $email->body($_POST['message']);
    $email->send();
}

if(isset($_POST['sms']))
{
    //print_r($_POST);
//    $message = $_POST['texte']."\nHoltzmann Et Fils";
//    $numero = $_POST['num'];
////    $numero = str_replace('+', '', $numero);
////    $numero = str_replace('(0)', '', $numero);
////    $numero = str_replace(' ', '', $numero);
//    echo $numero;
//    //echo $message;
//            
//    die();
    $sms = new Smseur($_POST['texte']);
    $sms->AddExpediteur('HoltzmannEtFils');
    $sms->AddNumero($_POST['num']);
    $sms->AddSignature('Holtzmann et Fils');
    $sms->envoie();
    $erreur = $sms->getError();
    if($erreur == 1)
        $_SESSION['erreur_sms'] = 1;
    
}




if($_POST['submit_projet'])
{
    global $db;
    if(is_numeric($_POST['id_inter']))
    {
        $id_inter = $db->SQLfix($_POST['id_inter']);
        $chantier = $db->SQLfix($_POST['nom_chantier']);
        $sql = 'UPDATE interventions SET projet="'.$chantier.'" WHERE id_inter = "'.$id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ';
      
        $db->Query($sql);
        $log = New addLog('a modifié le nom du chantier');
        $log->setInter($id_inter);
        $log->insert();
        changements_interventions($id_inter);
        
    }
    
}



//Post chat
if(!empty($_POST['message_chat']))
{
   if($_POST['message_chat'] != '')
   {
        global $db;
        $id_inter = (int) $db->SQLfix($_GET['id_inter']);
        $message = $db->SQLfix($_POST['message_chat']);
        $db->Query('INSERT INTO chat_inter (id_inter, id_staff, message, timestamp, compte_principal) VALUES ("'.$id_inter.'", "'.$_SESSION['id'].'", "'.$message.'", "'.time().'", "'.$_SESSION['compte_principal'].'" ) ');
        changements_interventions($id_inter);
   }
}



//post atelier ou site
if(!empty($_POST['atelier']) OR !empty($_POST['site']))
{

    global $db;
    $id_inter = (int) $db->SQLfix($_GET['id_inter']);
    $descr = $db->SQLFix($_POST['descr']);
    $heures = $db->SQLFix($_POST['heures']);
    $minutes = $db->SQLFix($_POST['minutes']);
    $date = $db->SQLFix($_POST['date']);
    $tab_date = explode('/', $date);
    $date_finale = mktime(0,0,0,$tab_date['1'], $tab_date['0'], $tab_date['2']);
    if($_POST['atelier']) {
        $type = "1";
        $saisie = "atelier";
    }
    if($_POST['site']) {
        $type = "2";
        $saisie = "site";
    }
    $sql = 'INSERT INTO releves_horaires (id_staff, id_inter, description, date, heures, minutes, type_atelier_site, compte_principal) VALUES ("'.$_SESSION['id'].'", "'.$id_inter.'", "'.$descr.'", "'.$date_finale.'", "'.$heures.'", "'.$minutes.'", "'.$type.'", "'.$_SESSION['compte_principal'].'" ) ';
    $db->Query($sql);


    $log = New addLog('a ajouté une saisie horaire '.$saisie);
    $log->setInter($id_inter);
    $log->insert();
    header('Location: intervention-'.$id_inter);

}


?>