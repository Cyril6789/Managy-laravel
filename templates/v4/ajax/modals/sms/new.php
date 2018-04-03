<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 02/09/2017
 * Time: 15:40
 */

$modal = new jsonModal(new Font('comment-o').' Envoyer un SMS');
$modal->width("45%");
$modal->hideButtons();

function parse_js($needle)
{
    return preg_replace("#\n|\t|\r#", "", addslashes($needle));
}

if(AccesActivableModul('sms'))
{
    if(right('sms', 3))
    {
        $client = new GetInfosCustomer($_POST['id_client']);

        if($client->getId())
        {
            if(!empty($_POST['id_inter'])) {
                $inter = new DataObject('interventions');
                $inter->find($_POST['id_inter'], 'id_inter');
            }

            if ($inter->id_client == $client->getId() OR empty($_POST['id_inter']))
            {



                $stock_sms = new DataObject('stocks_sms');
                $stock_sms->find(15, 'compte_principal');


                $credit = $stock_sms->commande - $stock_sms->conso;

                if ($credit < 1)
                    $modal->hideButtons();

                $content = new FormLayout('Envoie de SMS : (' . $credit . ' SMS restants)');
                $content->setFormControls('form_sms');
                $modal->form_id('form_sms');

                // particulier


                $array_destinataire = Array();

                $liste_dest = new Select('destinataire');
                $liste_dest->onChange("load()");

                $selected = false;

                if ($client->getPort()) {
                    $disabled = false;
                    $num = $client->getPort();
                    $selected = true;
                } else {
                    $num = 'Aucun numéro';
                    $disabled = true;
                    $selected = false;
                }

                $liste_dest->addOption($client->getId().'_'.$client->getPort(), $client->getFullName() . ' : ' . $num, $selected, $disabled);

                $array_destinataire[$client->getId()] = $client;
                $ville = $client->getVille();

                if ($client->getPropart() == '1') //Chargement des contacts
                {
                    $contacts = $client->loadContacts();

                    foreach ($contacts AS $contact) {
                        if ($contact->getPort()) {
                            $disabled = false;
                            $num = $contact->getPort();
                            if (!$selected) {
                                $select = true;
                                $selected = true;
                            } else
                                $select = false;
                        } else {
                            $num = 'Aucun numéro';
                            $disabled = true;
                        }

                        $liste_dest->addOption($contact->getId().'_'.$contact->getPort(), $contact->getFullName() . ' - ' . $num, $select, $disabled);
                        $array_destinataire[$contact->getId()] = $contact;


                    }
                }

                if (!$selected)
                    $modal->hideButtons();

                $content->addLine('', '', false, 'error_msg_sms', 'display: none;');

                $content->addLine('Destinataire', $liste_dest);

                if($inter->id_client == $client->getId()) //SMS types
                {
                    $sms_type  = new Select('sms_type');
                    $sms_type->onChange("load()");
                    $sms_type->withSearch();
                    $sms_type->addOption('0', '--');
                    $sms = new DataObject('sms_types');
                    $liste = $sms->findAll();
                    $array_sms = Array();
                    foreach ($liste AS $s) {
                        $sms_type->addOption($s->id, $s->titre);
                        $array_sms[$s->id] = $s->message;
                    }
                    $content->addLine('SMS type', $sms_type);

                }

                $js = '';
                if(!empty($_POST['id_inter']))
                {
                    $js = '
                    <script>
                        function load()
                        {
                            var tab_message = new Array();    
                        ';
                    foreach ($array_destinataire AS $a_d)
                    {
                        $js .= 'tab_message['.$a_d->getId().'] = new Array();
                        ';
                        foreach ($array_sms AS $key => $a_s)
                        {
                            $message = str_replace('%titre%', $a_d->GetTitre(), $a_s);
                            $message = str_replace('%nom%', $a_d->GetNom(), $message);
                            $message = str_replace('%prenom%', $a_d->GetPrenom(), $message);
                            $message = str_replace('%id_inter%', $inter->prefix.$inter->id_inter, $message);
                            $message = str_replace('%ref_chantier%', $inter->ref_chantier, $message);
                            $message = str_replace('%ville%', $ville, $message);
                            $message = str_replace('%heure%', date('H:i', $inter->rdv_debut), $message);
                            $message = str_replace('%date%', date('d/m/Y', $inter->rdv_debut), $message);
                            $message = str_replace('%lien_public%', 'http://mana.gy/'.$inter->external_link, $message);

                            $js .= 'tab_message['.$a_d->getId().']['.$key.'] = \''.addslashes($message).'\';
                            ';
                        }
                    }
                    $js .=
                        '      
                            var id_c =  $(\'#destinataire\').val().split(\'_\');
                            var id_s = $(\'#sms_type\').val();
                            $(\'#message_sms\').val(tab_message[id_c[0]][id_s]);
                        }
                    </script>
                    ';
                }

                $message = new Textarea('message_sms');
                $message->setRows(3);
                $content->addLine('Message', $message.$js);

                $signature = new Text('signature');
                $signature->setValue(SIGNATURE_SMS);
                $signature->disabled();

                $content->addLine('Signature', $signature);

                if (AccesActivableModul('automatismes')) {
                    $differe = new CheckBox('differe');
                    $differe->onChange("$('#dif_sms_date').toggle('display'); $('#dif_sms_heure').toggle('display');");

                    $content->addLine('Programmer l\'envoie', $differe);

                    $date = new Text('differe_date');
                    $date->datePicker();
                    $date->setValue(date('d/m/Y'));
                    $content->addLine('Date', $date, false, 'dif_sms_date', 'display: none;');

                    $heure = new Text('differe_heure');
                    $heure->timePicker();

                    $heures = date('H') + 1;
                    $modulo = date('i') % 5;
                    if ($modulo < 3)
                        $minute = date('i') - $modulo;
                    else
                        $minute = date('i') + (5 - $modulo);


                    $heure->setValue($heures . ':' . $minute);
                    $content->addLine('Heure', $heure, false, 'dif_sms_heure', 'display: none;');


                    $submit_button = new Button('Envoyer le SMS', "javascript:void;", 'Envoyer le SMS');
                    $submit_button->setClasse('btn-primary');
                    $submit_button->onClick("sms_submit();");

                    $submit_js = '
                    <script>
                        function sms_submit()
                        {
                           $(\'#error_msg_sms\').html(\''.parse_js(new Danger('test', false)).'\');
                           $(\'#error_msg_sms\').show(\'slow\');
                           $(\'#sms_new\').modal(\'hide\');
                        }
                    </script>
                    ';

                    $content->addLine('', $submit_button.$submit_js);
                }
            } else {
                $content = new Danger('Cette intervention n\'existe pas pour ce client');
                $modal->error();
            }
        }
        else {
            $content = new Danger('Ce client n\'existe pas', false);
            $modal->error();
        }
    }
    else {
        $content = new Danger('Vous n\'avez pas le droit d\'envoyer un SMS', false);
        $modal->error();
    }
}
else {
    $content = new Danger('Ce module n\'est pas activé sur votre compte.', false);
    $modal->error();
}

$modal->content($content);
echo $modal;