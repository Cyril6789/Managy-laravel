<?php session_start();
//mail
if(isset($_POST['mail']))
{
    $db = new MySQL();
    $sql = 'SELECT id_client FROM interventions WHERE id_inter="'.$id_inter.'" AND compte_principal = "'.$_SESSION['compte_principal'].'"';
    $db->Query($sql);
    $row = $db->Row();

    $email = new Maileur($_POST['sujet']);
    $email->AddTitle($_POST['titre']);
    $email->withAck($row->id_client, $_POST['titre']);
    $email->addDest($_POST['mail']);
   // $email->addDest('contact@depaninfo67.com');
    $email->addDest(MAIL_CONTACT);
    $email->addExpediteur(MAIL_CONTACT, NOM_SOCIETE);
    $email->setLogo($_SESSION['compte_principal']);
    $email->addSignature(NOM_SOCIETE, WEB, MAIL_CONTACT, TEL);
    $email->body($_POST['message']);
    $email->send();
    $log = New addLog('a envoyé un mail au client');
    $log->setInter($id_inter);
    $log->insert();
    header('Location: ./i'.$id_inter.'#tab_intervention_customer');
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
    $log = New addLog('a envoyé un SMS au client');
    $log->setInter($id_inter);
    $log->insert();
    header('Location: ./i'.$id_inter.'#tab_intervention_customer');
    
}



