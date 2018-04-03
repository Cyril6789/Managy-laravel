<?php session_start();
require_once('./templates/v4/classes/includes.php');
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 17/02/2016
 * Time: 11:39
 */



/*
 * Sellsy
 */

if(AccesActivableModul('sellsy'))
{
    $client_sellsy = new checkCustomer($id_customer);
    $right_title = $client_sellsy;
}


?>
<script>
    function unlock(id)
    {
        $('#unlock_id').val(id);
        $('#modal_unlock').modal('show');
    }

    function unlockpass()
    {

        $.ajax({
            url : './ajax/unlock.php?id='+$('#unlock_id').val()+'&pass='+$('#unlock_pass').val(),
            type : 'GET',
            dataType : 'html',
            complete : function(resultat, statut){
                var i =$('#unlock_id').val();
                var t = i.split('_');
                if (t[0] == 'editnote')
                {
                    $('#edit_note_'+t[1]).modal('show');
                    $('#editnote_'+t[1]).val(resultat.responseText);
                }
                else {

                    $('#' + $('#unlock_id').val()).html('<img src="./img/loading_spinner.gif" width="30px"/> Analyse de votre mot de passe et decryptage de l\'élement.');

                    $('#unlock_pass').val('');
                    setTimeout(function () {
                        $('#' + $('#unlock_id').val()).html(resultat.responseText);
                    }, 2000);
                }

                //

            }
        });
    }

    function delete_service(id)
    {
        if(confirm('Êtes vous sûr de vouloir supprimer ce service ?'))
            document.location.href = '?suppr_service='+id;
    }

    function delete_note(id)
    {
        if(confirm('Êtes vous sûr de vouloir supprimer cette note ?'))
            document.location.href = '?suppr_note='+id;
    }


    function toggle_archiver() {
        if ($('input[id=archiver]').is(':checked')) {
            klient = new XMLHttpRequest();
            klient.open("GET", "./ajax/archiver.php?id_customer=<?php echo $id_customer;?>&action=a");
            klient.send(null);
            $('#message_archive').show('slow');
            $('#btn_create_inter').attr('disabled', true);
            $('#btn_create_inter').removeClass('btn-primary');
            $('#btn_create_inter').addClass('btn-danger');
        }
        else {
            klient = new XMLHttpRequest();
            klient.open("GET", "./ajax/archiver.php?id_customer=<?php echo $id_customer;?>&action=d");
            klient.send(null);
            $('#message_archive').hide('slow');
            $('#btn_create_inter').attr('disabled', false);
            $('#btn_create_inter').removeClass('btn-danger');
            $('#btn_create_inter').addClass('btn-primary');
        }
    }
</script>
<?php
($row->pro_part == 1)? $sub_title = CUSTOMERS_PRO : $sub_title =CUSTOMERS_PART;

$tab = new Tab();
$tab->fullWidth();


