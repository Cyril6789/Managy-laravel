<?php session_start();



if(isset($_POST['date_heure']) AND !empty($_POST['date_heure']) AND isset($_POST['date_heure_fin']) AND !empty($_POST['date_heure_fin']))
{
    $id_inter = (int) $db->SQLfix($_GET['id_inter']);
    $tab = explode(' ', $_POST['date_heure']);
    $tab_date = explode('/', $tab[0]);
    $tab_heure = explode(':', $tab[1]);

    $j = $tab_date[0];
    $m = $tab_date[1];
    $a = $tab_date[2];

    $h = $tab_heure[0];
    $min = $tab_heure[1];


    $tabf = explode(' ', $_POST['date_heure_fin']);
    $tab_datef = explode('/', $tabf[0]);
    $tab_heuref = explode(':', $tabf[1]);

    $jf = $tab_datef[0];
    $mf = $tab_datef[1];
    $af = $tab_datef[2];

    $hf = $tab_heuref[0];
    $minf = $tab_heuref[1];

    $timestamp = mktime($h, $min, 0, $m, $j, $a);
    $timestampf = mktime($hf, $minf, 0, $mf, $jf, $af);


    $sql = 'UPDATE interventions SET rdv_debut="'.$timestamp.'", rdv_fin="'.$timestampf.'"WHERE id_inter="'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'"';
    $db->Query($sql);

    $log = New addLog('a modifié le créneau horaire de l\'intervention');
    $log->setInter($id_inter);
    $log->insert();


    $auto = new automatismes($id_inter, 'change_rdv');

    header('Location: ./i'.$id_inter);
    die();
}

/*
 * Supprimer le créneau horaire
 */

if(isset($_POST['priorite']))
{
    $priorite = $db->SQLFix($_POST['priorite']);
    $id_inter = (int) $db->SQLfix($_GET['id_inter']);

    $sql = 'UPDATE interventions SET rdv_debut="0", rdv_fin="0", priorite="'.$priorite.'" WHERE id_inter="'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'"';
    $db->Query($sql);

    $log = New addLog('a supprimé le créneau horaire de l\'intervention');
    $log->setInter($id_inter);
    $log->insert();


    header('Location: ./i'.$id_inter);
    die();
}

/*
 * Contacts Ref
 */

if(isset($_POST['contact_ref']))
{
    $contact = $db->SQLFix($_POST['contact_ref']);
    $id_inter = (int) $db->SQLfix($_GET['id_inter']);

    $sql = 'UPDATE interventions SET id_contact="'.$contact.'" WHERE id_inter="'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'"';

    $db->Query($sql);

    $log = New addLog('a modifié le contact référent de l\'intervention');
    $log->setInter($id_inter);
    $log->insert();


    header('Location: ./i'.$id_inter);
    die();
}



//mail

if(isset($_POST['mail']))

{

    print_r($_POST);

    //die();
    if($_POST['differe'] == 'on')
    {
        global $db;
        $sujet = $db->SQLFix($_POST['sujet']);
        $titre = $db->SQLFix($_POST['titre']);
        $message = $db->SQLFix($_POST['message']);

        $tab_date = explode('/', $db->SQLFix($_POST['differe_date']));
        $tab_time = explode(':', $db->SQLFix($_POST['differe_heure']));

        $timestamp = mktime($tab_time[0], $tab_time[1], 0, $tab_date[1], $tab_date[0], $tab_date[2]);



        $mail = $db->SQLFix($_POST['mail']);

        $db->Query('INSERT INTO mails_differes (sujet, titre, message, timestamp, compte_principal) VALUES ("'.$sujet.'", "'.$titre.'", "'.$message.'", "'.$timestamp.'", "'.$_SESSION['compte_principal'].'") ');

        $insert_id = $db->GetLastInsertID();

        $db->Query('INSERT INTO mails_differes_destinataires (id_mail, destinataire) VALUES ("'.$insert_id.'", "'.$mail.'") ');

        changements_interventions($id_inter);

        $log = New addLog('a programmé l\'envoie d\'un mail au client');
        $log->setInter($id_inter);
        $log->insert();

        //die();
        $_SESSION['notify'] = 'ScheduleMailSent';
        header('Location: ./i'.$id_inter.'#tab_inter_customer');
        die();
    }
    else
    {

       /* if($_SESSION['id'] == 1) {
            print_r($_POST);
            die();
        }*/

        $db = new MySQL();
        $sql = 'SELECT id_client FROM interventions WHERE id_inter="'.$id_inter.'" AND compte_principal = "'.$_SESSION['compte_principal'].'"';
        $db->Query($sql);
        $row = $db->Row();

        $email = new Maileur($_POST['sujet']);
        $email->AddTitle($_POST['titre']);
        $email->addDest($_POST['mail']);
        $email->withAck($row->id_client, $_POST['titre']);
        $email->addDest(MAIL_CONTACT);
        $email->addExpediteur(MAIL_CONTACT, NOM_SOCIETE);
        $email->setLogo($_SESSION['compte_principal']);
        $email->addSignature(NOM_SOCIETE, WEB, MAIL_CONTACT, TEL);
        $email->body($_POST['message']);
        $email->send();
        changements_interventions($id_inter);

        $log = New addLog('a envoyé un mail au client');
        $log->setInter($id_inter);
        $log->insert();

        $_SESSION['notify'] = 'MailSent';
        header('Location: ./i'.$id_inter.'#tab_inter_customer');
        die();
    }



}



