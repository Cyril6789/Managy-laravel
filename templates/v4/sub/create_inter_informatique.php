<?php session_start();



?>

<script  type='text/javascript'>

    function implementer(value)    

    {

        var tab = value.split('#');

        document.getElementById('id_materiel').value = tab[0];

        $('#id_materiel').trigger('change.select2');

        document.getElementById('id_os').value = tab[1];

        $('#id_os').trigger('change.select2');

        document.getElementById('id_antivirus').value = tab[2];

        $('#id_antivirus').trigger('change.select2');





        if(tab[3] == 1)

            $('#urgente').attr('checked', true);

        else

            $('#urgente').attr('checked', false);



        document.getElementById('mdp').value = tab[4];

        document.getElementById('matos').value = tab[5];

    }



    

    function set_end()

    {

        var x = $("#date_heure").val();

    var x = document.getElementById('date_heure').value;

    var tab_x = x.split(' ');

    

    var date = tab_x[0].split('/');

    

    var jour = date[0];

    var mois = date[1];

    var annee = date[2];

    

    var heures = tab_x[1].split(':');

    

    var heure = heures[0];

    var min = heures[1];

    

    var timestamp = java_mktime(heure, min, mois, jour, annee);

    

    var timestamp_fin = parseInt(timestamp) + parseInt(document.getElementById('ecart').value);

    

    var fin = new Date(timestamp_fin * 1000);

    jour = fin.getDate();

    if(jour < 10)

        jour = '0'+jour;

    

    mois = fin.getMonth() + 1;

    

    if(mois < 10)

        mois = '0'+mois;

    

    annee = fin.getFullYear();

    

    heure = fin.getHours();

    if(heure < 10)

        heure = '0'+heure;

    

    min = fin.getMinutes();

    if(min < 10)

        min = '0'+min;

    

    var final = jour+'/'+mois+'/'+annee+' '+heure+':'+min;

    //alert(final);

    

    document.getElementById('date_heure_fin').value = final;

}

    

function set_ecart()

{



    var x = document.getElementById('date_heure').value;

    var tab_x = x.split(' ');

    var date = tab_x[0].split('/');

    

    var jour = date[0];

    var mois = date[1];

    var annee = date[2];

    var heures = tab_x[1].split(':');

    

    var heure = heures[0];

    var min = heures[1];

    var timestamp_debut = java_mktime(heure, min, mois, jour, annee);

    var x = document.getElementById('date_heure_fin').value;

    var tab_x = x.split(' ');

    var date = tab_x[0].split('/');

    

    var jour = date[0];

    var mois = date[1];

    var annee = date[2];

    var heures = tab_x[1].split(':');

    

    var heure = heures[0];

    var min = heures[1];

    var timestamp_fin = java_mktime(heure, min, mois, jour, annee);

    

    var ecart = parseInt(timestamp_fin) - parseInt(timestamp_debut);



    if(ecart > 0)

        document.getElementById('ecart').value = ecart;



}



    function contact_ref(id)

    {

        if(id == '-1')

            $(location).attr('href',"customers-addcontactoncreateinter-<?php echo $id_client;?>");

        $('#contact_ref_atelier').val(id);

        $('#contact_ref_site').val(id);

    }

</script>





<?php

$client = new GetInfosCustomer($id_client);





$ask_mail = new DataObject('comptes_principaux');

$ask_mail->find($_SESSION['compte_principal'], 'id', false);



$ask = $ask_mail->ask_mail;


$mail_c = $client->GetMail();
//die();

 
if($ask AND right('customers', 2)  AND empty($mail_c))

{

    $ask_mail = new modalAjax('customers', 'ask_mail');

    $ask_mail->settings(Array('id_client' => $id_client));

    //$ask_mail->setDebug();

    echo $ask_mail->getModalHtml();

    echo $ask_mail->openNow();

}


                     






$ref = $db->SQLFix($_GET['ref']);



$widget_client = new WidgetBox('Client', 12);

$widget_client->setContent('<a target="_blank" href="'.$client->GetProfileLink().'">'.$client->GetTitre().' '.$client->GetNom().' '.$client->GetPrenom().'</a><br />'.$client->GetMail().'<br />'.$client->getPort().'<br />'.$client->getFixe().'<br />'.$client->GetAdresse().'<br />'.$client->getCp().' '.$client->getVille());



