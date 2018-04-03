<?php
include('./classes/mysql.class.php');
$db = new MySQL();
if(isset($_GET['pass']))	
	$pass = $db->SQLfix($_GET['pass']);
else	
	die();


$db->Query('SELECT id FROM comptes_principaux WHERE cal="'.$pass.'" ');
$row = $db->Row();
$compte_principal = $row->id;
$nb_jours_decal = 100;

$jours_decal = 60 * 60 * 24 * $nb_jours_decal; //jours d?cal?s en secondes


$time_debut_synchro = time() - $jours_decal; //D?but du calendri? synchronis? (H-x)
$ical = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//FR
BEGIN:VTIMEZONE
TZID:Europe/Paris
X-LIC-LOCATION:Europe/Paris
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=10
END:STANDARD
END:VTIMEZONE';



//RDV NORMAUX
$requete = "SELECT rdv.id As id, clients.id As id_c, date, date_fin, nom, prenom, adresse, cp, ville, lieu_client_atelier, fixe, portable,
				(SELECT COUNT(*) FROM rdv WHERE id_client=clients.id) AS nb_rdv,
				(SELECT COUNT(*) FROM interventions WHERE id_client=clients.id) AS nb_inter,
				(SELECT SUM(mouvements)	FROM maintenance WHERE id_client = clients.id) AS solde
			FROM rdv
			INNER JOIN clients
			ON (rdv.id_client = clients.id)
                        AND (clients.compte_principal = '".$compte_principal."')
			WHERE date > '".$time_debut_synchro."' AND rdv.compte_principal='".$compte_principal."' ORDER BY date ASC";
			
$db->Query($requete);
echo $db->Error();
        





while($d = $db->Row())
{
	if($d->solde)	
	{
		$heures = floor(abs($d->solde));
		$minutes = (abs($d->solde) - $heures) * 60;
		if($d->solde < 0)
			$moins = '-';
		else
			$moins = '';
		
		if(round($minutes) < 10)
			$solde = $moins.$heures.'H0'.round($minutes);
		else
			$solde = $moins.$heures.'H'.round($minutes);
	}
	else
		$solde = '0 heure';
	
	
	
	$date_d = $d->date;
        if($d->date < $d->date_fin)
            $date_f = $d->date_fin;
        else
            $date_f = $date_d + 3600;
	$d->nb_rdv--;
	$ical .= '
BEGIN:VEVENT
UID:'.$d->id.'@gi67.fr
DTSTART;TZID=Europe/Paris:'.date('Ymd', $date_d).'T'.date('His', $date_d).'
DTEND;TZID=Europe/Paris:'.date('Ymd', $date_f).'T'.date('His', $date_f).'
SUMMARY:'.strip_tags(utf8_decode(utf8_encode($d->nom))).' '.strip_tags(utf8_decode(utf8_encode($d->prenom))).' (Sync managy)
DESCRIPTION:Fixe : '.$d->fixe.'\nPortable : '.$d->portable.'\nItin?raire : http://www.managy.fr/gm'.$d->id_c.'\nRendez-vous précédents : '.$d->nb_rdv.'\nNombre d\'interventions : '.$d->nb_inter.'\nDétails : https://www.managy.fr/c'.$d->id_c.'\nSolde maintenance : '.$solde.'
';


if($d->lieu_client_atelier == '2')
	$ical .= '
LOCATION:Boutique';
else
	$ical .= '
LOCATION:'.strip_tags(str_replace(',', '', str_replace("\r\n", '\n', utf8_decode(utf8_encode($d->adresse))))).' '.$d->cp.' '.strip_tags(utf8_decode(utf8_encode($d->ville)));

$alarm = $d->rappel / 60;
$ical .= '
BEGIN:VALARM
ACTION:DISPLAY
DESCRIPTION:'.strip_tags(utf8_decode(utf8_encode($d->nom))).' '.strip_tags(utf8_decode(utf8_encode($d->prenom))).'
TRIGGER:-P0DT0H'.$alarm.'M0S
END:VALARM';

$ical.='
END:VEVENT
';
}





//INTERVENTIONS SUR SITE
$requete = "SELECT i.id_inter As id_inter, i.time_ouverture, i.prefix, clients.id As id_c, rdv_debut, rdv_fin, nom, prenom, clients.adresse, clients.cp, clients.ville, clients.fixe, clients.portable, i.panne,
				(SELECT COUNT(*) FROM rdv WHERE id_client=clients.id) AS nb_rdv,
				(SELECT COUNT(*) FROM interventions WHERE id_client=clients.id) AS nb_inter,
				(SELECT SUM(mouvements)	FROM maintenance WHERE id_client = clients.id) AS solde
			FROM interventions AS i
			INNER JOIN clients
			ON (i.id_client = clients.id)
                        AND (clients.compte_principal = '".$compte_principal."')
			WHERE rdv_debut > '".$time_debut_synchro."' AND i.compte_principal='".$compte_principal."' AND i.rdv_annule = '0' ORDER BY rdv_debut ASC";
			
$db->Query($requete);
echo $db->Error();
        