if(isset($_POST['sms']))
{

    if($_POST['differe'] == 'on')
    {
        global $db;
        $message = $db->SQLFix($_POST['texte']);

        $tab_date = explode('/', $db->SQLFix($_POST['differe_date']));
        $tab_time = explode(':', $db->SQLFix($_POST['differe_heure']));

        $timestamp = mktime($tab_time[0], $tab_time[1], 0, $tab_date[1], $tab_date[0], $tab_date[2]);

        $mail = $db->SQLFix($_POST['num']);

        $id_inter = $db->SQLFix($_GET['id_inter']);


        $sms_restant = $_SESSION['commande_sms'] - $_SESSION['conso_sms'];
        if($sms_restant <= $nb_sms) {
            $_SESSION['notify'] = 'ErrorQuotaSms';
        }
        else {

            $db->Query('INSERT INTO sms_differes (message, timestamp, id_staff, id_inter, compte_principal) VALUES ("' . $message . '", "' . $timestamp . '", "' . $_SESSION['id'] . '", "' . $id_inter . '", "' . $_SESSION['compte_principal'] . '") ');


            $insert_id = $db->GetLastInsertID();
            $t = explode('_', $_POST['sms']);

            $db->Query('INSERT INTO sms_differes_destinataires (id_sms, id_client, destinataire) VALUES ("' . $insert_id . '", "' . $t[0] . '", "' . $mail . '") ');

            changements_interventions($id_inter);

            $log = New addLog('a programmé l\'envoie d\'un sms au client');
            $log->setInter($id_inter);
            $log->insert();

            //die();
            $_SESSION['notify'] = 'ScheduleSmsSent';
        }
        header('Location: ./i'.$id_inter.'#tab_inter_customer');
        die();
    }
    else {

        $sms = new Smseur($_POST['texte']);

        $t = explode('_', $_POST['sms']);

        $sms->setIdCustomer($t[0]);
        $sms->AddExpediteur(EXP_SMS);
        $sms->AddNumero($_POST['num']);
        $sms->AddSignature(SIGNATURE_SMS);
        $sms->envoie();
        $erreur = $sms->getError();

        if($erreur)
        {
            $sms_restant = $_SESSION['commande_sms'] - $_SESSION['conso_sms'];
            if($sms_restant <= $nb_sms) {
                $_SESSION['notify'] = 'ErrorQuotaSms';
            }
            else
            {
                $_SESSION['notify'] = 'ErrorSentSMS';
            }
        }
        else {
            changements_interventions($id_inter);

            $log = New addLog('a envoyé un SMS au client');
            $log->setInter($id_inter);
            $log->insert();

            $_SESSION['notify'] = 'SmsSent';
        }
        header('Location: ./i' . $id_inter.'#tab_inter_customer');
        die();
    }

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
        $db->Query('SELECT time_cloture FROM interventions WHERE id_inter="'.$id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
        $row = $db->Row();

        if($row->time_cloture)
            $cloture = 1;
        else
            $cloture = 0;

        if(!$cloture)
        {
                     //Prestations effectuées            

            $sql = 'DELETE FROM prestations_effectuees WHERE id_inter ="'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ';
            $db->Query($sql);
            
            for($i=0; $i<10; $i++)
            {              
                if($_POST['presta_'.$i] != '0_0')
                {
                    $tab_val = explode('_', $_POST['presta_'.$i]);
                    $id_presta = $tab_val['0'];

                    $sql = 'SELECT designation FROM prestations WHERE id="'.$id_presta.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ';
                    $db->Query($sql);
                    $row = $db->Row();

                    $designation = $row->designation;
                    $duree = str_replace(',', '.', $_POST['duree_'.$i]);

                    if(is_numeric($duree))
                        $duree = $db->SQLfix($duree);
                    else
                        $duree = 0;

                    $sql = 'INSERT INTO prestations_effectuees (id_inter, id_presta, designation, duree, compte_principal) VALUES ("'.$id_inter.'", "'.$id_presta.'", "'.$designation.'", "'.$duree.'", "'.$_SESSION['compte_principal'].'")';
                    $db->Query($sql);

                }
            }            

            $panne = $db->SQLfix($_POST['panne']);
            $message_interne = $db->SQLfix($_POST['message_interne']);
            $resolution = $db->SQLfix($_POST['resolution']);
            $message_client = $db->SQLfix($_POST['message_client']);
            $materiel_ajoute = $db->SQLfix($_POST['materiel_ajoute']);
            $materiel_depose = $db->SQLfix($_POST['materiel_depose']);
            

            $sql = 'UPDATE interventions
                        SET panne="'.$panne.'", 
                        message_interne="'.$message_interne.'", 
                        resolution="'.$resolution.'", 
                        message_client="'.$message_client.'",
                        materiel_ajoute ="'.$materiel_ajoute.'",
                        matos = "'.$materiel_depose.'"';
            
            if($t[0] == "cloture")
            {
                $sql .= ' , time_cloture = '.time().' ';



                    $mail_cloture = true;

                //die();

                $force_facture = 0;

                if($_POST['pm_add_form'])
                {
                    $desi = $db->SQLFix($_POST['pm_desi_form']);
                    $duree = $db->SQLFix($_POST['pm_duree_form']);

                    if($desi AND $duree)
                    {
                        $sql2 = 'SELECT id_client FROM interventions WHERE id_inter="'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ';
                        $db->Query($sql2);
                        $row = $db->Row();

                        $id_client = $row->id_client;



                        $sql2 = 'INSERT INTO maintenance (id_client, date_mouvement, mouvements, commentaires, id_inter, compte_principal)
                                VALUES ("'.$id_client.'", "'.time().'", "'.$duree.'", "'.$desi.'", "'.$id_inter.'", "'.$_SESSION['compte_principal'].'") ';
                        $db->Query($sql2);

                      // print_r($_POST);
                        if($_POST['pm_force_form'])
                            $force_facture = 1;

                    }
                }

                $sql .= ' , force_facturation="'.$force_facture.'"   ';



                $log = New addLog('a clôturé l\'intervention');
                $log->setInter($id_inter);
                $log->insert();

                $auto = new automatismes($id_inter, 'cloture');
            }
            else
            {
                $log = New addLog('a sauvegardé une saisie');
                $log->setInter($id_inter);
                $log->insert();

                $auto = new automatismes($id_inter, 'sauvegarde');
                
                $_SESSION['notify'] = 'SaveInter';
            }

            $sql .= ' WHERE id_inter="'.$id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ';     

            //echo $sql;

            //die();
            $db->Query($sql);


            if($mail_cloture)
            {
                $email = new Maileur('Managy : Clôture Intervention N°'.$id_inter);
                $email->AddTitle('Clôture Intervention N°'.$id_inter);
                $email->addDest(MAIL_CONTACT);

                $sql2 = 'SELECT inter.id_inter, inter.time_cloture, inter.rdv_annule, inter.type_atelier_rdv, inter.rdv_debut, inter.rdv_fin, inter.time_ouverture, inter.id_materiel, inter.id_se, inter.id_antivirus, inter.garantie, inter.urgente, inter.panne, inter.matos, inter.message_interne, inter.resolution, inter.message_client, inter.materiel_ajoute, inter.mdp, clients.id AS id_c, clients.pro_part, clients.titre, clients.nom, clients.prenom, clients.adresse, clients.adresse_suite, clients.cp, clients.ville, clients.fixe, clients.portable, clients.mail, materiels.nom AS nom_materiel, se.nom AS nom_se, antivirus.nom AS nom_antivirus
                                FROM interventions AS inter
                                INNER JOIN clients
                                ON (inter.id_client = clients.id)
                                AND (clients.compte_principal="'.$_SESSION['compte_principal'].'")
                                LEFT JOIN materiels
                                ON (materiels.id = inter.id_materiel)
                                AND (materiels.compte_principal="'.$_SESSION['compte_principal'].'")
                                LEFT JOIN se
                                ON (se.id = inter.id_se)
                                AND (se.compte_principal="'.$_SESSION['compte_principal'].'")
                                LEFT JOIN antivirus
                                ON (antivirus.id = inter.id_antivirus)
                                AND (antivirus.compte_principal="'.$_SESSION['compte_principal'].'")
                                WHERE inter.id_inter="'.$id_inter.'"
                                AND inter.compte_principal="'.$_SESSION['compte_principal'].'"
                                AND clients.compte_principal="'.$_SESSION['compte_principal'].'" ';


                $info = '<strong>Requête : </strong>'.$sql2;


                $db->Query($sql2);

                if($db->Error())
                {
                    $danger = '<strong>Erreur mysql : </strong>'.$db->Error();
                    echo $danger.$info;
                    die();
                }

                $row = $db->Row();

                $type_atelier_rdv = $row->type_atelier_rdv;
                $rdv_debut = $row->rdv_debut;
                $rdv_fin = $row->rdv_fin;
                $id_materiel = $row->id_materiel;
                $id_se = $row->id_se;
                $id_antivirus = $row->id_antivirus;
                $garantie = $row->garantie;
                $urgente = $row->urgente;
                $panne = $row->panne;
                $matos = $row->matos; $nom_materiel = $row->nom_materiel;
                $message_interne = $row->message_interne;
                $resolution = $row->resolution;
                $message_client = $row->message_client;
                $materiel_ajoute = $row->materiel_ajoute;
                $time_ouverture = $row->time_ouverture;
                $time_cloture = $row->time_cloture;
                $nom_se = $row->nom_se;
                $codes = $row->mdp;
                $nom_antivirus = $row->nom_antivirus;
                $rdv_annule = $row->rdv_annule;
                $id_client = $row->id_c;
                $prenom_client = $row->prenom;
                $nom_client = $row->nom;


                if($type_atelier_rdv == 1)
                    $texte = 'Bonjour !<br /><strong>'.$_SESSION['prenom'].'</strong> vient de clôturer l\'intervention N°<strong>'.$id_inter.'</strong>';
                else
                    $texte = 'Bonjour !<br /><strong>'.$_SESSION['prenom'].'</strong> vient de clôturer l\'intervention sur site N°<strong>'.$id_inter.'</strong>';

                if($rdv_annule)
                    $texte .= '<br />Motif : <strong>Rendez-vous annulé</strong>';

                $texte .= '<br /><br /><strong>Client :</strong> '.$row->titre.' '.$row->nom.' '.$row->prenom;

                if($type_atelier_rdv == 1)
                    $texte .= '<br /><strong>Date ouverture :</strong> Le '.date('d/m/Y à H:i', $time_ouverture);
                else
                    $texte .= '<br /><strong>Date rendez-vous :</strong> Le '.date('d/m/Y à H:i', $rdv_debut);
                $texte .= '<br /><strong>Date clôture : </strong>Le '.date('d/m/Y à H:i');
                $texte .= '<br /><strong>Panne :</strong> '.nl2br($panne);
                $texte .= '<br /><strong>Résolution :</strong> '.nl2br($resolution);
                $texte .= '<br /><strong>Matériel ajouté : </strong>'.nl2br($materiel_ajoute);

                $db->Query('SELECT id_presta, designation, duree FROM prestations_effectuees WHERE id_inter = "'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'"');
                $i=0;
                $texte .= '<br /><strong>Prestations effectuées :</strong>
                            <ul>';
                while($row = $db->Row())
                    $texte .= '<li>'.$row->designation.' : '.$row->duree.'h</li>';





                $email->body($texte);



                $notification = new notificationMail('2');
                $notification->tab_parse(Array('%id_inter%' => $id_inter, '%client%' => $id_client, '%prenom_staff%' => $_SESSION['prenom']));
                $notification->sendMail();

            }
            
            changements_interventions($id_inter);

            header('location: ./i'.$id_inter);
            die();
        }
    }
}
//FIN SAVE




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

       $log = New addLog('a ecrit un message dans les commentaires de l\'intervention');
       $log->setInter($id_inter);
       $log->insert();

       header('Location: ./i'.$id_inter);
   }

}