$widget_contacts = '';



if($client->getPropart() == '1') {

    $widget_contacts = new FormLayout('Contact référent', 12);

    $widget_contacts->setFormControls('');

    $contacts = new Select('contacts');

    if ($ref)

        $contacts->setSelected($ref);

    $contacts->onChange('contact_ref(this.value)');

    $contacts->addOption('0', 'Aucun');

    foreach ($tab_contacts As $con)

        $contacts->addOption($con['id'], $con['nom'] . ' ' . $con['prenom']);

    $contacts->addOption('-1', 'Ajouter un contact');

    $widget_contacts->addLine('Contact', $contacts);



}





$widget_google_map = new WidgetBox('Carte', 4);





require('./classes/GoogleMapAPI.class.php');

$adress = $client->getFullAdress();

$gmap = new GoogleMapAPI();

$gmap->setDirectionDivId('route');

$gmap->setDivId('map');

//$gmap->setDirectionDivId('route');

$gmap->setCenter($adress);

$gmap->setEnableWindowZoom(true);

//$gmap->setEnableAutomaticCenterZoom(true);

$gmap->setDisplayDirectionFields(true);

$gmap->setClusterer(true);

$gmap->setSize('100%','200px');

//$gmap->setZoom(12);

$gmap->setLang('fr');

$gmap->setDefaultHideMarker(false);



$gmap->addDirection(ADRESS_SOCIETY, $adress);





//$gmap->addMarkerByAddress($adress, $row->nom.' '.$row->prenom, $row->nom.' '.$row->prenom);

$gmap->generate();



$widget_google_map->setContent($gmap->getGoogleMap());

$iti = new WidgetBox('Itineraire', 4);

$iti->setContent('<div id="route" style=" height: 200px;  overflow-y: auto;"></div>');



$col = new Col(4);

$col->setContent($widget_client.$widget_contacts);



$row = new Row($col.$widget_google_map.$iti);



echo $row;

$tab = new Tab();

$tab->fullWidth();





$atelier = new FormLayout('Ancien matériel déposé');

$atelier->setFormControls('form_ancien');



$liste_ancien = new Select('ancien', 'ancien');

$liste_ancien->withSearch();

$liste_ancien->onChange("implementer(value);");

$liste_ancien->addOption('#####', '--');

foreach($tab_anciens AS $ancien)

    $liste_ancien->addOption($ancien['id_materiel'].'#'.$ancien['id_se'].'#'.$ancien['id_antivirus'].'#'.$ancien['urgente'].'#'.$ancien['mdp'].'#'.$ancien['matos'], 'Intervention N°'.$ancien['id_inter'].' ('.$ancien['time_ouverture'].') : '.$ancien['matos']);



$atelier->addLine('Selectionnez : ', $liste_ancien);



$inter = new FormLayout('Spécificités de l\'intervention');

$inter->setFormControls('form_spe', './intervention-create');

$inter->setValueButton('Créer l\'intervention atelier', 'send');



$id_client = new Hidden('id_client', 'id_client');

$id_client->setValue($_GET['id_customer']);



$materiels = new Select('id_materiel', 'id_materiel');

$materiels->withSearch();

foreach($tab_materiels AS $materiel)

    $materiels->addOption($materiel['id'], stripslashes($materiel['name']));

$contact_ref = new Hidden('contact_ref', 'contact_ref_atelier');

$contact_ref->setValue($ref);

$inter->addLine('Type de matériel', $id_client.$contact_ref.$materiels);



$os = new Select('id_os', 'id_os');

$os->withSearch();

foreach($tab_ses AS $se)

    $os->addOption($se['id'], $se['name']);

$inter->addLine('Système d\'exploitation', $os);



$av = new Select('id_antivirus', 'id_antivirus');

$av->withSearch();

foreach($tab_antivirus AS $antivirus)

    $av->addOption($antivirus['id'], stripslashes($antivirus['name']));$inter->addLine('Antivirus', $av);



$urgente = new CheckBox('urgente', 'urgente');

$inter->addLine('Urgente', $urgente);



$mdp = new Text('mdp', 'mdp');

