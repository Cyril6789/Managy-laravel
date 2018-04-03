<?php session_start();

Class MailForm{
    
    private $html;
    private $write_mail;
    private $dialog;
    private $id_dialog;
    private $id_button;
    private $button;
    private $width;
    private $sujet;
    private $titre_m;
    private $message;
    private $destinaires;
    private $liste_mail;
    private $mails_types;
    private $liste_messages;
    private $id_inter;
    private $ref_chantier;


    public function __construct($id_inter=0, $ref_chantier=0) 
    {
        $this->id_inter = $id_inter;
        $this->ref_chantier = $ref_chantier;
        $this->write_mail = true;
        $this->dialog = false;
        $this->id_dialog = "dialog_form_simple";
        $this->html = '';
        $this->liste_messages = false;

    }
    
    public function addSujet($sujet)
    {
        $this->sujet = $sujet;
    }

    public function addTitre($titre)
    {
        $this->titre_m = $titre;
    }

    public function addMessage($message)
    {
        $this->message = $message;
    }
    public function addDest($email)
    {
        if(empty($this->destinaires))
            $this->destinaires = $email;
        else
            $this->destinaires .= ', '.$email;
    }
    
    public function addListDest($liste)
    {
        $this->liste_mail = Array();
        $this->liste_mail = $liste;
        $this->write_mail = False;
    }
    
    private function html() 
    {
        if($this->dialog)
        {
            
            if(is_file('./moduls/mail/blocs/button.inc'))
            {
                
                $id_button = $this->id_button;
                $button = $this->button;
                ob_start();
                include('./moduls//mail/blocs/button.inc');
                $this->html .= ob_get_contents();
                ob_end_clean();
            }    
        }
        
        
        
        if(is_file('./moduls/mail/blocs/form.inc'))
        {
            $id_inter = $this->id_inter;
            $ref_chantier = $this->ref_chantier;
            $write_mail = $this->write_mail;
            $emails = $this->destinaires;
            $liste_mails = $this->liste_mail;
            $liste_messages = $this->liste_messages;
            $titre_m = $this->titre_m;
            $sujet = $this->sujet;
            $message = $this->message;
            $mails_types = $this->mails_types;
            if($this->dialog)
                $dialog = true;
            $id_dialog = $this->id_dialog;
            $titre_dialog =  $this->titre;
            ob_start();
            include('./moduls/mail/blocs/form.inc');
            $this->html .= ob_get_contents();
            ob_end_clean();
            if($this->dialog)
                $this->script();
        }
    }
    
    public function displayListe() 
    {
        $this->liste_messages = true;
        
        global $db;
        $db->Query('SELECT * FROM mails_types WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');
        while($row = $db->Row())
        {
            $this->mails_types[$row->id]['id'] = $row->id;
            $this->mails_types[$row->id]['titre'] = $row->titre;
            $this->mails_types[$row->id]['nom'] = $row->nom;
            $this->mails_types[$row->id]['sujet'] = $row->sujet;
            $this->mails_types[$row->id]['message'] = $row->message;
        }
        
    }
    
    private function script()
    {
        if(is_file('./moduls//mail/blocs/script.inc'))
        {
            $id_button = $this->id_button;
            $id_dialog = $this->id_dialog;
            $width = $this->width;
            ob_start();
            include('./moduls//mail/blocs/script.inc');
            $this->html .= ob_get_contents();
            ob_end_clean();
        }
    }

    public function setToDialog($titre, $button='Envoyer mail', $id_button='dialog_form_btn', $id_dialog='dialog_form', $width="600") 
    {
        $this->titre = $titre;
        $this->dialog = true;
        $this->id_button = $id_button;
        $this->id_dialog = $id_dialog;
        $this->button = $button;
        $this->width = $width;
    }

    public function __toString() 
    {
        
        $this->html();
        return $this->html;        
    }
    
    
}

?>