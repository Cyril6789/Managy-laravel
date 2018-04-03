<?php session_start();

//Mot de passe
if(!empty($_POST['pass_service']) AND !empty($_GET['id_customer']))
{
    require_once('./functions/crypt_decrypt.func.inc');
    global $db;
    $id_customer = $db->SQLFix($_GET['id_customer']);
    $service = $db->SQLFix($_POST['pass_service']);

    $identifiant =  Base64_Encryption::Crypter($db->SQLFix($_POST['pass_identifiant']), CRYPT_KEY);
    $mdp = Base64_Encryption::Crypter($db->SQLFix($_POST['pass_pass']), CRYPT_KEY);

    $db->Query('INSERT INTO mdp (id_client, service, identifiant, pass, compte_principal) VALUES ("'.$id_customer.'", "'.$service.'", "'.$identifiant.'", "'.$mdp.'", "'.$_SESSION['compte_principal'].'") ');

    $_SESSION['notify'] = 'AddPass';
    header('Location: ./c'.$_GET['id_customer']);
    die();

}


 //GESTION DES POSTS
if(isset($_POST['mail']))
{

    $email = new Maileur($_POST['sujet']);
    $email->AddTitle($_POST['titre']);
    $email->addDest($_POST['mail']);
    //$email->addDest('contact@depaninfo67.com');
    $email->addDest(MAIL_CONTACT);
    $email->addExpediteur(MAIL_CONTACT, NOM_SOCIETE);
    $email->setLogo(LOGO);
    $email->addSignature(NOM_SOCIETE, WEB, MAIL_CONTACT, TEL);
    $email->body($_POST['message']);
    $email->send();

    $log = New addLog('a envoyé un mail au client');
    $log->setClient($_GET['id_customer']);
    $log->insert();

    header('Location: ./c'.$_GET['id_customer']);

}

