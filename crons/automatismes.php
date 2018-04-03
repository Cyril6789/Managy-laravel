<?php session_start();
$_SESSION['temp']++;
echo $_SESSION['temp'];
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 26/02/2017
 * Time: 09:51
 */

include('./../classes/mysql.class.php');
include('./../classes/smseur.class.php');
include('./../classes/Maileur.class.php');
include('./../functions/AccesActivableModul.func.inc');
include('./../moduls/customers/classes/GetInfosCustomer.class.php');

/*
$mail = new Maileur('test cron');
$mail->addDest('heilmann.cyril@free.fr');
$mail->body('Envoie d un mail de test');
$mail->send();*/

$t1 = mktime(date('H'), date('i'), 0, date('m'), date('d'), date('Y'));
$t2 = $t1+59;

$quinze = time() - (15*24*60*60);

$sql = 'SELECT i.id_inter, i.compte_principal, i.rdv_debut, i.external_link, i.id_contact,  i.prefix,           
                a.j, a.h, a.m, a.heurefixe, a.htime, a.mtime, a.id_sms, a.id_mail, a.dest_client_staff AS dest,                
                c.id AS id_customer, c.nom, c.prenom, c.titre, c.mail, c.portable, c.ville, 
                mt.titre AS titrem, mt.sujet, mt.message AS messagem, 
                st.message AS messagesms,
                cp.mail_contact, cp.nom_societe, cp.web, cp.tel AS telcp, cp.expediteur_sms, cp.signature_sms,
                s.mail AS mail_staff
        FROM interventions AS i
        INNER JOIN automatismes AS a 
        ON (a.compte_principal = i.compte_principal)
        INNER JOIN clients AS c
        ON (i.id_client = c.id)
        AND (i.compte_principal = c.compte_principal)
        LEFT JOIN mails_types AS mt
        ON (a.id_mail = mt.id)
        AND (mt.compte_principal = i.compte_principal)
        LEFT JOIN sms_types AS st 
        ON (a.id_sms = st.id)
        AND (st.compte_principal = i.compte_principal)
        INNER JOIN comptes_principaux AS cp
        ON (cp.id = i.compte_principal)
        INNER JOIN modules_acces AS ma
        ON (i.compte_principal = ma.compte_principal)
        LEFT JOIN prise_en_charge AS pec
        ON (pec.id_intervention = i.id_inter)
        AND (pec.compte_principal = i.compte_principal)
        LEFT JOIN staffs AS s
        ON (s.id = pec.id_staff)
        AND (s.compte_principal = i.compte_principal)
        WHERE i.type_atelier_rdv = "2"
        AND immediat = "0"
        AND i.rdv_debut > "'.$quinze.'"
        AND ma.date_fin >= "'.time().'"
        AND a.action = "heure"
        AND i.rdv_annule != "1"
        GROUP BY a.id, i.id
        ';
echo $sql;
$db = new MySQL();
$db2 = new MySQL();

$db2->Query($sql);
echo $db2->Error();