//Modifier les paramètres de l'interventions

if($_POST['id_materiel'])
{

    global $db;
    $id_inter = $db->SQLfix($_GET['id_inter']);
    if(is_numeric($id_inter))
    {



        $id_materiel = $db->SQLfix($_POST['id_materiel']);
        $id_os = $db->SQLfix($_POST['id_os']);
        $id_antivirus = $db->SQLfix($_POST['id_antivirus']);
        $mdp = $db->SQLfix($_POST['mdp']);
        $pattern = $db->SQLFix($_POST['pattern_edit_value']);

        if($_POST['garantie'] == 'on')
            $garantie = 1;
        else
            $garantie = 0;        

        if($_POST['urgente'] == 'on')
            $urgente = 1;
        else
            $urgente = 0;

        $tarif = $db->SQLFix($_POST['tarif']);
        
        $sql = 'UPDATE interventions SET id_materiel="'.$id_materiel.'", pattern="'.$pattern.'", id_se="'.$id_os.'", id_antivirus="'.$id_antivirus.'", mdp="'.$mdp.'", urgente="'.$urgente.'", garantie="'.$garantie.'", tarif_estimatif="'.$tarif.'" WHERE id_inter = "'.$id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ';

        $db->Query($sql);
              
        changements_interventions($id_inter);
        $log = New addLog('a modifié les détails de l\'intervention');
        $log->setInter($id_inter);
        $log->insert();

        $_SESSION['notify'] = 'SaveDetails';
        header('location: ./i'.$id_inter);
        die();
    }
}