if ($pro_part == "1") // pro
{
    $client = new Accordion('Coordonnées', 12);
    $client->addContent($row->titre.' '.$row->prenom.' '.$row->nom, INTERVENTION_ADRESS . ' : ' . $row->adresse . ' ' . $cpy_adress_suite . ' ' . $row->adresse_suite . ' ' . $row->cp.' ' .$row->ville. '<br />' . INTERVENTION_FIXED_PHONE . ' : ' . $row->fixe . '<br>' . INTERVENTION_MOBILE_PHONE . ' : ' . $row->portable . '<br />' . INTERVENTION_EMAIL . ' : ' . $row->mail);


    $i = 1;
    foreach($tab_contacts AS $contact)
    {

        $suppr_contact_btn = new Button('<i class="fa fa-remove"></i> Dissocier ce contact', './customers-suppcontact-'.$row->id.'-'.$contact['id']);
        $suppr_contact_btn->setClasse('btn-danger btn-sm');
        $suppr_contact_btn->setFullWidth();
        $client->addContent(CUSTOMERS_CONTACT.' '.$i.' : '.$contact['nom'].' '.$contact['prenom'], CUSTOMERS_CUSTOMER_NAME.' : <a href="./customers-customer-'.$contact['id'].'">'.$contact['titre'].' '.$contact['nom'].' '.$contact['prenom'].'</a><br /><br />'.$suppr_contact_btn);
        $i++;
    }

    $add_contact_btn = new Button('<i class="fa fa-link"></i> Lier un contact', './customers-addcontact-'.$row->id);
    $add_contact_btn->setClasse('btn-primary btn-sm');
    $add_contact_btn->setFullWidth();
    $client->addContent('Nouveau contact', $add_contact_btn);

}
else //particulier
{
    $client = new WidgetBox($row->titre.' '.$row->prenom.' '.$row->nom, 12);

    if($row->pro_part == 2)
        $parent = '<a href="./c'.$parent_society_id.'">'.$parent_society_name.'</a>';
    else
        $parent = '';


    $client->setContent(INTERVENTION_ADRESS . ' : ' . $row->adresse . ' ' . $row->adresse_suite . ' ' . $row->cp . ' ' . $row->ville . '<br />' . INTERVENTION_FIXED_PHONE . ' : ' . $row->fixe . '<br>' . INTERVENTION_MOBILE_PHONE . ' : ' . $row->portable . '<br />' . INTERVENTION_EMAIL . ' : ' . $row->mail.'<br />'.CUSTOMERS_LINKED_SOCIETY.' : '.$parent);
}


$coor = new Col(4, 'md');

$acces_rapide = new WidgetBox('Accès rapide');



$new_inter_btn = new Button('<i class="fa fa-plus-circle"></i> Créer une intervention', './intervention-new-customer-' . $id_customer, '', 'btn_create_inter');
$new_inter_btn->setClasse('btn-primary');
if($archive) {
    $new_inter_btn->setClasse('btn-danger');
    $new_inter_btn->disable();
}
$new_inter_btn->setFullWidth();


$dl_vcard = new Button('<i class="fa fa-address-card"></i> Télécharger la vCard', './cdv-'.$id_customer);
$dl_vcard->setClasse('btn-primary');
$dl_vcard->setFullWidth();

$acces_rapide->setContent($new_inter_btn.$dl_vcard, false);

$coor->setContent($client.$acces_rapide);



$widget_graphique = new WidgetBox('Interventions ces 12 derniers mois', 4);

$graph_inter = new GraphsLines('graph_inters', '260px');
$graph_inter->addColor('#67B7DC', 'Intervention atelier ');

$tab_mois = Array('Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aôu', 'Sep', 'Oct', 'Nov', 'Déc');

$table_time_atelier = Array();
$table_exist_atelier = array();
foreach($tab_graph AS $val)
{
    if($val['type'] == 1)
    {
        $time = $tab_mois[$val['mois'] - 1];
        $table_time_atelier[] = $time;
        $table_exist_atelier[$time]['val'] = $val['nb'];
        //$graph_inter->addData($val['nb'], $time);
    }

}

$y = date('Y');
for($m = date('n') - 11; $m<=date('n'); $m++) {
    if ($m < 1) {
        $mois = $m + 12;
        $annee = $y - 1;

    } else {
        $mois = $m;
        $annee = $y;
    }
    $timestamp = $tab_mois[$mois - 1];

    if(!in_array($timestamp, $table_time_atelier))
        $graph_inter->addData(0, $timestamp);
    else
        $graph_inter->addData($table_exist_atelier[$timestamp]['val'], $timestamp);
}


$graph_inter->addColor('yellow', 'Intervention sur site');
$table_time_site = Array();
$table_exist_site = array();

