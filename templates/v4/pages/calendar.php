<?php session_start();

    



$mon_calendrier = new Calendar('Calendrier', 'full_cal');



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

    $mon_calendrier->addEvent('s'.$rdv['id'], addslashes($rdv['title']).' ('.$rdv['prefix'].$rdv['id'].')\n'.addslashes($rdv['adresse']), $rdv['annee'], $rdv['mois'], $rdv['jour'], $rdv['heure'], $rdv['minute'], $rdv['anneef'], $rdv['moisf'], $rdv['jourf'], $rdv['heuref'], $rdv['minutef'], $description, $rdv['cal_color']);

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

$row = new Row($attente_rdv_widget.$mon_calendrier);
echo $row; //$mon_calendrier;


?>