//Ajouter/modifier commande
if(!empty($_POST['bdc']) AND (right('commandes', 2) OR right('commandes', 3)))
{
    global $db;
    $id_inter = $db->SQLfix($_GET['id_inter']);
    $bdc = $db->SQLfix($_POST['bdc']);
    $fournisseur = $db->SQLfix($_POST['fournisseur']);
    $num_cde = $db->SQLfix($_POST['num_cde']);
    $colis = $db->SQLfix($_POST['colis']);
    $date_cde = $db->SQLfix($_POST['date_cde']);
    $date_reception = $db->SQLfix($_POST['date_reception']);

    $tab_d1 = explode('/', $date_cde);
    $date_c = mktime(0, 0, 0, $tab_d1[1], $tab_d1[0], $tab_d1[2]);
    
    $tab_d2 = explode('/', $date_reception);
    $date_r = mktime(0, 0, 0, $tab_d2[1], $tab_d2[0], $tab_d2[2]);
    
    if(!empty($_POST['id_cde']) AND is_numeric($_POST['id_cde']) )
    {
        if(right('commandes', 3))
        {
            $id_cde = $db->SQLfix($_POST['id_cde']); 
            $sql = 'UPDATE commandes SET fournisseur="'.$fournisseur.'", bdc="'.$bdc.'", num_cde="'.$num_cde.'", colis="'.$colis.'", date_cde="'.$date_c.'", date_reception="'.$date_r.'" WHERE id_inter="'.$id_inter.'" AND id="'.$id_cde.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ';
            $_SESSION['notify'] = 'UpdateOrder';

            $log = New addLog('a mis à jour une commande');
            $log->setInter($id_inter);
            $log->insert();
        }
    }
    else
    {
        $sql = 'INSERT INTO commandes (id_inter, fournisseur, bdc, num_cde, colis, date_cde, date_reception, compte_principal) VALUES ("' . $id_inter . '", "' . $fournisseur . '", "' . $bdc . '", "' . $num_cde . '", "' . $colis . '", "' . $date_c . '", "' . $date_r . '", "' . $_SESSION['compte_principal'] . '" )';
        $_SESSION['notify'] = 'AddOrder';

        $auto = new automatismes($id_inter, 'commande');

        $log = New addLog('a créé une commande');
        $log->setInter($id_inter);
        $log->insert();
    }
    $db->Query($sql);
    changements_interventions($id_inter);


    header('Location: ./i'.$id_inter);
    die();
}



