<?php session_start();

$new_rdv_widget = new WidgetBox('Ajouter un Rendez-vous', 6);

$new_rdv_form = new FormLayout('Saisie');
$new_rdv_form->setFormControls('', './rdv-create');

$id_client = new Hidden('id_client');
$id_client->setValue($_GET['id_customer']);

$ecart = new Hidden('ecart');
$ecart->setValue(3600);

$send = new Hidden('send');
$send->setValue(1);

$date_heure = new Text('date_heure', 'date_heure');
$date_heure->setValue(date('d/m/Y').' 10:00');
$date_heure->dataMask('99/99/9999 99:99');
$date_heure->onKeyUp("set_end();");
$new_rdv_form->addLine('Date et heure début', $send.$id_client.$ecart.$date_heure, true);

$date_heure_fin = new Text('date_heure_fin', 'date_heure_fin');
$date_heure_fin->setValue(date('d/m/Y').' 11:00');
$date_heure_fin->dataMask('99/99/9999 99:99');
$date_heure_fin->onKeyUp("set_ecart();");
$new_rdv_form->addLine('Date et heure fin', $date_heure_fin, true);

$lieu_1 = new Radio('lieu', '1', 'lieu_1_add_rdv');
//$lieu_1->checked();
$lieu_2 = new Radio('lieu', '2', 'lieu_2_add_rdv');
$lieu_2->checked();

$new_rdv_form->addLine('Lieu', $lieu_1.' chez le client<br />'.$lieu_2.' à l\'atelier', false, 'lieux');

$new_rdv_form->setValueButton('Créer le rendez-vous');

$new_rdv_widget->setContent($new_rdv_form, false);
echo $new_rdv_widget;


$mon_calendrier = new Calendar('Calendrier', 'semi_call', 6);



//Interventions sur site
foreach ($tab_inter_site AS $rdv)
{
    $description = $rdv['jour'].'/';
    if($rdv['mois']+1 < 10)
    {
        $description .= '0';
        $description .= $rdv['mois']+1;
    }
    else
    {
        $description .= $rdv['mois']+1;

    }

    $description .= '/'.$rdv['annee'].' '.$rdv['heure'].':'.$rdv['minute'].'#'.$rdv['jourf'].'/';
    if($rdv['moisf']+1 < 10)
    {
        $description .= '0';
        $description .= $rdv['moisf']+1;
    }
    else
    {
        $description .= $rdv['moisf']+1;

    }

    $description .= '/'.$rdv['anneef'].' '.$rdv['heuref'].':'.$rdv['minutef'].'#'.$rdv['sms_veille'];

    $mon_calendrier->addEvent('s'.$rdv['id'], $rdv['title'].' ('.$rdv['id'].')\n'.addslashes($rdv['adresse']), $rdv['annee'], $rdv['mois'], $rdv['jour'], $rdv['heure'], $rdv['minute'], $rdv['anneef'], $rdv['moisf'], $rdv['jourf'], $rdv['heuref'], $rdv['minutef'], $description);

}

//Rendez-vous normaux
foreach ($tab_rdv AS $rdv)
{
    $description = $rdv['jour'].'/';
    if($rdv['mois']+1 < 10)
    {
        $description .= '0';
        $description .= $rdv['mois']+1;
    }
    else
    {
        $description .= $rdv['mois']+1;

    }

    $description .= '/'.$rdv['annee'].' '.$rdv['heure'].':'.$rdv['minute'].'#'.$rdv['jourf'].'/';
    if($rdv['moisf']+1 < 10)
    {
        $description .= '0';
        $description .= $rdv['moisf']+1;
    }
    else
    {
        $description .= $rdv['moisf']+1;

    }
    $description .= '/'.$rdv['anneef'].' '.$rdv['heuref'].':'.$rdv['minutef'].'#'.$rdv['sms_veille'].'#'.$rdv['sms_15'].'#'.$rdv['sms_satisfaction'].'#'.$rdv['lieu_client_atelier'];

    if($rdv['lieu_client_atelier'] == 1)
    {
        $adresse = addslashes($rdv['adresse']);
        $color = 'blue';
    }
    else
    {
        $adresse = 'Atelier';
        $color = 'green';
    }


        $mon_calendrier->addEvent($rdv['id'], $rdv['title'].'\n'.$adresse, $rdv['annee'], $rdv['mois'], $rdv['jour'], $rdv['heure'], $rdv['minute'], $rdv['anneef'], $rdv['moisf'], $rdv['jourf'], $rdv['heuref'], $rdv['miutef'], $description, $color);

}


echo $mon_calendrier;

?>