//SAVE
if(!empty($_POST['type']))
{
   
    $t = explode('_', $_POST['type']);
    global $db;
    $id_inter = (int) $db->SQLfix($t[1]);
    
    $db->Query('SELECT COUNT(*) AS nb FROM prise_en_charge WHERE id_intervention = "'.$id_inter.'" AND id_staff = "'.$_SESSION['id'].'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
    $row = $db->Row();
    $pris_en_charge = $row->nb;
    
    if($pris_en_charge)
    {
        $db->Query('SELECT time_cloture, quantite_couleur, id_couleur, id_finition FROM interventions WHERE id_inter="'.$id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
        $row = $db->Row();

        if($row->time_cloture)
            $cloture = 1;
        else
            $cloture = 0;

        if(!$cloture)
        {
            $qte_couleur = $db->SQLfix($_POST['i'.$id_inter.'_qtecouleur']);
            $quantite_stock =  $row->quantite_couleur - $qte_couleur;
            //maj stock
            $db->Query('UPDATE stocks_couleurs SET stock = stock + "'.$quantite_stock.'" WHERE id_couleur="'.$row->id_couleur.'" AND id_aspect = "'.$row->id_finition.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            //echo $db->Error();
            
            $nb_balancelle = $db->SQLfix($_POST['i'.$id_inter.'_balancelle']);
            $accroche = $db->SQLfix($_POST['i'.$id_inter.'_accroche']);
            $emballage = $db->SQLfix($_POST['i'.$id_inter.'_emballage']);
            $comments = $db->SQLfix($_POST['i'.$id_inter.'_comments']);
            $application = $db->SQLfix($_POST['i'.$id_inter.'_application']);
            
            $db->Query('SELECT id_traitement FROM traitements_interventions WHERE id_inter ="'.$id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            while($row = $db->Row())
            {
                $db2 = new MySQL();
                $traitement_time = $db2->SQLFix($_POST['i'.$id_inter.'_traitement'.$row->id_traitement]);
                
                $db2->Query('UPDATE traitements_interventions SET duree = "'.$traitement_time.'" WHERE id_traitement="'.$row->id_traitement.'" AND id_inter="'.$id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                //echo $db2->Error();
            }
            
            
            $db->Query('SELECT id_etape FROM etapes_interventions WHERE id_inter ="'.$id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            echo $db->Error();
            while($row = $db->Row())
            {
                $db2 = new MySQL();
                $etape_time = $db2->SQLFix($_POST['i'.$id_inter.'_etape'.$row->id_etape]);
                $etape_type = $db2->SQLFix($_POST['i'.$id_inter.'_typeetape'.$row->id_etape]);
                
                $db2->Query('UPDATE etapes_interventions SET duree = "'.$etape_time.'", type="'.$etape_type.'" WHERE id_etape="'.$row->id_etape.'" AND id_inter="'.$id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                //echo 'UPDATE etapes_interventions SET duree = "'.$etape_time.'", type="'.$etape_type.'" WHERE id_etape="'.$row->id_etape.'" AND id_inter="'.$id_inter.'" ';
                //echo $db2->Error();
            }
            
            $sql = 'UPDATE interventions
                        SET quantite_couleur="'.$qte_couleur.'", 
                        nombre_balancelle="'.$nb_balancelle.'", 
                        temps_accroche_decroche="'.$accroche.'", 
                        commentaires="'.$comments.'",
                        temps_application ="'.$application.'",
                        temps_emballage = "'.$emballage.'"';
            if($t[0] == "cloture")
            {
                $sql .= ' , time_cloture = '.time().' ';




                    $envoie_mail = true;

                
                $log = New addLog('a clôturé l\'intervention');
                $log->setInter($id_inter);
                $log->insert();
                
                changements_interventions($id_inter);
            }
            else
            {
                $log = New addLog('a sauvegardé une saisie');
                $log->setInter($id_inter);
                $log->insert();
                changements_interventions($id_inter);
            }
            $sql .= ' WHERE id_inter="'.$id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ';

            $db->Query($sql);

            if($envoie_mail)
            {
                $email = new Maileur('Managy : Clôture Intervention N°'.$id_inter);
                $email->AddTitle('Clôture Intervention N°'.$id_inter);
                $email->addDest(MAIL_CONTACT);
               // $email->addDest('contact@depaninfo67.com');
                $email->body('Bonjour !<br />'.$_SESSION['prenom'].' vient de clôturer l\'intervention N°'.$id_inter);
                $email->addOfPeinture($id_inter);
                $sql = 'SELECT mail_notification_cloture FROM comptes_principaux WHERE id="'.$_SESSION['compte_principal'].'"';

                $db->Query($sql);
                $row = $db->Row();
                $mail_notification_cloture = $row->mail_notification_cloture;

                if($mail_notification_cloture)
                    $email->send();
            }



            echo $db->Error();
            
            
        }
        header('Location: ./i'.$id_inter);
    }
    
}

//FIN SAVE



//Modifier l'intervention
if($_POST['submit_traitements'])
{
    global $db;
    $db->Query('SELECT id, nom FROM traitements WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');
    while($row = $db->Row())
    {
        if(is_numeric($_POST['id_inter']))
        {
            $id_inter = $db->SQLfix($_POST['id_inter']);
            if($_POST['traitement_'.$row->id] != 'on') //supprimer 
            {
               $db2 = new MySQL();
               $db2->Query('DELETE FROM traitements_interventions WHERE id_inter = "'.$id_inter.'" AND id_traitement = "'.$row->id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
               
                $log = New addLog('a supprimer le traitement '.$row->nom);
                $log->setInter($id_inter);
                $log->insert();
                changements_interventions($id_inter);
            }
            else
            {
                $db2 = new MySQL();
                $db2->Query('SELECT COUNT(*) AS nb FROM traitements_interventions WHERE id_inter = "'.$id_inter.'" AND id_traitement = "'.$row->id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                $row2 = $db2->Row();
                if(!$row2->nb)
                {
                    $db2->Query('INSERT INTO traitements_interventions (id_inter, id_traitement, compte_principal) VALUES ("'.$id_inter.'", "'.$row->id.'", "'.$_SESSION['compte_principal'].'" ) ');
                    $log = New addLog('a ajouté le traitement '.$row->nom);
                    $log->setInter($id_inter);
                    $log->insert();
                    changements_interventions($id_inter);
                }    
            }
            header('Location: ./i'.$id_inter.'#tab_intervention_edit');
        }
    }
}

if($_POST['submit_etapes'])
{
    global $db;
    $db->Query('SELECT id, nom FROM etapes WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');
    while($row = $db->Row())
    {
        if(is_numeric($_POST['id_inter']))
        {
            $id_inter = $db->SQLfix($_POST['id_inter']);
            if($_POST['etape_'.$row->id] != 'on') //supprimer 
            {
                $db2 = new MySQL();
                $db2->Query('DELETE FROM etapes_interventions WHERE id_inter = "'.$id_inter.'" AND id_etape = "'.$row->id.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" '); 
                $log = New addLog('a supprimer l\'étape '.$row->nom);
                $log->setInter($id_inter);
                $log->insert();
                changements_interventions($id_inter);
            }
            else
            {
                $db2 = new MySQL();
                $db2->Query('SELECT COUNT(*) AS nb FROM etapes_interventions WHERE id_inter = "'.$id_inter.'" AND id_etape = "'.$row->id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                $row2 = $db2->Row();
                if(!$row2->nb)
                {
                    $db2->Query('INSERT INTO etapes_interventions (id_inter, id_etape, compte_principal) VALUES ("'.$id_inter.'", "'.$row->id.'", "'.$_SESSION['compte_principal'].'" ) ');
                    $log = New addLog('a ajouté l\'étape '.$row->nom);
                    $log->setInter($id_inter);
                    $log->insert();
                    changements_interventions($id_inter);
                }    
            }
            header('Location: ./i'.$id_inter.'#tab_intervention_edit');
        }
    }
}


if($_POST['submit_projet'])
{
    global $db;
    if(is_numeric($_POST['id_inter']))
    {
        $id_inter = $db->SQLfix($_POST['id_inter']);
        $projet = $db->SQLfix($_POST['nom_projet']);
        $id_matiere = $db->SQLfix($_POST['id_matiere']);
        $ref_chantier = $db->SQLfix($_POST['ref_chantier']);
        $sql = 'UPDATE interventions SET projet="'.$projet.'", ref_chantier="'.$ref_chantier.'", id_matiere="'.$id_matiere.'" WHERE id_inter = "'.$id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ';
      
        $db->Query($sql);
        $log = New addLog('a modifié le nom du projet et/ou la matière de l\'intervention');
        $log->setInter($id_inter);
        $log->insert();
        changements_interventions($id_inter);

        header('Location: ./i'.$id_inter.'#tab_intervention_edit');
        
    }
    
}


if($_POST['id_couleur'])
{
    if(is_numeric($_POST['id_couleur']) AND is_numeric($_POST['id_finition']))
    {
        if(is_numeric($_POST['id_inter']))
        {
            global $db;
            $id_inter = $db->SQLfix($_POST['id_inter']);
            $id_couleur = $db->SQLfix($_POST['id_couleur']);
            $id_finition = $db->SQLfix($_POST['id_finition']);
            if($_POST['deconditonne'] == 'on')
                $deconditonne = 1;
            else
                $deconditonne = 0;
            
            
            $db->Query('UPDATE interventions SET id_couleur = "'.$id_couleur.'", id_finition = "'.$id_finition.'", deconditionne="'.$deconditonne.'" WHERE id_inter="'.$id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            
            $log = New addLog('a modifié la couleur et/ou la finition de l\'intervention');
            $log->setInter($id_inter);
            $log->insert();
            changements_interventions($id_inter);

            header('Location: ./i'.$id_inter.'#tab_intervention_edit');
        }
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
       $log = New addLog('a ecrit un commentaire dans les messages de l\'intervention');
       $log->setInter($id_inter);
       $log->insert();
       header('Location: ./i'.$id_inter.'#tab_intervention_edit');
   }
}


/*
 * Commentaire
 */

if(isset($_POST['new_comment']) AND !(empty($_POST['new_comment'])))
{
    $texte = $db->SQLFix($_POST['new_comment']);
    $id_inter = $db->SQLfix($_GET['id_inter']);

    $sql = 'INSERT INTO commentaires (id_staff, id_inter, texte, timestamp, compte_principal) VALUES ("'.$_SESSION['id'].'", "'.$id_inter.'", "'.$texte.'", "'.time().'", "'.$_SESSION['compte_principal'].'")';
    $db->Query($sql);

    header('location: ./i'.$id_inter.'#tab_intervention_comments');
    die();

}

?>