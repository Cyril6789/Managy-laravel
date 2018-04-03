<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 26/02/2017
 * Time: 12:45
 */
class automatismes
{
    public function __construct($id_inter, $action)
    {
        if(AccesActivableModul('automatismes')AND (AccesActivableModul('sms') OR AccesActivableModul('mail'))) {

            $db_auto = new MySQL();
            $id_inter = $db_auto->SQLFix($id_inter);
            $action = $db_auto->SQLFix($action);

                $sql = 'SELECT i.rdv_debut, i.external_link, i.id_contact, i.prefix,         
                a.j, a.h, a.m, a.heurefixe, a.htime, a.mtime, a.id_sms, a.id_mail, a.immediat, a.dest_client_staff AS dest,             
                c.id AS id_customer, c.nom, c.prenom, c.titre, c.mail, c.portable, c.ville, 
                mt.titre AS titrem, mt.sujet, mt.message AS messagem, 
                st.message AS messagesms,
                cp.mail_contact, cp.nom_societe, cp.web, cp.tel AS telcp, cp.expediteur_sms, cp.signature_sms, cp.id AS id_cp,
                s.mail AS mail_staff
                FROM interventions AS i
                INNER JOIN automatismes AS a 
                ON (a.compte_principal = i.compte_principal)
                AND (a.type_atelier_rdv = i.type_atelier_rdv)
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
                LEFT JOIN prise_en_charge AS pec
                ON (pec.id_intervention = i.id_inter)
                AND (pec.compte_principal = i.compte_principal)
                LEFT JOIN staffs AS s
                ON (s.id = pec.id_staff)
                AND (s.compte_principal = i.compte_principal)
                WHERE i.id_inter = "' . $id_inter . '"
                AND i.compte_principal = "' . $_SESSION['compte_principal'] . '"
                AND a.action = "' . $action . '"
                ';


            $db_auto->Query($sql);




            while ($row_a = $db_auto->Row()) {
                if ($row_a->immediat) //Action immédiate
                {
                    if ($row_a->id_mail) // Envoie mail
                    {
                        $date = date('d/m/Y', $row_a->rdv_debut);
                        $heure = date('H\hi', $row_a->rdv_debut);

                        $message = $row_a->messagem;
                        $message = str_replace('%id_inter%', $row_a->prefix.$id_inter, $message);
                        $message = str_replace('%titre%', $row_a->titre, $message);
                        $message = str_replace('%nom%', $row_a->nom, $message);
                        $message = str_replace('%prenom%', $row_a->prenom, $message);
                        $message = str_replace('%lien_public%', 'http://www.mana.gy/' . $row_a->external_link, $message);
                        $message = str_replace('%ville%', $row_a->ville, $message);
                        $message = str_replace('%date%', $date, $message);
                        $message = str_replace('%heure%', $heure, $message);
                        $sujet = $row_a->sujet;
                        $sujet = str_replace('%id_inter%', $row_a->prefix.$id_inter, $sujet);
                        $sujet = str_replace('%ville%', $row_a->ville, $sujet);
                        $sujet = str_replace('%date%', $date, $sujet);
                        $sujet = str_replace('%heure%', $heure, $sujet);
                        $titre = $row_a->titrem;
                        $titre = str_replace('%id_inter%', $row_a->prefix.$id_inter, $titre);
                        $titre = str_replace('%ville%', $row_a->ville, $titre);
                        $titre = str_replace('%date%', $date, $titre);
                        $titre = str_replace('%heure%', $heure, $titre);

                        $email = new Maileur($sujet);
                        $email->AddTitle($titre);
                       // echo $row_a->mail;
                        $email->addDest($row_a->mail_contact);
                        if($row_a->dest == '1') {
                            if ($row_a->id_contact) {
                                $contact = new GetInfosCustomer($row_a->id_contact);
                                $email->addDest($contact->GetMail());
                                $id_client = $row_a->id_contact;
                                $e_mail = $contact->GetMail();
                            } else {
                                $email->addDest($row_a->mail);
                                $id_client = $row_a->id_customer;
                                $e_mail = $row_a->mail;
                            }
                        }
                        else {
                            $id_client = $row_a->id_customer;
                            $email->addDest($row_a->mail_staff);
                            $e_mail = $row_a->mail_staff;
                        }
                        $email->withAck($id_client, $titre, 'id_inter', $id_inter, $e_mail);
                        $email->addExpediteur($row_a->mail_contact, $row_a->nom_societe);
                        $email->setLogo($row_a->id_cp);
                        $email->addSignature($row_a->nom_societe, $row_a->web, $row_a->mail_contact, $row_a->telcp);

                        $email->body($message);
                        $email->send();

                    }

                    if ($row_a->id_sms) //Envoie SMS
                    {
                        $date = date('d/m/Y', $row_a->rdv_debut);
                        $heure = date('H\hi', $row_a->rdv_debut);

                        $message = $row_a->messagesms;
                        $message = str_replace('%id_inter%', $row_a->prefix.$id_inter, $message);
                        $message = str_replace('%titre%', $row_a->titre, $message);
                        $message = str_replace('%nom%', $row_a->nom, $message);
                        $message = str_replace('%prenom%', $row_a->prenom, $message);
                        $message = str_replace('%lien_public%', 'http://www.mana.gy/' . $row_a->external_link, $message);
                        $message = str_replace('%ville%', $row_a->ville, $message);
                        $message = str_replace('%date%', $date, $message);
                        $message = str_replace('%heure%', $heure, $message);

                        $sms = new Smseur($message, $row_a->compte_principal);

                        $sms->setIdCustomer($row_a->id_customer);
                        $sms->AddExpediteur($row_a->expediteur_sms);
                        $num = '';
                        if($row_a->id_contact)
                        {
                            $contact = new GetInfosCustomer($row_a->id_contact);
                            $num = $contact->getPort();
                        }
                        else
                            $num = $row_a->portable;
                        $sms->AddNumero($num);
                        $sms->AddSignature($row_a->signature_sms);
                        $sms->setIdInter($id_inter);

                        if(!empty($num))
                            $sms->envoie();
                        echo $sms->getError();
                    }
                } else //Action différée
                {
                    if ($row_a->heurefixe) {
                        $declenchement_temp = time() + ($row_a->j * 60 * 60 * 24);
                        $declenchement = mktime($row_a->htime, $row_a->mtime, 0, date('m', $declenchement_temp), date('d', $declenchement_temp), date('Y', $declenchement_temp));

                    } else {
                        $declenchement = time() + ($row_a->j * 60 * 60 * 24) + ($row_a->h * 60 * 60) + ($row_a->m * 60);
                    }

                    if ($row_a->id_mail) {
                        $date = date('d/m/Y', $row_a->rdv_debut);
                        $heure = date('H\hi', $row_a->rdv_debut);

                        $message = $row_a->messagem;
                        $message = str_replace('%id_inter%', $row_a->prefix.$id_inter, $message);
                        $message = str_replace('%titre%', $row_a->titre, $message);
                        $message = str_replace('%nom%', $row_a->nom, $message);
                        $message = str_replace('%prenom%', $row_a->prenom, $message);
                        $message = str_replace('%lien_public%', 'http://www.mana.gy/' . $row_a->external_link, $message);
                        $message = str_replace('%ville%', $row_a->ville, $message);
                        $message = str_replace('%date%', $date, $message);
                        $message = str_replace('%heure%', $heure, $message);
                        $sujet = $row_a->sujet;
                        $sujet = str_replace('%id_inter%', $row_a->prefix.$id_inter, $sujet);
                        $sujet = str_replace('%ville%', $row_a->ville, $sujet);
                        $sujet = str_replace('%date%', $date, $sujet);
                        $sujet = str_replace('%heure%', $heure, $sujet);
                        $titre = $row_a->titrem;
                        $titre = str_replace('%id_inter%', $row_a->prefix.$id_inter, $titre);
                        $titre = str_replace('%ville%', $row_a->ville, $titre);
                        $titre = str_replace('%date%', $date, $titre);
                        $titre = str_replace('%heure%', $heure, $titre);

                        if($row_a->dest == '1') {
                            if ($row_a->id_contact) {
                                $contact = new GetInfosCustomer($row_a->id_contact);
                                $mail = $contact->GetMail();
                            } else {
                                $mail = $row_a->mail;
                            }
                        }
                        else
                            $mail = $row_a->mail_staff;

                        $db2 = new MySQL();
                        $db2->Query('INSERT INTO mails_differes (sujet, titre, message, timestamp, compte_principal) VALUES ("' . $sujet . '", "' . $titre . '", "' . $message . '", "' . $declenchement . '", "' . $_SESSION['compte_principal'] . '") ');
                        $insert_id = $db2->GetLastInsertID();
                        $db2->Query('INSERT INTO mails_differes_destinataires (id_mail, destinataire) VALUES ("' . $insert_id . '", "' . $mail . '") ');
                    }

                    if ($row_a->id_sms) {
                        $date = date('d/m/Y', $row_a->rdv_debut);
                        $heure = date('H\hi', $row_a->rdv_debut);

                        $message = $row_a->messagesms;
                        $message = str_replace('%id_inter%', $row_a->prefix.$row_a->id_inter, $message);
                        $message = str_replace('%titre%', $row_a->titre, $message);
                        $message = str_replace('%nom%', $row_a->nom, $message);
                        $message = str_replace('%prenom%', $row_a->prenom, $message);
                        $message = str_replace('%lien_public%', 'http://www.mana.gy/' . $row_a->external_link, $message);
                        $message = str_replace('%ville%', $row_a->ville, $message);
                        $message = str_replace('%date%', $date, $message);
                        $message = str_replace('%heure%', $heure, $message);
                        $db2 = new MySQL();

                        if($row_a->id_contact)
                        {
                            $contact = new GetInfosCustomer($row_a->id_contact);
                            $numero = $contact->getPort();
                        }
                        else
                            $numero = $row_a->portable;

                        if(!empty($numero)) {

                            $db2->Query('INSERT INTO sms_differes (message, timestamp, id_staff, id_inter, compte_principal) VALUES ("' . $message . '", "' . $declenchement . '", "' . $_SESSION['id'] . '", "' . $id_inter . '", "' . $_SESSION['compte_principal'] . '") ');
                            $insert_id = $db2->GetLastInsertID();


                            $db2->Query('INSERT INTO sms_differes_destinataires (id_sms, id_client, destinataire) VALUES ("' . $insert_id . '", "' . $row_a->id_customer . '", "' . $numero . '") ');
                        }
                    }

                }
            }
            
        }
    }

}