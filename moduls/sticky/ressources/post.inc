<?php session_start();
 //GESTION DES POSTS
if(right('sms', 2))
{
    if(isset($_POST['qte_sms']) AND !empty($_POST['qte_sms']) AND is_numeric($_POST['qte_sms']))
    {
        global $db;
        $qte = $db->SQLfix($_POST['qte_sms']);
        $nouvelle_qte = $qte + ($_SESSION['commande_sms'] - $_SESSION['conso_sms']);
        
        $db->Query('SELECT COUNT(*) AS nb FROM stocks_sms WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');
        $row=$db->Row();
        if($row->nb)
            $db->Query('UPDATE stocks_sms SET commande = "'.$nouvelle_qte.'", conso = 0 WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');
        else
            $db->Query('INSERT INTO stocks_sms (commande, compte_principal) VALUES ("'.$nouvelle_qte.'", "'.$_SESSION['compte_principal'].'") ');
        
        $mail = new Maileur('Commande de sms');
        $mail->addDest('contact@depaninfo67.com');
        $mail->AddTitle($qte.' SMS commandés !');
        $mail->addExpediteur(MAIL_CONTACT, NOM_SOCIETE." via Managy");
        $mail->setLogo(LOGO);
        $mail->body('Salut Cyril !<br /><br />'.$_SESSION['prenom'].' '.$_SESSION['nom'].' vient de commander un pack de <strong>'.$qte.'</strong> SMS sur Managy.fr !<br />Merci d\'envoyer une facture à '.NOM_SOCIETE);
        $mail->send();      
        
        $mail = new Maileur('Votre commande');
        $mail->addDest(MAIL_CONTACT);
        $mail->AddTitle('Votre commande de '.$qte.' SMS');
        $mail->addExpediteur('contact@depaninfo67.com', "Dépan'Info 67");
        $mail->setLogo('di67');
        $mail->addSignature('Cyril Heilmann - Dépan\'Info 67', 'www.depaninfo67.com', 'contact@depaninfo67.com', '06.87.47.00.91');
        $mail->body('M '.$_SESSION['nom'].' '.$_SESSION['prenom'].',<br /><br />Votre commande de <strong>'.$qte.'</strong> SMS sur Managy.fr a été traitée. Vous recevrez votre facture dans les plus brefs délais. <br /><br />Merci et à bientôt sur Managy.fr !');
        $mail->send(); 
        
        
        $_SESSION['commande_sms'] = $nouvelle_qte;
        $_SESSION['conso_sms'] = 0;
        
    }
}
        
        
?>