//Ajouter/modifier sous-traitance
if(!empty($_POST['devis']) AND (right('soustraitances', 2) OR right('soustraitances', 3)))
{
    global $db;
    $id_inter = $db->SQLfix($_GET['id_inter']);
    $devis = $db->SQLfix($_POST['devis']);
    $nom = $db->SQLfix($_POST['nom']);
    $num_sst = $db->SQLfix($_POST['num_sst']);
    $colis_alle = $db->SQLfix($_POST['colis_alle']);
    $colis_retour = $db->SQLfix($_POST['colis_retour']);
    $date_envoie = $db->SQLfix($_POST['date_envoie']);
    $date_retour = $db->SQLfix($_POST['date_retour']);

    $tab_d1 = explode('/', $date_envoie);
    $date_e = mktime(0, 0, 0, $tab_d1[1], $tab_d1[0], $tab_d1[2]);
    
    $tab_d2 = explode('/', $date_retour);
    $date_r = mktime(0, 0, 0, $tab_d2[1], $tab_d2[0], $tab_d2[2]);
    
    if(!empty($_POST['id_sst']) AND is_numeric($_POST['id_sst']) )
    {
        if(right('soustraitances', 3))
        {
            $id_cde = $db->SQLfix($_POST['id_sst']); 
            $sql = 'UPDATE sous_traitances SET devis="'.$devis.'", nom="'.$nom.'", num_cde="'.$num_sst.'", colis_alle="'.$colis_alle.'", colis_retour="'.$colis_retour.'", date_sst="'.$date_e.'", date_retour="'.$date_r.'" WHERE id_inter="'.$id_inter.'" AND id="'.$id_cde.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ';
            $_SESSION['notify'] = 'UpdateSousTraitance';
        }
    }
    else
    {
        $sql = 'INSERT INTO sous_traitances (id_inter, devis, nom, num_cde, colis_alle, colis_retour, date_sst, date_retour, compte_principal) VALUES ("' . $id_inter . '", "' . $devis . '", "' . $nom . '", "' . $num_sst . '", "' . $colis_alle . '", "' . $colis_retour . '", "' . $date_e . '", "' . $date_r . '", "' . $_SESSION['compte_principal'] . '" )';
        $_SESSION['notify'] = 'AddSousTraitance';
        $auto = new automatismes($id_inter, 'stt');
    }
    
    $db->Query($sql);
    changements_interventions($id_inter);
    $log = New addLog('a envoyé l\'intervention en sous-traitance');
    $log->setInter($id_inter);
    $log->insert();

    header('Location: ./i'.$id_inter);
    die();
}