$inter->addLine('Mot de passe - Codes<br /><a href="javascript:void();" onclick="$(\'#div_pattern\').toggle(\'slow\');">Schéma de dévérouillage</a> ', $mdp);





$pattern = new Pattern('pattern_create', 'pattern_value_create');

$inter->addLine('', $pattern, false,  'div_pattern', 'display: none;');





$garantie = new CheckBox('garantie', 'garantie');

$inter->addLine('Prise en charge sous garantie', $garantie);



$panne = new Textarea('panne', 'panne');

$panne->elastic();

$panne->setRows(5);

$inter->addLine('Panne constatée,<br />Travaux demandés', $panne);



$matos = new Textarea('matos', 'matos');

$matos->elastic();

$matos->setRows(5);

$inter->addLine('Matériel déposé', $matos);



$tarif = new Text('tarif');

$inter->addLine('Tarif estimé', $tarif);



$techncien = new Select('staff');

$techncien->addOption('0', 'Aucun pour le moment');

$techncien->withSearch();

foreach ($tab_staffs AS $staff)

{

    $techncien->addOption($staff['id'], $staff['prenom'].' '.$staff['nom']);

}



$inter->addLine('Technicien affecté', $techncien);





$statut = new Select('statut');

$statut->addOption('0', 'En cours');

foreach ($tab_statuts AS $stat)

{

    $texte = '';

    if($stat['busy'])

        $texte = '(Intervention occupée)';

    $statut->addOption($stat['id'], $stat['nom'].' '.$texte);

}

$inter->addLine('Statut de l\'intervention', $statut);



if($nb_anciennce_inter)

    $tab->addPane('Intervention atelier', $atelier.$inter);

else

    $tab->addPane('Intervention atelier', $inter);





$site = new FormLayout('Spécificités de l\'intervention sur site');

$site->setFormControls('form_cal', './intervention-create');

$site->setValueButton('Créer l\'intervention sur site', 'rdv');



$id_clientc = new Hidden('id_clientc');

$id_clientc->setValue($_GET['id_customer']);



$pannec = new Textarea('pannec', 'pannec');

$pannec->elastic();

$pannec->setRows(5);

$contact_ref = new Hidden('contact_ref', 'contact_ref_site');

$contact_ref->setValue($ref);

$site->addLine('Panne constatée,<br />Travaux demandés', $id_clientc.$contact_ref.$pannec);



$message_interne = new Textarea('message_interne', 'message_interne');

$message_interne->elastic();

$message_interne->setRows(5);

$site->addLine('Message interne', $message_interne);



$ecart = new Hidden('ecart', 'ecart');

$ecart->setValue(3600);



$check = new CheckBox('rdv_late');

$check->onChange("$('#date_debut_rdv').toggle('slow');$('#date_fin_rdv').toggle('slow');$('#priority').toggle('slow');");

$site->addLine('Définir le rendez-vous plus tard', $check);



$debut = new Text('date_heure', 'date_heure');

$debut->dataMask('99/99/9999 99:99');

$debut->dateTimePicker('set_end();');

$debut->setValue(date('d/m/Y').' 10:00');

$debut->onKeyUp("set_end();");

$site->addLine('Début', $ecart.$debut, false, 'date_debut_rdv');



$fin = new Text('date_heure_fin', 'date_heure_fin');

$fin->dataMask('99/99/9999 99:99');

$fin->dateTimePicker('set_ecart();');

$fin->setValue(date('d/m/Y').' 11:00');

$fin->onKeyUp("set_ecart();");

$site->addLine('Fin', $fin, false, 'date_fin_rdv');



$priority = new Select('priorite');

$priority->setSelected(0);

$priority->addOption('-1', 'Basse');

$priority->addOption('0', 'Normale');

$priority->addOption('1', 'Urgente');

$site->addLine('Priorité', $priority, false, 'priority', "display: none;");





$techncien = new Select('staff');

$techncien->addOption('0', 'Aucun pour le moment');

$techncien->withSearch();

foreach ($tab_staffs AS $staff)

{

    $techncien->addOption($staff['id'], $staff['prenom'].' '.$staff['nom']);

}



$site->addLine('Technicien affecté', $techncien);









$site->setValueButton('Créer le rendez-vous', 'rdv');



$tab->addPane('Intervention sur site', $site);

echo $tab;





?>