foreach($tab_graph AS $val)
{
    if($val['type'] == 2)
    {
        $time = $time = $tab_mois[$val['mois'] - 1];
        $table_time_site[] = $time;
        $table_exist_site[$time]['val'] = $val['nb'];
        //$graph_inter->addData($val['nb'], $time);
    }

}

for($m = date('n') - 11; $m<=date('n'); $m++) {
    if ($m < 1) {
        $mois = $m + 12;
        $annee = $y - 1;

    } else {
        $mois = $m;
        $annee = $y;
    }
    $timestamp = $tab_mois[$mois - 1];

    if(!in_array($timestamp, $table_time_site))
        $graph_inter->addData(0, $timestamp);
    else
        $graph_inter->addData($table_exist_site[$timestamp]['val'], $timestamp);
}

$widget_graphique->setContent($graph_inter, false);





$widget_google_map = new WidgetBox('Carte', 4);
$widget_google_map->setContent($gmap->getGoogleMap(), false);



$tab2 = new Tab('left');



$table_inters = new HtmlTable('', 'table table-bordered table-hover table-responsive table-datatable');
$table_inters->addTSection('thead');
$table_inters->addRow();
$table_inters->addCell('#');
$table_inters->addCell('Ouvert le');
$table_inters->addCell('Type');
$table_inters->addCell('Panne');
$table_inters->addCell('Résolution');
$table_inters->addCell('Cloturé le');

$table_inters->addTSection('tbody');

foreach ($tab_inters AS $value_inter)
{
    $table_inters->addRow('', array('onclick'=>'document.location=\'./intervention-'.$value_inter['id'].'\'', 'style'=>'cursor: pointer;'));
    if($value_inter['cloture'])
        $color = 'success';
    else
    {
        if($value_inter['pec'])
            $color = 'warning';
        else
            $color = 'danger';
    }

    $zero = '';
    $lg = strlen($value_inter['id']);
    for($i=$lg; $i<4; $i++)
        $zero .= '0';


    $num = new Label($color, $value_inter['prefix'].$zero.$value_inter['id'], './i'.$value_inter['id']);

    $table_inters->addCell($num);
    $table_inters->addCell(parse_date($value_inter['ouverture']));
    if($value_inter['type_a_r'] == 1)
        $table_inters->addCell($value_inter['materiel']);
    else
    {
        if($value_inter['rdv_annule'])
            $table_inters->addCell('Rendez-vous annulé');
        else
            $table_inters->addCell('Intervention sur site le '.parse_date($value_inter['rdv_debut']));
    }

    $table_inters->addCell(nl2br($value_inter['panne']));
    $table_inters->addCell(nl2br($value_inter['resolution']));
    if($value_inter['cloture'])
        $table_inters->addCell(parse_date($value_inter['cloture']));
    else
        $table_inters->addCell('Non clôturé');

}


$tab2->addPane('Interventions', $table_inters);


/*
 * Commentaires
 */
$add_comment_widget = new WidgetBox('Ajouter un commentaire');
$textarea = new Textarea('new_comment');
$textarea->placeholder('Ajouter un commentaire');
$textarea->elastic();

$form_add_comment = new Form('form_add_comment');
$form_add_comment->setContent($textarea);

$button_add_comment = new Button('Ajouter le commentaire', 'javascript:void()', 'Ajouter le commentaire en tant que '.$_SESSION['prenom']);
$button_add_comment->setClasse('btn-primary');
$button_add_comment->onClick("form_add_comment.submit();");


$add_comment_widget->setContent($form_add_comment.'<br />'.$button_add_comment);

$commentaires = '';

