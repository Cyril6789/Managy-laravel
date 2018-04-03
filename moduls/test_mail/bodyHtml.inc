<?php session_start();

    
        /*$mail = new Maileur('Managy.fr vous informe');
        $mail->addDest('contact@depaninfo67.com');
        $mail->addDest('be_someone@live.fr');
        $mail->AddTitle('Super Classe mail terminée !');
        $mail->body('Nouveau maileur disponible sur managy.fr');
        $mail->send();
        */

        




            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/pages/test_mail.php');
            $ModulContent = ob_get_contents();
            ob_end_clean();

?>