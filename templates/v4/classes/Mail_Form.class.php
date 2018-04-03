<?php session_start();

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 27/03/2016
 * Time: 16:36
 */
class Mail_Form extends Modal
{
    private $list='';
    private $mail;
    private $script;
    private $id_inter;
    private $ref_chantier;
    private $mails_types=Array();
    private $liste_messages=false;
    private $listmessages='';
    //private $mail_ref;

    public function __construct($title, $modal_id, $id_inter=0, $ref_chantier=0)
    {
        $this->title = $title;
        $this->modal_id = $modal_id;
        $this->id_inter = $id_inter;
        $this->ref_chantier = $ref_chantier;
    }

    private function listeMessage()
    {
        $this->listmessages = new Select('liste_messages', 'liste_message');
        $this->listmessages->withSearch();
        $this->listmessages->onChange("setMail(this.value);");
        $this->listmessages->addOption('0', '--');

        foreach ($this->mails_types AS $mail)
        {
            $this->listmessages->addOption($mail['id'], $mail['nom']);
        }
    }

    public function addListDest($arrayList, $ref='')
    {
        $this->mail = new Hidden('mail', 'mail');

        $this->list = new Select('mail_liste', 'mail_liste');
        $this->list->addOption('0', '--');
        $this->list->withSearch();
        $this->list->onChange("setDestMail(this.value);");
        $needle = '';
        foreach ($arrayList AS $mail)
        {
            if($mail['mail']!= '')
            {
                $this->list->addOption($mail['id'].'_'.$mail['mail'], $mail['nom'].' ('.$mail['mail'].')');
                if($ref == $mail['id']) {
                    $needle = $mail['mail'];
                    $this->mail->setValue($mail['mail']);
                }
            }
            else
            {
                //echo 'ok';
                $this->list->addOption('',  $mail['nom'].'(Aucune adresse e-mail)', false, true);
            }
        }
        if($ref)
            $this->list->setSelected($ref.'_'.$needle);
        $this->script($arrayList);

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

        $this->listeMessage();

    }