foreach ($tab_commentaires AS $com)
{
    $staff = new GetInfosStaff($com['id_staff']);
    $widget_com = new WidgetBox('<a href="'.$staff->GetProfileLink().'">'.$staff->GetPrenom().' '.$staff->GetNom().'</a> - '.parse_date($com['timestamp']));

    $button_suppr = new Button('<i class="fa fa-remove"></i>', '?suppr_comment='.$com['id'], 'Supprimer ce commentaire');
    $button_suppr->setClasse('btn-xs btn-danger');

    if($_SESSION['id'] == $com['id_staff'] OR $_SESSION['gerant'])
        $widget_com->addToolbarButtons($button_suppr);

    $texte = strip_tags($com['texte']);

    $texte = preg_replace('#http://[a-z0-9._/-?-{}&%\#]+#i', '<a href="$0" target="_blank">$0</a>', $texte);
    $texte = preg_replace('#https://[a-z0-9._/-?-{}&%\#]+#i', '<a href="$0" target="_blank">$0</a>', $texte);

    $widget_com->setContent(nl2br($texte));

    $commentaires .= $widget_com;
}


if($nb_commentaires) {
    $badge = new Label('info', $nb_commentaires);
    $tab2->addPane('Commentaires ' . $badge, $add_comment_widget . $commentaires, 'comments');
}
else
    $tab2->addPane('Commentaires', $add_comment_widget.$commentaires, 'comments');

/*
 * Notes
 */

$add_note_widget = new WidgetBox('Ajouter une note');
$titre = new Text('titre_note');
$titre->placeHolder('Titre de la note');

$textarea = new Textarea('new_note');
$textarea->placeholder('Ajouter une note');
$textarea->elastic();

$chkbx = new CheckBox('crypt_note');

$form_add_note = new FormLayout('Saisie');
$form_add_note->setWidth(2);
$form_add_note->setFormControls('form_add_note');
$form_add_note->addLine('titre', $titre);
$form_add_note->addLine('Texte', $textarea);
$form_add_note->addLine('Protéger cette note', $chkbx);



$button_add_note = new Button('Ajouter la note', 'javascript:void()', 'Ajouter la note');
$button_add_note->setClasse('btn-primary');
$button_add_note->onClick("form_add_note.submit();");


$add_note_widget->setContent($form_add_note.'<br />'.$button_add_note);


$content_pane = $add_note_widget;
foreach ($tab_notes AS $note) {



    $widget_note = new WidgetBox($note['titre'] . ' - Créée ' . parse_date($note['timestamp']));


    $modal_edit_note = new Modal('Modifier une note', 'edit_note_'.$note['id']);



    $titre = new Text('titre_note');
    $titre->setValue($note['titre']);
    $textarea = new Textarea('edit_note', 'editnote_'.$note['id']);
    $textarea->setValue($note['texte']);
    $textarea->elastic();
    $chkbx = new CheckBox('crypt_note');
    if($note['crypte'])
        $chkbx->checked();
    $form_add_note = new FormLayout('Saisie');
    $form_add_note->setWidth(2);
    $form_add_note->setFormControls('form_edit_note_'.$note['id']);
    $hidden = new Hidden('id_note');
    $hidden->setValue($note['id']);
    $form_add_note->addLine('titre', $titre.$hidden);
    $form_add_note->addLine('Texte', $textarea);
    $form_add_note->addLine('Protéger cette note', $chkbx);

    $modal_edit_note->setContent($form_add_note);
    $modal_edit_note->setOnclickButton('Modifier cette note', '$(\'#form_edit_note_'.$note['id'].'\').submit();');
    $dropdown = New DropdownButton();


    if($note['crypte'])
        $dropdown->addSubButton('<i class="fa fa-pencil"></i> Editier', 'javascript:void();', 'unlock(\'editnote_'.$note['id'].'\')');
    else
        $dropdown->addSubButton('<i class="fa fa-pencil"></i> Editier', $modal_edit_note->getAhref());
    $dropdown->addSubButton('<i class="fa fa-remove"></i> Supprimer', 'javascript:void();', 'delete_note('.$note['id'].');');

    $widget_note->addToolbarButtons($dropdown);


    if ($note['crypte'])
    {
        $texte = '<a href="javascript:void();" onclick="unlock(\'note_'.$note['id'].'\')">Cette note est protégée, cliquez ici pour l\'afficher</a>';
    }
    else
    {
        $texte = strip_tags($note['texte']);
        $texte = preg_replace('#http://[a-z0-9._/-?-{}&%\#]+#i', '<a href="$0" target="_blank">$0</a>', $texte);
        $texte = preg_replace('#https://[a-z0-9._/-?-{}&%\#]+#i', '<a href="$0" target="_blank">$0</a>', $texte);
        $texte = nl2br($texte);
    }

    $widget_note->setContent($texte, true, 'note_'.$note['id']);

    $content_pane .= $widget_note; //.$modal_edit_note->getModalHtml();
}