//modification
if(isset($_POST['settings-name']) AND !empty($_POST['settings-name']))
{
    if(right('customers', 1))
    {
        if($_GET['id_customer'] AND !empty($_GET['id_customer']) AND is_numeric($_GET['id_customer'])) //Un client séléctionné
        {
            global $db;
            $id_customer = $db->SQLfix($_GET['id_customer']);
            
            $db->QUERY('SELECT COUNT(*) AS nb FROM clients WHERE id ="'.$id_customer.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            
            if($row->nb) //client existant
            {
                $nom = $db->SQLfix($_POST['settings-name']);
                $prenom = $db->SQLfix($_POST['settings-lastname']);
                $mail = $db->SQLfix($_POST['settings-mail']);
                $adresse = $db->SQLfix($_POST['settings-adresse']);
                $adresse_suite = $db->SQLfix($_POST['settings-adresse-suite']);
                $cp = $db->SQLfix(str_replace(' ', '',$_POST['settings-cp']));
                $ville = $db->SQLfix($_POST['settings-ville']);
                $portable = $db->SQLfix($_POST['settings-portable']);
                $fixe = $db->SQLfix($_POST['settings-fixe']);
                $titre = $db->SQLfix($_POST['settings-titre']);
                
                if(empty($titre))
                    $sql = 'UPDATE clients SET nom = "'.$nom.'", prenom="'.$prenom.'", mail = "'.$mail.'", adresse="'.$adresse.'", adresse_suite="'.$adresse_suite.'", cp ="'.$cp.'", ville ="'.$ville.'", portable="'.$portable.'", fixe="'.$fixe.'" WHERE id = "'.$id_customer.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ';
                else
                    $sql = 'UPDATE clients SET titre="'.$titre.'", nom = "'.$nom.'", prenom="'.$prenom.'", mail = "'.$mail.'", adresse="'.$adresse.'", adresse_suite="'.$adresse_suite.'", cp ="'.$cp.'", ville ="'.$ville.'", portable="'.$portable.'", fixe="'.$fixe.'" WHERE id = "'.$id_customer.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ';


                $db->Query($sql);

                $log = New addLog('a modifié le client');
                $log->setClient($id_customer);
                $log->insert();

                if(!$_SESSION['gerant']) {
                    $notif = new notificationMail(9);
                    $notif->tab_parse(Array('%client%' => $last_id, '%prenom_staff%' => $_SESSION['prenom']));
                    $notif->sendMail();
                }

                $_SESSION['notify'] = 'ModifyCustomer';
                header('Location: ./c'.$id_customer);
                die();
            
            }
                
            
        }
    }
}

/*
 * Commentaire
 */

if(isset($_POST['new_comment']) AND !(empty($_POST['new_comment'])))
{
    $texte = $db->SQLFix($_POST['new_comment']);
    $id_customer = $db->SQLfix($_GET['id_customer']);

    $sql = 'INSERT INTO commentaires (id_staff, id_client, texte, timestamp, compte_principal) VALUES ("'.$_SESSION['id'].'", "'.$id_customer.'", "'.$texte.'", "'.time().'", "'.$_SESSION['compte_principal'].'")';
    $db->Query($sql);

    $log = New addLog('a ajouté une commentaire au client');
    $log->setClient($id_customer);
    $log->insert();

    header('location: ./c'.$id_customer.'#tab_tab_comments');
    die();

}

if(isset($_POST['id_note']) AND !(empty($_POST['id_note']))) {
    $texte = $db->SQLFix($_POST['edit_note']);
    $id_customer = $db->SQLfix($_GET['id_customer']);
    $titre = $db->SQLFix($_POST['titre_note']);
    $crypt = 0;
    $id = $db->SQLFix($_POST['id_note']);
    if($_POST['crypt_note'] == 'on')
    {
        require_once('./functions/crypt_decrypt.func.inc');
        $texte = Base64_Encryption::Crypter($texte, CRYPT_KEY);
        $crypt = 1;
    }

    $sql = 'UPDATE notes SET titre="'.$titre.'", texte="'.$texte.'", crypte="'.$crypt.'" WHERE id ="'.$id.'" AND compte_principal="'.$_SESSION['compte_principal'].'" AND id_client="'.$id_customer.'" ';
    $db->Query($sql);

    $log = New addLog('a modifié une note du client');
    $log->setClient($id_customer);
    $log->insert();

    header('location: ./c'.$id_customer.'#tab_tab_notes');
    die();

}
/*
 * ajouts de note
 */
if(isset($_POST['new_note']) AND !(empty($_POST['new_note'])))
{

    $texte = $db->SQLFix($_POST['new_note']);
    $id_customer = $db->SQLfix($_GET['id_customer']);
    $titre = $db->SQLFix($_POST['titre_note']);
    $crypt = 0;
    if($_POST['crypt_note'] == 'on')
    {
        require_once('./functions/crypt_decrypt.func.inc');
        $texte = Base64_Encryption::Crypter($texte, CRYPT_KEY);
        $crypt = 1;
    }


    $sql = 'INSERT INTO notes (id_client, titre, texte, timestamp, crypte, compte_principal) VALUES ( "'.$id_customer.'","'.$titre.'", "'.$texte.'", "'.time().'","'.$crypt.'", "'.$_SESSION['compte_principal'].'")';
    $db->Query($sql);

    $log = New addLog('a ajouté une note au client');
    $log->setClient($id_customer);
    $log->insert();

    header('location: ./c'.$id_customer.'#tab_tab_notes');
    die();

}

/*
//Pack Maintenance :
if(isset($_POST['mt']) and right('pack_maintenance', 2))
{
    $id_customer = $db->SQLfix($_GET['id_customer']);
    global $db;
    $h = $db->SQLFix($_POST['heures']);
    $m = $db->SQLFix($_POST['minutes']);

    $mouvement = round($h + ( $m / 60 ),2);
    if($_POST['plus'] != 'on' AND right('pack_maintenance', 3))
    {
        $mouvement -= $mouvement * 2;

    }
    $d = $db->SQLFix($_POST['date']);
    $tab_mbt = explode('/', $d);
    $date = mktime(0, 0, 0, $tab_mbt[1], $tab_mbt[0], $tab_mbt[2]);

    $comm = $db->SQLFix($_POST['commentaires']);

    $db->Query('INSERT INTO maintenance (id_client, date_mouvement, mouvements, commentaires, compte_principal) VALUES ("'.$id_customer.'", "'.$date.'", "'.$mouvement.'", "'.$comm.'", "'.$_SESSION['compte_principal'].'") ');

    $db->Query('DELETE FROM chrono_maintenance
                WHERE id_client = "'.$id_customer.'"
                AND compte_principal = "'.$_SESSION['compte_principal'].'"
                ');

    $log = New addLog('a ajouté une ligne au pack maintenance du client');
    $log->setClient($id_customer);
    $log->insert();
    header('Location: ./c'.$id_customer);

}*/

/*
if($maj)
{
    global $db;
    $nom = $db->SQLfix($_POST['settings-name']);
    $prenom = $db->SQLfix($_POST['settings-lastname']);
    $mail = $db->SQLfix($_POST['settings-email']);
    $pseudo = $db->SQLfix($_POST['settings-pseudo']);
    $pass = $db->SQLfix($_POST['settings-password']);
    
    $sql = 'UPDATE staffs SET nom = "'.$nom.'"';
    if(!empty($prenom))
        $sql .= ', prenom = "'.$prenom.'"';
    if(!empty($pseudo))
        $sql .= ', pseudo = "'.$pseudo.'"';
    if(!empty($pass))
        $sql .= ', pass = "'.$pass.'"';
    $sql .= ', mail = "'.$mail.'" WHERE id = "'.$id_staff.'" ';
    
    
    $db->Query($sql);
    
    
    if($id_staff == $_SESSION['id']) //on redéfini les superglobales si c'est nous même qu'on a changé
    {
        $db->Query("SELECT prenom, nom, mail FROM staffs WHERE id='".$id_staff."' ");
        echo $db->Error();
        $row = $db->Row();
        $_SESSION['prenom'] = $row->prenom;
        $_SESSION['nom'] = $row->nom;
        $_SESSION['mail'] = $row->mail;
    }
}*/
?>