while($row2 = $db2->Row())
{
    echo 'boucle';
    if(AccesActivableModul('automatismes', $row2->compte_principal) AND (AccesActivableModul('sms', $row2->compte_principal) OR AccesActivableModul('mail', $row2->compte_principal)))
    {
        //echo 'module accessible';
        if($row2->heurefixe) //heure précise
        {
            $declenchement_temp = $row2->rdv_debut + ($row2->j * 60  * 60 * 24);

            $declenchement = mktime($row2->htime, $row2->mtime, 0, date('m', $declenchement_temp), date('d', $declenchement_temp), date('Y', $declenchement_temp));
            if($declenchement >= $t1 AND $declenchement <= $t2) {
                if ($row2->id_mail) {
                    echo 'envoie mail';

                    $date = date('d/m/Y', $row2->rdv_debut);
                    $heure = date('H\hi', $row2->rdv_debut);

                    $message = $row2->messagem;
                    $message = str_replace('%id_inter%', $row2->prefix.$row2->id_inter, $message);
                    $message = str_replace('%titre%', $row2->titre, $message);
                    $message = str_replace('%nom%', $row2->nom, $message);
                    $message = str_replace('%prenom%', $row2->prenom, $message);
                    $message = str_replace('%nom_couleur%', $nom_couleur, $message);
                    $message = str_replace('%ral%', $ral, $message);
                    $message = str_replace('%lien_public%', 'http://www.mana.gy/' . $row2->external_link, $message);
                    $message = str_replace('%ville%', $row2->ville, $message);
                    $message = str_replace('%date%', $date, $message);
                    $message = str_replace('%heure%', $heure, $message);
                    $sujet = $row2->sujet;
                    $sujet = str_replace('%id_inter%', $row2->id_inter, $sujet);
                    $sujet = str_replace('%ville%', $row2->ville, $sujet);
                    $sujet = str_replace('%date%', $date, $sujet);
                    $sujet = str_replace('%heure%', $heure, $sujet);
                    $titre = $row2->titrem;
                    $titre = str_replace('%id_inter%', $row2->id_inter, $titre);
                    $titre = str_replace('%ville%', $row2->ville, $titre);
                    $titre = str_replace('%date%', $date, $titre);
                    $titre = str_replace('%heure%', $heure, $titre);

                    $email = new Maileur($sujet);
                    $email->AddTitle($titre);


                    if($row2->dest == '1')
                    {
                        if($row2->id_contact)
                        {
                            $contact = new GetInfosCustomer($row2->id_contact);
                            $email->addDest($contact->GetMail());
                            $id_client = $row2->id_client;
                            $e_mail = $contact->GetMail();
                        }
                        else {
                            $email->addDest($row2->mail);
                            $id_client = $row2->id_customer;
                            $e_mail = $row2->mail;
                        }
                        $email->addDest($row2->mail_contact);
                        $e_mail = $row2->mail_contact;
                    }
                    else {
                        $id_client = $row2->id_customer;
                        $email->addDest($row2->mail_staff);
                    }
                    $email->withAck($id_client, $titre, 'id_inter', $id_inter, $e_mail,  -1, $row2->compte_principal);
                    $email->addExpediteur($row2->mail_contact, $row2->nom_societe);
                    $email->setLogo($row2->compte_principal);
                    $email->addSignature($row2->nom_societe, $row2->web, $row2->mail_contact, $row2->telcp);
                    $email->body($message);
                    $email->send();


                }
                if ($row2->id_sms) {

                    $date = date('d/m/Y', $row2->rdv_debut);
                    $heure = date('H\hi', $row2->rdv_debut);

                    $message = $row2->messagesms;
                    $message = str_replace('%id_inter%', $row2->prefix.$row2->id_inter, $message);
                    $message = str_replace('%titre%', $row2->titre, $message);
                    $message = str_replace('%nom%', $row2->nom, $message);
                    $message = str_replace('%prenom%', $row2->prenom, $message);
                    $message = str_replace('%lien_public%', 'http://www.mana.gy/' . $row2->external_link, $message);
                    $message = str_replace('%ville%', $row2->ville, $message);
                    $message = str_replace('%date%', $date, $message);
                    $message = str_replace('%heure%', $heure, $message);

                    $sms = new Smseur($message, $row2->compte_principal);


                    $sms->setIdCustomer($row2->id_customer);
                    $sms->AddExpediteur($row2->expediteur_sms);

                    $num = '';
                    if($row2->id_contact)
                    {
                        $contact = new GetInfosCustomer($row2->id_contact);
                        $num = $contact->getPort();
                    }
                    else
                        $num = $row2->portable;

                    $sms->AddNumero($num);


                    $sms->AddSignature($row2->signature_sms);
                    if(!empty($num))
                        $sms->envoie();
                    echo $sms->getError();

                    echo 'envoie sms';
                }
                echo $row2->id_inter . ' ' . $row2->titre . ' ' . $row2->nom . ' ' . $row2->prenom . ' ' . $row2->ville . '<br />';
            }
        }
        else
        {
            echo 'heure relative';
            $declenchement = $row2->rdv_debut + ($row2->j * 60  * 60 * 24) + ($row2->h * 60 * 60) + ($row2->m * 60);
           echo ' <br />t1 = '.$t1.' t2='.$t2.' declenchement '.$declenchement.' '.$row2->id_inter.' j'.$row2->j.' h'.$row->h.' m'.$row2->m;
            if($declenchement >= $t1 AND $declenchement <= $t2)
            {
               echo 'ok ' .$id_inter;
                if ($row2->id_mail) {
                    echo ' envoie mail ';



                    $date = date('d/m/Y', $row2->rdv_debut);
                    $heure = date('H\hi', $row2->rdv_debut);

                    $message = $row2->messagem;
                    $message = str_replace('%id_inter%', $row2->prefix.$row2->id_inter, $message);
                    $message = str_replace('%titre%',  $row2->titre, $message);
                    $message = str_replace('%nom%', $row2->nom, $message);
                    $message = str_replace('%prenom%', $row2->prenom, $message);
                    $message = str_replace('%nom_couleur%', $nom_couleur, $message);
                    $message = str_replace('%ral%', $ral, $message);
                    $message = str_replace('%lien_public%', 'http://www.mana.gy/'.$row2->external_link, $message);
                    $message = str_replace('%ville%', $row2->ville, $message);
                    $message = str_replace('%date%', $date, $message);
                    $message = str_replace('%heure%', $heure, $message);
                    $sujet = $row2->sujet;
                    $sujet = str_replace('%id_inter%', $row2->id_inter, $sujet);
                    $sujet = str_replace('%ville%', $row2->ville, $sujet);
                    $sujet = str_replace('%date%', $date, $sujet);
                    $sujet = str_replace('%heure%', $heure, $sujet);
                    $titre = $row2->titrem;
                    $titre = str_replace('%id_inter%', $row2->id_inter, $titre);
                    $titre = str_replace('%ville%', $row2->ville, $titre);
                    $titre = str_replace('%date%', $date, $titre);
                    $titre = str_replace('%heure%', $heure, $titre);

                    $email = new Maileur($sujet);
                    $email->addDest($row2->mail_contact);
                    $email->AddTitle($titre);
                    if($row2->dest == '1') {
                        if ($row2->id_contact) {
                            $contact = new GetInfosCustomer($row2->id_contact);
                            $email->addDest($contact->GetMail());
                        } else
                            $email->addDest($row2->mail);
                    }
                    else
                        $email->addDest($row2->mail_staff);


                    $email->addExpediteur($row2->mail_contact, $row2->nom_societe);
                    $email->setLogo($row2->compte_principal);
                    $email->addSignature($row2->nom_societe, $row2->web, $row2->mail_contact, $row2->telcp);

                    $email->body($message);
                    $email->send();


                }
                if ($row2->id_sms) {

                    echo 'Envoie de SMS';
                    $date = date('d/m/Y', $row2->rdv_debut);
                    $heure = date('H\hi', $row2->rdv_debut);

                    $message = $row2->messagesms;
                    $message = str_replace('%id_inter%', $row2->prefix.$row2->id_inter, $message);
                    $message = str_replace('%titre%', $row2->titre, $message);
                    $message = str_replace('%nom%', $row2->nom, $message);
                    $message = str_replace('%prenom%', $row2->prenom, $message);
                    $message = str_replace('%lien_public%', 'http://www.mana.gy/'.$external_link, $message);
                    $message = str_replace('%ville%', $row2->ville, $message);
                    $message = str_replace('%date%', $date, $message);
                    $message = str_replace('%heure%', $heure, $message);

                    $sms = new Smseur($message, $row2->compte_principal);


                    $sms->setIdCustomer($row2->id_customer);
                    $sms->AddExpediteur($row2->expediteur_sms);
                    $num = '';
                    if($row2->id_contact)
                    {
                        $contact = new GetInfosCustomer($row2->id_contact);
                        $num = $contact->getPort();
                    }
                    else
                        $num = $row2->portable;

                    $sms->AddNumero($num);
                    $sms->AddSignature($row2->signature_sms);
                    if(!empty($num))
                        $sms->envoie();
                    echo $sms->getError();

                    echo ' envoie sms ';
                }
                echo $row2->id_inter . ' ' . $row2->titre . ' ' . $row2->nom . ' ' . $row2->prenom . ' ' . $row2->ville . '<br />';
            }
        }

    }
}


$db->Close();
$db2->Close();
?>