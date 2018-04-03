<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 27/03/2016
 * Time: 18:16
 */
class SMS_form extends Modal
{
    private $script;
    private $id_inter;
    private $ref_chantier;
    private $sms_types = Array();
    private $liste_messages=false;
    private $liste_sms;
    private $ref;

    public function __construct($title, $modal_id, $id_inter=0, $ref_chantier=0)
    {
        $this->title = $title;
        $this->modal_id = $modal_id;
        $this->id_inter = $id_inter;
        $this->ref_chantier = $ref_chantier;
    }


    public function addListDest($liste, $ref='')
    {
        $this->liste_sms = Array();
        $this->liste_sms = $liste;
        $this->ref = $ref;

        $this->script($liste);
    }

    public function script($liste_sms)
    {
        $this->script ='
<script>


        function setDestSms(dest)
        {
                var tab = dest.split(\'_\');
                document.getElementById(\'num\').value = tab[1];


                if(tab[0] > 0)
                {
                        var sms = document.getElementById(\'liste\').value;
                        if(sms > 0)
                                setMessageSms(tab[0], sms)
                }
                else
                        document.getElementById(\'texte\').value = \'\';
        }

        function setSms(sms)
        {

                if(sms > 0)
                {
                        var dest = document.getElementById(\'sms\').value;
                        var tab = dest.split(\'_\');
                        if(tab[0] > 0)
                                setMessageSms(tab[0], sms)
                }
                else
                        document.getElementById(\'texte\').value = \'\';
        }

        function setMessageSms(dest, sms)
        {
            var messages = new Array();';

        foreach ($liste_sms AS $sms)
        {

            if(is_numeric($this->id_inter))
                $id_i = $this->id_inter;
            else
            {
                $tmp = explode('-', $this->id_inter);
                $id_i = $tmp[count($tmp) - 1];
            }
            global $db;
            $sql = 'SELECT external_link FROM interventions WHERE id_inter="'.$id_i.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ';
            $db->Query($sql);
            $row = $db->Row();

            $external_link = 'http//mana.gy/'.$row->external_link;


            $db->Query('SELECT titre, nom, prenom, ville FROM clients WHERE id="'.$sms['id'].'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            $titre_c = addslashes($row->titre);
            $nom = addslashes($row->nom);
            $prenom = addslashes($row->prenom);
            $ville = addslashes($row->ville);
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
            $this->script .= '
                messages['.$sms['id'].'] = new Array();';

            $sql = 'SELECT rdv_debut FROM interventions WHERE id_inter="'.$this->id_inter.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ';
            $db->Query($sql);
            //echo $sql;
            //die();
           
            $row = $db->Row();
            $date = date('d/m/Y', $row->rdv_debut);
            $heure = date('H\hi', $row->rdv_debut);


            foreach($this->sms_types AS $type)
            {
                $message = addslashes($type['message']);
                $message = str_replace('%id_inter%', $this->id_inter, $message);
                $message = str_replace('%titre%', $titre_c, $message);
                $message = str_replace('%nom%', $nom, $message);
                $message = str_replace('%prenom%', $prenom, $message);
                $message = addslashes(str_replace('%ref_chantier%', $this->ref_chantier, $message));
                $message = str_replace('%nom_couleur%', $nom_couleur, $message);
                $message = str_replace('%ral%', $ral, $message);
                $message = str_replace('%lien_public%', $external_link, $message);
                $message = str_replace('%ville%', $ville, $message);
                $message = str_replace('%date%', $date, $message);
                $message = str_replace('%heure%', $heure, $message);
                $this->script .= '
                messages['.$sms['id'].']['.$type['id'].'] = \''.$message.'\'';

            }
        }


        $this->script .= '
            var message = messages[dest][sms];

                document.getElementById(\'texte\').value = message.replace(/\\\/g, \'\');
        }

</script>';
    }

    public function displayListe()
    {
        $this->liste_messages = true;

        global $db;
        $db->Query('SELECT * FROM sms_types WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');
        while($row = $db->Row())
        {
            $this->sms_types[$row->id]['id'] = $row->id;
            $this->sms_types[$row->id]['titre'] = $row->titre;
            $this->sms_types[$row->id]['message'] = $row->message;
        }

    }

    public function putContent($id_form)
    {
        $form = new FormLayout('Saisie');
        $form->setFormControls($id_form);

        /*if(!empty($this->list))
            $form->addLine('Choisir : ', $this->list.$this->mail);

        if($this->liste_messages)
            $form->addLine('Message : ', $this->listmessages);*/

        if(!empty($this->liste_sms))
        {
            $listing = new Select('sms', 'sms');
            $listing->withSearch();
            $listing->onChange("setDestSms(this.value);");

            $listing->addOption('0', '--');
            $needle='';
            foreach ($this->liste_sms AS $sms)
            {
                if($sms['sms']!= '')
                {
                    $listing->addOption($sms['id'].'_'.$sms['sms'], $sms['nom'].' '.$sms['prenom'].' ('.$sms['sms'].')');
                    if($this->ref == $sms['id']) {
                        $needle = $sms['sms'];

                    }

                }
                else
                {
                    $listing->addOption($sms['id'].'_'.$sms['sms'], $sms['nom'].' '.$sms['prenom'].' (Pas de numéro)', false, true);
                }
            }

            if($this->ref)
                $listing->setSelected($this->ref.'_'.$needle);

            $form->addLine('Destinataire : ', $listing);
        }

        if($this->liste_messages)
        {
            $listmessage = new Select('liste', 'liste');
            $listmessage->withSearch();
            $listmessage->onChange("setSms(this.value);");
            $listmessage->addOption('0', '--');

            foreach ($this->sms_types AS $sms)
            {
                $listmessage->addOption($sms['id'], $sms['titre']);
            }

            $form->addLine('SMS type : ', $listmessage);
        }

        $message = new Textarea('texte', 'texte');
        $message->setRows(3);

        $num = new hidden('num', 'num');
        $num->setValue($needle);

        $form->addLine('Message : ', $message.$num);

        if(AccesActivableModul('automatismes')) {
            $differe = new CheckBox('differe');
            $differe->onChange("$('#dif_sms_date').toggle('display'); $('#dif_sms_heure').toggle('display');");

            $form->addLine('Programmer l\'envoie', $differe);

            $date = new Text('differe_date');
            $date->datePicker();
            $date->setValue(date('d/m/Y'));
            $form->addLine('Date', $date, false, 'dif_sms_date', 'display: none;');

            $heure = new Text('differe_heure');
            $heure->timePicker();

            $heures = date('H') + 1;
            $modulo = date('i') % 5;
            if($modulo < 3)
                $minute = date('i') - $modulo;
            else
                $minute = date('i') + (5 - $modulo);


            $heure->setValue($heures.':'.$minute);
            $form->addLine('Heure', $heure, false, 'dif_sms_heure', 'display: none;');
        }

        $this->setContent($this->script.$form);

    }

}