$tab2->addPane('Notes', $content_pane, 'notes');


/*
 * Pack maintenance
 */



if(AccesActivableModul('pack_maintenance') AND right('pack-maintenance', 1))
{


    $pack_maintenance = new DatabaseWorker('maintenance');
    $pack_maintenance->setWidget('Pack Maintenance - Solde : '.$solde);
    $pack_maintenance->displayedFields(Array('date_mouvement', 'commentaires', 'mouvements'));
    $pack_maintenance->labelsDisplayedFields(Array('Date', 'Libellé', 'Nombre d\'heures'));
    $pack_maintenance->setOrderBy('date_mouvement', 'DESC');
    $pack_maintenance->addDateFields('date_mouvement');
    $pack_maintenance->addWhere("id_client=".$id_customer);
    $pack_maintenance->activateAdd('c'.$id_customer, '', 'id_client', $id_customer);
    $pack_maintenance->activeDelete('c'.$id_customer);
    $pack_maintenance->activeModify('c'.$id_customer);

    $tab2->addPane('Pack maintenance', $pack_maintenance);
}

/*
 * MDP
 */


if(right('customers', 3))
{
    $widget_pass = new WidgetBox('Les mots de passe');

    $modal_add_pass = new Modal('Ajouter un mot de pass', 'modal_add_pass');
    $modal_add_pass->openButton('<i class="fa fa-plus"></i>', 'btn-primary');
    $modal_add_pass->setSubmitButton('add_pass_form', 'Ajouter le mot de passe');

    $form_add_pass = new FormLayout('Saisie');
    $form_add_pass->setFormControls('add_pass_form');

    $service = new Text('pass_service');
    $form_add_pass->addLine('Service', $service);

    $identifiant = new Text('pass_identifiant');
    $form_add_pass->addLine('identifiant', $identifiant);

    $mdp = new Text('pass_pass');
    $form_add_pass->addLine('Mot de passe', $mdp);

    $modal_add_pass->setContent($form_add_pass);



    $widget_pass->addToolbarButtons($modal_add_pass->getOpenHtml());


    $tables_mdp = new HtmlTable('table table-bordered table-hover table-responsive table-datatable');
    $tables_mdp->addTSection('tbody');
    $tables_mdp->addRow();
    $tables_mdp->addCell('Services');
    $tables_mdp->addCell('Identifiants');
    $tables_mdp->addCell('Mot de passe');
    $tables_mdp->addCell('Actions');
    $tables_mdp->addTSection('tbody');

    foreach($tab_pass AS $mdp)
    {
        $tables_mdp->addRow();
        $tables_mdp->addCell($mdp['service']);
        $tables_mdp->addCell('<div id="identifiant_'.$mdp['id'].'"><a href="javascript:void();" onclick="unlock(\'identifiant_'.$mdp['id'].'\')"><i class="fa fa-lock"></i></a> *****</div>');
        $tables_mdp->addCell('<div id="mdp_'.$mdp['id'].'"><a href="javascript:void();" onclick="unlock(\'mdp_'.$mdp['id'].'\')"><i class="fa fa-lock"></i></a> *****</div>');

        $suppr = new Button('Supprimer', 'Javascript:void();');
        $suppr->setClasse('btn-danger');
        $suppr->setFullWidth();
        $suppr->onClick('delete_service(\''.$mdp['id'].'\');');
        $tables_mdp->addCell($suppr);
    }
    $widget_pass->setContent($tables_mdp, false);



    $tab2->addPane('Gestionnaire de mots de passe', $widget_pass.$modal_add_pass->getModalHtml());
}


