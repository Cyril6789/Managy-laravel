<?php session_start();

/**

 * Created by PhpStorm.

 * User: Cyril

 * Date: 23/08/2016

 * Time: 16:03

 */







$widget = new FormLayout('Informations du compte');

$widget->setFormControls('form_societe');

$widget->setValueButton('Modifier les informations du compte');



$nom_societe = new Text('nom_societe');

$nom_societe->setValue($infos_societe['societe']);

$widget->addLine('Nom de la société :', $nom_societe);



$adresse = new Text('adresse');

$adresse->setValue($infos_societe['adresse']);

$widget->addLine('Adresse postale : ', $adresse);



$mail_societe = new Text('mail_societe');

$mail_societe->setValue($infos_societe['mail']);

$widget->addLine('Mail de votre société :', $mail_societe);



$tel = new Text('tel');

//$tel->dataMask('99 99 99 99 99');

$tel->setValue($infos_societe['tel']);

$widget->addLine('Téléphone société :', $tel);



$web = new Text('web');

$web->setValue($infos_societe['web']);

$widget->addLine('Site web :', $web);



$siret = new Text('siret');

$siret->setValue($infos_societe['siret']);

$widget->addLine('N° SIRET :', $siret);



$ape = new Text('ape');

$ape->setValue($infos_societe['ape']);

$widget->addLine('Code APE :', $ape);



$expediteur_sms = new Text('expediteur_sms');

$expediteur_sms->setValue($infos_societe['expediteur_sms']);

$widget->addLine('Expediteur SMS', $expediteur_sms);



$signature_sms = new Text('signature_sms');

$signature_sms->setValue($infos_societe['signature_sms']);

$widget->addLine('Signature SMS ', $signature_sms);



$prefix = new Select('prefix');

if($infos_societe['prefix'] != '')

    $prefix->setSelected($infos_societe['prefix']);

$prefix->addOption('0', 'Aucun');

$prefix->addOption('aaaa-', 'AAAA');

$prefix->addOption('mm-', 'MM');

$prefix->addOption('aaaa-mm-', 'AAAA-MM');

$prefix->addOption('mm-aaaa-', 'MM-AAAA');



$widget->addLine('Prefix aux numéros d\'interventions', $prefix);



$ask_mail = new CheckBox('ask_mail');

if($infos_societe['ask_mail'])

    $ask_mail->checked();

$widget->addLine('Demander le mail du client à la création d\'une intervention', $ask_mail);


$disable_recap_hebdo = new CheckBox('disable_recap_hebdo');
if($infos_societe['disable_recap_hebdo'])
    $disable_recap_hebdo->checked();
$widget->addLine('Désactiver les mails "Récap Hebdo"', $disable_recap_hebdo);



$pop1 = new PopOver('Comment faire sous iOs ?', 'Sur votre appareil iOs, cliquez sur Réglages -> Mail, Contacts, Calendrier -> Ajouter un compte -> Autre -> Ajouter un cal. avec abonnement. Collez-y cette adresse et activez la connexion sécurisée SSL');

$pop1->setLink(new Font('question-circle-o').' iOs');

$pop2 = new PopOver('Comment faire sous Android ?', 'Il vous faudra télécharger et configurer avec les liens ci dessous l\'application \'iCalSync2\' disponible sur le Play Store');

$pop2->setLink(new Font('question-circle-o').' Android');

$widget->addLine('Abonnement Calendrier (iOs + Android) :', 'https://www.managy.fr/ical.php?pass='.$infos_societe['cal'].'<br />'.$pop1.'<br />'.$pop2);



$row = new Row($widget);

echo $row;





$cal = new DatabaseWorker('staffs');

$cal->setWidget('Tableau des calendriers personnels (https://www.managy.fr/ical.php?pass=CODE)');

$cal->displayedFields(Array('prenom', 'nom', 'cal'));

$cal->labelsDisplayedFields(Array('Prénom', 'Nom', 'CODE (Utilisez ce code et remplacez-le dans le lien ci-dessus pour avoir le calendrier personnel du technicien)'));





$row = new Row($cal);

echo $row;





$logos = new FormLayout('Gestion des logos');

$logos->setFormControls('form_societe');

$logos->setValueButton('Enregistrer');

$logos->file();



$document = new File('docs');



if(is_file('./print/images/logos/'.$_SESSION['compte_principal'].'.png'))

    $img = '<img src="./print/images/logos/'.$_SESSION['compte_principal'].'.png?rand='.rand(1000,100000).'" width="150px" alt="logo"/>';

else

    $img = '';



$logos->addLine('Logo documents :', $document.$img);



$mail = new File('mails');



if(is_file('./images/logos_mails/'.$_SESSION['compte_principal'].'.png'))

    $img = '<img alt="Logo" src="./images/logos_mails/'.$_SESSION['compte_principal'].'.png?rand='.rand(1000,100000).'" border="0" width="150px" />';

else

    $img = '';

$logos->addLine('Logo mails :', $mail.$img);



$row = new Row($logos);

echo $row;











if(AccesActivableModul('intervention_informatique')) {

    $texte_entree = new DatabaseWorker('comptes_principaux', false);

    $texte_entree->setWidget('Modalités sur les documents à signer');

    $texte_entree->noDatatable();

    $texte_entree->addWhere('id="' . $_SESSION['compte_principal'] . '"');

    $texte_entree->displayedFields(Array('entree', 'sortie', 'site'));

    $texte_entree->labelsDisplayedFields(Array('Modalités sur fiches entrées', 'Modalités sur fiches sorties', 'Modalités sur fiches interventions sur site'));

    $texte_entree->addIfValue('entree', '', $modalites['entree']);

    $texte_entree->addIfValue('sortie', '', $modalites['sortie']);

    $texte_entree->addIfValue('site', '', $modalites['site']);

    $texte_entree->activeModify('./admin');



    $row = new Row($texte_entree);

    echo $row;

}





if($_SESSION['gerant'])

{

    $widget_supprimer = new WidgetBox('Supprimer le compte');



    $modal_supprimer = new Modal('Supprimer le compte', 'suppr_modal');

    $modal_supprimer->setHeaderBackgroundColor('#DB0019');



    $modal_supprimer->setContent("Êtes-vous certain de vouloir supprimer entièrement votre compte Managy ? Cette action est définitive et irréversible ! Toutes vos données clients, interventions, techniciens, statistiques seront supprimées. Tous vos crédits SMS ou modules payants seront supprimés. Aucun remboursement pour la période non consommée ne pourra être réclamé. <br /><br />IL N'Y A PAS D'AUTRE CONFIRMATION APRES CELLE-CI.");







    $modal_supprimer->setOnclickButton('Supprimer mon compte', '$(location).attr(\'href\', \'./admin-delete_account\');');





    echo $modal_supprimer;



    $bouton = new Button('Supprimer le compte Managy', $modal_supprimer->getAhref());

    $bouton->setClasse('btn-danger');

    $bouton->setFullWidth();



    $widget_supprimer->setContent($bouton);



    $row = new Row($widget_supprimer);

    echo $row;

}



?>