    public function script($liste_mails)
    {
        $this->script = '
        <script>
    function setDestMail(dest)
    {
        var tab = dest.split(\'_\');
        document.getElementById(\'mail\').value = tab[1];


        if(tab[0] > 0)
        {

            var mail = document.getElementById(\'liste_messages\').value;
            if(mail > 0)
                setMessageMail(tab[0], mail)
        }
        else
        {
            $(\'#message\').data("wysihtml5").editor.setValue(\'\');
            document.getElementById(\'sujet\').value = \'\';
            document.getElementById(\'titre\').value = \'\';
        }
    }

    function setMail(mail)
    {

        if(mail > 0)
        {
            var dest = document.getElementById(\'mail_liste\').value;
            var tab = dest.split(\'_\');
            if(tab[0] > 0)
                setMessageMail(tab[0], mail)
        }
        else
        {

            $(\'#message\').data("wysihtml5").editor.setValue(\'\');
            document.getElementById(\'sujet\').value = \'\';
            document.getElementById(\'titre\').value = \'\';
        }
    }

    function setMessageMail(dest, mail)
    {
        var messages = new Array();
    ';
        global $db;

        $tab = explode('-', $this->id_inter);
        $c = count($tab);
        $id_inter = $tab[$c-1];


        $db->Query('SELECT external_link FROM interventions WHERE id_inter="'.$id_inter.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
        $row = $db->Row();

        $external_link = 'http://www.mana.gy/'.$row->external_link;
        foreach ($liste_mails AS $mail)
        {


            $db->Query('SELECT titre, nom, prenom, ville FROM clients WHERE id="'.$mail['id'].'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            $titre_c = addslashes($row->titre);
            $nom = addslashes($row->nom);
            $prenom = addslashes($row->prenom);
            $ville  =addslashes($row->ville);
            $this->script .= '
            messages['.$mail['id'].'] = new Array();';

            $db->Query('SELECT c.nom, c.ral
                    FROM couleurs AS c
                    INNER JOIN interventions AS i
                    ON (i.id_couleur = c.id)
                    WHERE i.compte_principal="'.$_SESSION['compte_principal'].'"
                    AND c.compte_principal="'.$_SESSION['compte_principal'].'"
                    AND i.id_inter = "'.$this->id_inter.'"    ');
            
            $row = $db->Row();
            $ral = addslashes($row->ral);
            $nom_couleur = addslashes($row->nom);

            $sql = 'SELECT rdv_debut FROM interventions WHERE id_inter="'.$this->id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ';
            $db->Query($sql);

            $row = $db->Row();
            $date = date('d/m/Y', $row->rdv_debut);
            $heure = date('H\hi', $row->rdv_debut);
            foreach($this->mails_types AS $type)
            {
                $message = addslashes($type['message']);
                $message = str_replace('%id_inter%', $this->id_inter, $message);
                $message = str_replace('%titre%', $titre_c, $message);
                $message = str_replace('%nom%', $nom, $message);
                $message = str_replace('%prenom%', $prenom, $message);
                $message = str_replace('%nom_couleur%', $nom_couleur, $message);
                $message = str_replace('%ral%', $ral, $message);
                $message = addslashes(str_replace('%ref_chantier%', $this->ref_chantier, $message));
                $message = str_replace('%lien_public%', $external_link, $message);
                $message = str_replace('%ville%', $ville, $message);
                $message = str_replace('%date%', $date, $message);
                $message = str_replace('%heure%', $heure, $message);
                $sujet = $type['sujet'];
                $sujet = str_replace('%id_inter%', $this->id_inter, $sujet);
                $sujet = addslashes(str_replace('%ref_chantier%', $this->ref_chantier, $sujet));
                $sujet = str_replace('%nom_couleur%', $nom_couleur, $sujet);
                $sujet = str_replace('%ral%', $ral, $sujet);
                $sujet = str_replace('%ville%', $ville, $sujet);
                $sujet = str_replace('%date%', $date, $sujet);
                $sujet = str_replace('%heure%', $heure, $sujet);
                $titre = $type['titre'];
                $titre = str_replace('%id_inter%', $this->id_inter, $titre);
                $titre = addslashes(str_replace('%ref_chantier%', $this->ref_chantier, $titre));
                $titre = str_replace('%nom_couleur%', $nom_couleur, $titre);
                $titre = str_replace('%ral%', $ral, $titre);
                $titre = str_replace('%ville%', $ville, $titre);
                $titre = str_replace('%date%', $date, $titre);
                $titre = str_replace('%heure%', $heure, $titre);



                $this->script .= '
            messages['.$mail['id'].']['.$type['id'].'] = new Array();
            messages['.$mail['id'].']['.$type['id'].'][\'message\'] = \''.$message.'\';
            messages['.$mail['id'].']['.$type['id'].'][\'sujet\'] = \''.$sujet.'\';
            messages['.$mail['id'].']['.$type['id'].'][\'titre\'] = \''.$titre.'\';

                ';

            }
        }

        $this->script .= '
        var message = messages[dest][mail][\'message\'];
        var sujet = messages[dest][mail][\'sujet\'];
        var titre = messages[dest][mail][\'titre\'];
        $(\'#message\').data("wysihtml5").editor.setValue(message.replace(/\\\/g, \'\'));
        //$(\'#message\').val(message.replace(/\\\/g, \'\')).blur();
        //document.getElementById(\'message\').value = message.replace(/\\/g, \'\');
        document.getElementById(\'sujet\').value = sujet.replace(/\\\/g, \'\');
        document.getElementById(\'titre_mail\').value = titre.replace(/\\\/g, \'\');
    }

</script>';


    }

    public function putContent($id_form)
    {
        $form = new FormLayout('Saisie');
        $form->setFormControls($id_form);

        if(!empty($this->list))
            $form->addLine('Choisir : ', $this->list.$this->mail);

        if($this->liste_messages)
            $form->addLine('Message : ', $this->listmessages);

        $sujet = new Text('sujet', 'sujet');
        $form->addLine('Sujet', $sujet);

        $titre = new Text('titre', 'titre_mail');
        $form->addLine('Titre', $titre);

        $message = new Textarea('message', 'message');
        //$message->elastic();
        $message->Wysiwyg();
        $message->setRows(10);
        $form->addLine('Message : ', $message);

        if(AccesActivableModul('automatismes')) {
            $differe = new CheckBox('differe');
            $differe->onChange("$('#dif_mail_date').toggle('display'); $('#dif_mail_heure').toggle('display');");

            $form->addLine('Programmer l\'envoie', $differe);

            $date = new Text('differe_date');
            $date->datePicker();
            $date->setValue(date('d/m/Y'));
            $form->addLine('Date', $date, false, 'dif_mail_date', 'display: none;');

            $heure = new Text('differe_heure');
            $heure->timePicker();

            $heures = date('H') + 1;
            $modulo = date('i') % 5;
            if($modulo < 3)
                $minute = date('i') - $modulo;
            else
                $minute = date('i') + (5 - $modulo);
            //die();
            $heure->setValue($heures.':'.$minute);
            $form->addLine('Heure', $heure, false, 'dif_mail_heure', 'display: none;');
        }

        $this->setContent($this->script.$form);

    }





}