/*
 * SMS
 */


$table_sms = new HtmlTable();
$table_sms->addTSection('table table-bordered table-hover table-responsive table-datatable');
$table_sms->addRow();
$table_sms->addCell('Intervention', '', 'thead');
$table_sms->addCell('Numéro', '', 'thead');
$table_sms->addCell('Message', '', 'thead');
$table_sms->addCell('Envoyé par', '', 'thead');
$table_sms->addCell('Date d\'envoie', '', 'thead');
$table_sms->addTSection('tbody');
foreach($tab_sms AS $sms)
{
    $table_sms->addRow();
    $table_sms->addCell($sms['id_inter']);
    $table_sms->addCell($sms['numero']);
    $table_sms->addCell($sms['message']);

    if($sms['staff']) {
        $staff = new GetInfosStaff($sms['staff']);
        $table_sms->addCell('<a href="' . $staff->GetProfileLink() . '">' . $staff->GetPrenom() . '</a>');
    }
    else
        $table_sms->addCell('Managy');

    $table_sms->addCell(parse_date($sms['date']));
}

$tab2->addPane('SMS envoyés', $table_sms);


/*
 * Fichiers
 */
if(AccesActivableModul('files'))
{
    $elfinder = new elFinderPerso();
    $elfinder->addPath('c/' . $id_customer, 'Fichiers du client');

    foreach ($tab_inters AS $v)
        $elfinder->addPath('i/' . $v['id'], 'Intervention ' . $v['id']);

    $tab2->addPane('Fichiers', $elfinder, 'files');
}


/*
 * LOGS
 */
if(right('logs', 1))
{
    $logs = new Feeds();

    foreach ($tab_logs AS $log)
    {
        $staff = new GetInfosStaff($log['id_staff']);
        $logs->addLine($staff->GetPrenom().' '.$staff->GetNom().' '.$log['texte'], parse_date($log['time']), $staff->GetProfileLink());
    }



    $tab2->addPane('Suivi employés', $logs);
}

//$tab2->addPane('Agenda', 'En cours de développement');




$activity = new WidgetBox('Activités');
$activity->setContent($tab2);

$tab->addPane('Vue d\'ensemble', $coor.$widget_graphique.$widget_google_map.$activity);