while($d = $db->Row())
{
	if($d->solde)	
	{
		$heures = floor(abs($d->solde));
		$minutes = (abs($d->solde) - $heures) * 60;
		if($d->solde < 0)
			$moins = '-';
		else
			$moins = '';
		
		if(round($minutes) < 10)
			$solde = $moins.$heures.'H0'.round($minutes);
		else
			$solde = $moins.$heures.'H'.round($minutes);
	}
	else
		$solde = '0 heure';
	
	
	
	$date_d = $d->rdv_debut;
        if($d->date < $d->rdv_fin)
            $date_f = $d->rdv_fin;
        else
            $date_f = $date_d + 3600;
    $ical .= '
BEGIN:VEVENT
DTSTAMP:'.date('Ymd', $d->time_ouverture).'T'.date('His', $d->time_ouverture).'
UID:'.$d->id_inter.'@gi67.fr
DTSTART;TZID=Europe/Paris:'.date('Ymd', $date_d).'T'.date('His', $date_d).'
DTEND;TZID=Europe/Paris:'.date('Ymd', $date_f).'T'.date('His', $date_f).'
SUMMARY:'.strip_tags(utf8_decode(utf8_encode($d->nom))).' '.strip_tags(utf8_decode(utf8_encode($d->prenom))).' (Inter : '.$d->prefix.$d->id_inter.')
DESCRIPTION:Panne : '.str_replace("\n", '\n', strip_tags(utf8_decode(utf8_encode($d->panne)))).'\nFixe : '.$d->fixe.'\nPortable : '.$d->portable.'\nItinéraire : https://www.managy.fr/gm'.$d->id_c.'\nRendez-vous précédents : '.$d->nb_rdv.'\nNombre d\'interventions : '.$d->nb_inter.'\nDétails : http://www.managy.fr/i'.$d->id_inter.'\nSolde maintenance : '.$solde.'
LOCATION:'.str_replace("\r\n", ' ', strip_tags(utf8_decode(utf8_encode(str_replace(',','' , $d->adresse))))).' '.$d->cp.' '.strip_tags(utf8_decode(utf8_encode($d->ville)));

    $alarm = 15;
$ical .= '
BEGIN:VALARM
ACTION:DISPLAY
DESCRIPTION:'.strip_tags($d->nom).' '.strip_tags($d->prenom).'
TRIGGER:-P0DT0H'.$alarm.'M0S
END:VALARM';

$ical.='
END:VEVENT
';
}



//INTERVENTIONS SUR SITE PEC par un technicien
$requete = "SELECT i.id_inter As id_inter, i.prefix, i.time_ouverture, i.panne, clients.id As id_c, rdv_debut, rdv_fin, clients.nom, clients.prenom, clients.adresse, clients.cp, clients.ville, clients.fixe, clients.portable, staffs.id, staffs.compte_principal,
				(SELECT COUNT(*) FROM rdv WHERE id_client=clients.id) AS nb_rdv,
				(SELECT COUNT(*) FROM interventions WHERE id_client=clients.id) AS nb_inter,
				(SELECT SUM(mouvements)	FROM maintenance WHERE id_client = clients.id) AS solde
			FROM interventions AS i
			INNER JOIN prise_en_charge AS pec
			ON (pec.id_intervention = i.id_inter)
			INNER JOIN staffs
			ON(staffs.id = pec.id_staff)
			INNER JOIN clients
			ON (i.id_client = clients.id)
			WHERE rdv_debut > '".$time_debut_synchro."' 
			AND i.rdv_annule = '0' 
			AND staffs.cal = '".$pass."'
			AND i.compte_principal = staffs.compte_principal
			ORDER BY rdv_debut ASC";

$db->Query($requete);
echo $db->Error();


while($d = $db->Row())
{
	if($d->solde)
	{
		$heures = floor(abs($d->solde));
		$minutes = (abs($d->solde) - $heures) * 60;
		if($d->solde < 0)
			$moins = '-';
		else
			$moins = '';

		if(round($minutes) < 10)
			$solde = $moins.$heures.'H0'.round($minutes);
		else
			$solde = $moins.$heures.'H'.round($minutes);
	}
	else
		$solde = '0 heure';



	$date_d = $d->rdv_debut;
	if($d->date < $d->rdv_fin)
		$date_f = $d->rdv_fin;
	else
		$date_f = $date_d + 3600;
	$ical .= '
BEGIN:VEVENT
DTSTAMP:'.date('Ymd', $d->time_ouverture).'T'.date('His', $d->time_ouverture).'
UID:'.$d->id_inter.'@gi67.fr
DTSTART;TZID=Europe/Paris:'.date('Ymd', $date_d).'T'.date('His', $date_d).'
DTEND;TZID=Europe/Paris:'.date('Ymd', $date_f).'T'.date('His', $date_f).'
SUMMARY:'.strip_tags(utf8_decode(utf8_encode($d->nom))).' '.strip_tags(utf8_decode(utf8_encode($d->prenom))).' (Inter : '.$d->prefix.$d->id_inter.')
DESCRIPTION:Panne : '.str_replace("\n", '\n', strip_tags(utf8_decode(utf8_encode($d->panne)))).'\nFixe : '.$d->fixe.'\nPortable : '.$d->portable.'\nItinéraire : https://www.managy.fr/gm'.$d->id_c.'\nRendez-vous précédents : '.$d->nb_rdv.'\nNombre d\'interventions : '.$d->nb_inter.'\nDétails : http://www.managy.fr/i'.$d->id_inter.'\nSolde maintenance : '.$solde.'
LOCATION:'.str_replace("\r\n", '', strip_tags(utf8_decode(utf8_encode(str_replace(',','' , $d->adresse))))).' '.$d->cp.' '.strip_tags(utf8_decode(utf8_encode($d->ville)));

	$alarm = 15;
	$ical .= '
BEGIN:VALARM
ACTION:DISPLAY
DESCRIPTION:'.strip_tags(utf8_decode(utf8_encode($d->nom))).' '.strip_tags(utf8_decode(utf8_encode($d->prenom))).'
TRIGGER:-P0DT0H'.$alarm.'M0S
END:VALARM';

	$ical.='
END:VEVENT';
}





$ical .= '
END:VCALENDAR';


//header('Content-Type: text/calendar; charset=CP1250');
//header('Content-Disposition: attachment; filename="calendar.ics"; ');
echo  $ical;
?>