//fichier
if(!empty($_FILES['fichier']))
{
    $extensions = Array('.jpg', '.jpeg', '.png', '.gif', '.pdf', '.doc', '.docx', '.txt', '.tiff', '.raw', '.xls', '.xlsx', '.ppt', '.pps', '.pptx', '.ppsx', '.sxw', '.odt', '.stw', '.ott', '.sxc', '.ods', '.stc', '.ots', '.sxi', '.odp', '.sti', '.otp', '.odb', '.sxd', '.odg', '.std', '.otg', '.sxm', '.odf');

    $extension = strtolower(strrchr($_FILES['fichier']['name'], '.'));

    if(!in_array($extension, $extensions))
    {
        $_SESSION['notify'] = 'ErrorExtFile';
        header('Location: ./i'.$id_inter);
        die();
    }
    else
    {
        $fichier = strtolower(utf8_decode($_FILES['fichier']['name']));
        $fichier = strtr($fichier,
            utf8_decode('ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ'),
            'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
        $fichier = strtolower(preg_replace('/([^.a-z0-9]+)/i', '-', $fichier));

        $dir = './files/'.$_SESSION['compte_principal'].'/i/'.$_GET['id_inter'].'/';

        if(!is_dir($dir))
            mkdir($dir);

        if(is_file($dir . $fichier))
        {

            $i = 1;
            $tab = explode('.', $fichier);
            $temp_name = $tab[0];
            while(is_file($dir . $temp_name.' ('.$i.').'.$tab[1]))
            {
                $i++;
            }

            $fichier = $temp_name.' ('.$i.').'.$tab[1];
        }


        if(move_uploaded_file($_FILES['fichier']['tmp_name'], $dir . $fichier))
        {
            if(AccesActivableModul('dropbox')) {
                $dropbox = new Dropbox();
                $dropbox->upload_file($dir . $fichier, 'Interventions/' . $_GET['id_inter']);
            }

            $_SESSION['notify'] = 'FileUploadSucces';
            changements_interventions($id_inter);
            $log = New addLog('a téléchargé un fichier sur l\'intervention');
            $log->setInter($id_inter);
            $log->insert();
        }
        else
        {
            $_SESSION['notify'] = 'FileUploadError';
        }
        header('Location: ./i'.$id_inter.'#tab_inter_files');
        die();
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

    header('location: ./i'.$id_inter.'#tab_inter_comments');
    die();

}

?>