if(right('customers', 2))
{
    $form_edit_customer = new FormLayout('Modifier le client');
    $form_edit_customer->setFormControls('form_edit_customer');
    $form_edit_customer->setValueButton('Modifier');


    if($row->pro_part == 1)
    {
        $convertir = 'particulier';
        $settings_name = new Text('settings-name');
        $settings_name->setValue($row->nom);
        $form_edit_customer->addLine(CUSTOMERS_SOCIETY_NAME, $settings_name);
    }
    else
    {
        $convertir = 'professionel';

        $titre_m = new Radio('settings-titre', CUSTOMERS_MISTER);
        if(CUSTOMERS_MISTER == $row->titre)
            $titre_m->checked();
        $titre_miss = new Radio('settings-titre', CUSTOMERS_MISS);
        if(CUSTOMERS_MISS == $row->titre)
            $titre_miss->checked();
        $titre_misses = new Radio('settings-titre', CUSTOMERS_MISSES);
        if(CUSTOMERS_MISSES == $row->titre)
            $titre_misses->checked();
        $form_edit_customer->addLine('Titre', $titre_m.' '.CUSTOMERS_MISTER.', '.$titre_miss.' '.CUSTOMERS_MISS.', '.$titre_misses.' '.CUSTOMERS_MISSES);

        $nom = new Text('settings-name');
        $nom->setValue($row->nom);
        $form_edit_customer->addLine(CUSTOMERS_FNAME, $nom);

        $prenom = new Text('settings-lastname');
        $prenom->setValue($row->prenom);
        $form_edit_customer->addLine(CUSTOMERS_LNAME, $prenom);
    }

    $mail = new Email('settings-mail');
    $mail->setValue($row->mail);
    $form_edit_customer->addLine(CUSTOMERS_MAIL, $mail);

    $adresse = new Text('settings-adresse');
    $adresse->setValue($row->adresse);
    $form_edit_customer->addLine(CUSTOMERS_ADRESS, $adresse);

    $adresse_suite = new Text('settings-adresse-suite');
    $adresse_suite->setValue($row->adresse_suite);
    $form_edit_customer->addLine(CUSTOMERS_ADRESS_SUITE, $adresse_suite);

    $cp = new Hidden('settings-cp', 'customer_cp');
    $cp->setValue($row->cp);
    $cpac = new Text('auto_v', 'autocomplete-cp');
    $cpac->setValue($row->cp.' '.$row->ville);
    $city = new Hidden('settings-ville', 'customer_city');
    $city->setValue($row->ville);
    $form_edit_customer->addLine(CUSTOMERS_CP.' '.CUSTOMERS_CITY, $cpac.$city.$cp);

    $portable = new Text('settings-portable');
    $portable->dataMask('+33 (0)9 99 99 99 99');
    $portable->setValue($row->portable);
    $form_edit_customer->addLine(CUSTOMERS_GSM, $portable);

    $fixe = new Text('settings-fixe');
    $fixe->dataMask('+33 (0)9 99 99 99 99');
    $fixe->setValue($row->fixe);
    $form_edit_customer->addLine(CUSTOMERS_PHONE, $fixe);

    $convertion = new Label('info', 'Convertir le client en '.$convertir, '?action=convertir');
    $form_edit_customer->addLine('', $convertion);

    $widget_archiver = new WidgetBox('Archiver ce client');
    $archiver = new Switches('archiver');
    $archiver->onChange("toggle_archiver();");
    if($archive)
        $archiver->checked();
    $col1 = new Col(3);
    $col1->setContent($archiver);
    $col2 = new Col(9);
    $col2->setContent(' Archiver ce client (vous ne pourrez plus créer d\'intervention ou de rendez-vous pour ce client, et il n\'apparaitra plus dans le resultat de la recherche.');

    $row = new Row($col1.$col2);

    $widget_archiver->setContent($row);


    $tab->addPane('Modifier le client', $form_edit_customer.$widget_archiver);
}

if(!$archive)
    $display = 'style="display: none;"';

$archive = new Danger('Ce client est archivé. Vous ne pouvez pas créer une intervention ou un rendez-vous pour lui', false);
$archive->setStyle($display);
$archive->setId('message_archive');


$row = new Row($archive);

echo $row;

$row = new Row($tab);
echo $row;






$modal_unlock = new Modal('Déverouiller un élément', 'modal_unlock');
$modal_unlock->setOnclickButton('Déverouiller', 'unlockpass();');
$modal_unlock->openButton('ouvrir la modal');


$saisie = new FormLayout('Saisie');
$saisie->setFormControls('form_unlock');

$password = new Password('unlock_pass', 'unlock_pass');
$hidden = new Hidden('unlock_id', 'unlock_id');

$saisie->addLine('Votre mot de passe : ', $password.$hidden);

$modal_unlock->setContent($saisie);

echo $modal_unlock->getModalHtml();












//$tab2->addPane('Mails envoyés', 'En cours de développement');






$md = new Col(12, 'sm');
$md->setContent(' ');

//$tab->addPane('Vue d\'ensemble', $coor.$widget_graphique.$widget_google_map.$md.$tab2);











?>