<?php

/**

 * Created by PhpStorm.

 * User: Cyril

 * Date: 30/05/2017

 * Time: 09:02

 */



include('./../classes/mysql.class.php');

include('./../classes/smseur.class.php');

include('./../classes/Maileur.class.php');

include('./../functions/AccesActivableModul.func.inc');

include('./../moduls/customers/classes/GetInfosCustomer.class.php');





function percent($nb1, $nb2)

{

    if(is_numeric($nb1) AND is_numeric($nb2))

    {

        if($nb2)

            $val = round($nb1 * 100 / $nb2,1) - 100 ;

        else

        {

            if ($nb1)

                $val = $nb1*100 ;

            else

                $val = 0;

        }



        if($val < 0)

            return '<span style="color: red;"><i class="fa fa-arrow-down"></i> '.$val.'%</span>';

        else

            return '<span style="color: green;"><i class="fa fa-arrow-up"></i> +'.$val.'%</span>';



    }

    else

        return false;

}



$day_minus_seven  = time() - (60*60*24*7);



if(date('w') == 0)

    $monday_past = $day_minus_seven - 60*60*24*6;

else

    $monday_past = $day_minus_seven - 60*60*24*(date('w')-1);







$lundi_matin = mktime(0, 0, 0, date('m', $monday_past), date('d', $monday_past), date('Y', $monday_past));

//echo $lundi_matin;

$dimanche_soir = $lundi_matin + 60*60*24*7 - 1 ;



$lundi_precedent = $lundi_matin - 60*60*24*7;

$dimanche_precedent = $lundi_matin - 1;











$db_gen = new MySQL();



$sql = 'SELECT mail_contact, nom_societe, id 

        FROM comptes_principaux

        WHERE id != "1"

        AND id != "2"

        AND id != "8"

        AND fin_abo > "' . (time() - (60*60*24*15)).'"
        AND disable_recap_hebdo="0"

        ';

$db_gen->Query($sql);



while($rowg = $db_gen->Row()) {



    $compte = $rowg->id;



    $html = '<h1>Récapitulatif des interventions</h1>

Bonjour, voici le récapitulatif hebodmadaire de vos interventions<br /><br />

            <table style="width: 100%;  border: 1px solid #999;  border-collapse: collapse;  margin: 0 0 1em 0;  caption-side: top;">

                <tr>

                    <th style="text-align: left; border-bottom: 2px solid #999;">

                        Données

                    </th>

                    <th style="text-align: center; border-bottom: 2px solid #999;"">

                        Semaine dernière

                    </th>

                    <th style="text-align: center; border-bottom: 2px solid #999;"">

                        Semaine précédente

                    </th>

                    <th style="text-align: center; border-bottom: 2px solid #999;"">

                        Tendance

                    </th>

                </tr>

                ';



    $db = new MySQL();

    $sql = 'SELECT COUNT(*) AS nb FROM interventions WHERE time_cloture BETWEEN "' . $lundi_matin . '" AND "' . $dimanche_soir . '" AND type_atelier_rdv = "1" AND compte_principal = "' . $compte . '" ';

    $db->Query($sql);

    $row = $db->Row();



    $nb_cloture_week_a = $row->nb;

    if (!$nb_cloture_week_a)

        $nb_cloture_week_a = '0';



    $sql = 'SELECT COUNT(*) AS nb FROM interventions WHERE time_cloture BETWEEN "' . $lundi_precedent . '" AND "' . $dimanche_precedent . '" AND type_atelier_rdv = "1" AND compte_principal = "' . $compte . '" ';

    $db->Query($sql);

    $row = $db->Row();

//echo $db->GetHTML();

    $nb_cloture_week_precedent_a = $row->nb;

    if (!$nb_cloture_week_precedent_a)

        $nb_cloture_week_precedent_a = '0';



    $sql = 'SELECT COUNT(*) AS nb FROM interventions WHERE time_cloture BETWEEN "' . $lundi_matin . '" AND "' . $dimanche_soir . '" AND type_atelier_rdv = "2" AND compte_principal = "' . $compte . '" ';

    $db->Query($sql);

    $row = $db->Row();



    $nb_cloture_week_r = $row->nb;

    if (!$nb_cloture_week_r)

        $nb_cloture_week_r = '0';



    $sql = 'SELECT COUNT(*) AS nb FROM interventions WHERE time_cloture BETWEEN "' . $lundi_precedent . '" AND "' . $dimanche_precedent . '" AND type_atelier_rdv = "2" AND compte_principal = "' . $compte . '" ';

    $db->Query($sql);

    $row = $db->Row();

//echo $db->GetHTML();

    $nb_cloture_week_precedent_r = $row->nb;

    if (!$nb_cloture_week_precedent_r)

        $nb_cloture_week_precedent_r = '0';





    $html .= '

                <tr>

                    <td style="border-bottom: 2px solid #999;"">

                        Interventions clôturées

                    </td>

                    <td  style="text-align: center; border-bottom: 2px solid #999;">

                        <strong>' . ($nb_cloture_week_a + $nb_cloture_week_r) . '</strong>

                        <hr />

                        <span style="font-size: 0.8em">

                            ' . $nb_cloture_week_a . ' en atelier<br />

                            ' . $nb_cloture_week_r . ' sur site

                         </span>

                    </td>

                    <td style="text-align: center; border-bottom: 2px solid #999;">

                        <strong>' . ($nb_cloture_week_precedent_a + $nb_cloture_week_precedent_r) . '</strong>

                        <hr />

                        <span style="font-size: 0.8em">

                            ' . $nb_cloture_week_precedent_a . ' en atelier<br />

                            ' . $nb_cloture_week_precedent_r . ' sur site

                         </span>

                    </td>

                    <td style="text-align: center; border-bottom: 2px solid #999;">

                        <strong>' . percent($nb_cloture_week_a + $nb_cloture_week_r, $nb_cloture_week_precedent_a + $nb_cloture_week_precedent_r) . '</strong>

                        <hr />

                        <span style="font-size: 0.8em">

                            ' . percent($nb_cloture_week_a, $nb_cloture_week_precedent_a) . ' en atelier<br />

                            ' . percent($nb_cloture_week_r, $nb_cloture_week_precedent_r) . ' sur site

                         </span>

                    </td>

                </tr>';



//echo percent($nb_cloture_week, $nb_cloture_week_precedent);



    /*

     * Sommes prestations

     */



    $sql = 'SELECT SUM(duree) AS sommes 

        FROM prestations_effectuees AS pe

        INNER JOIN interventions AS i

        ON (i.id_inter = pe.id_inter)

        AND (i.compte_principal = "' . $compte . '")

        WHERE pe.compte_principal = "' . $compte . '"

        AND i.time_cloture BETWEEN "' . $lundi_matin . '" AND "' . $dimanche_soir . '"

        AND i.type_atelier_rdv = "1"

        ';

    $db->Query($sql);

    $row = $db->Row();

    $heure_week_a = $row->sommes;

    if (!$heure_week_a)

        $heure_week_a = '0';



    $sql = 'SELECT SUM(duree) AS sommes 

        FROM prestations_effectuees AS pe

        INNER JOIN interventions AS i

        ON (i.id_inter = pe.id_inter)

        AND (i.compte_principal = "' . $compte . '")

        WHERE pe.compte_principal = "' . $compte . '"

        AND i.time_cloture BETWEEN "' . $lundi_precedent . '" AND "' . $dimanche_precedent . '"        

        AND i.type_atelier_rdv = "1"

        ';

    $db->Query($sql);

    $row = $db->Row();

    $heure_week_precedent_a = $row->sommes;

    if (!$heure_week_precedent_a)

        $heure_week_precedent_a = '0';



    $sql = 'SELECT SUM(duree) AS sommes 

        FROM prestations_effectuees AS pe

        INNER JOIN interventions AS i

        ON (i.id_inter = pe.id_inter)

        AND (i.compte_principal = "' . $compte . '")

        WHERE pe.compte_principal = "' . $compte . '"

        AND i.time_cloture BETWEEN "' . $lundi_matin . '" AND "' . $dimanche_soir . '"

        AND i.type_atelier_rdv = "2"

        ';

    $db->Query($sql);

    $row = $db->Row();

    $heure_week_r = $row->sommes;

    if (!$heure_week_r)

        $heure_week_r = '0';





    $sql = 'SELECT SUM(duree) AS sommes 

        FROM prestations_effectuees AS pe

        INNER JOIN interventions AS i

        ON (i.id_inter = pe.id_inter)

        AND (i.compte_principal = "' . $compte . '")

        WHERE pe.compte_principal = "' . $compte . '"

        AND i.time_cloture BETWEEN "' . $lundi_precedent . '" AND "' . $dimanche_precedent . '"        

        AND i.type_atelier_rdv = "2"

        ';

    $db->Query($sql);

    $row = $db->Row();

    $heure_week_precedent_r = $row->sommes;

    if (!$heure_week_precedent_r)

        $heure_week_precedent_r = '0';



    $html .= '

                <tr>

                    <td style="border-bottom: 2px solid #999;">

                        Heures facturées

                    </td>

                    <td style="text-align: center; border-bottom: 2px solid #999;">

                        <strong>' . ($heure_week_a + $heure_week_r) . 'h</strong>

                        <hr />

                        <span style="font-size: 0.8em">

                            ' . $heure_week_a . 'h en atelier<br />

                            ' . $heure_week_r . 'h sur site

                        </span>

                    </td>

                    <td style="text-align: center; border-bottom: 2px solid #999;">

                        <strong>' . ($heure_week_precedent_a + $heure_week_precedent_r) . 'h</strong>

                        <hr />

                        <span style="font-size: 0.8em">

                            ' . $heure_week_precedent_a . 'h en atelier<br />

                            ' . $heure_week_precedent_r . 'h sur site

                        </span>

                    </td>

                    <td style="text-align: center; border-bottom: 2px solid #999;">

                        <strong>' . percent(($heure_week_a + $heure_week_r), $heure_week_precedent_a + $heure_week_precedent_r) . '</strong>

                        <hr />

                        <span style="font-size: 0.8em">

                            ' . percent($heure_week_a, $heure_week_precedent_a) . ' en atelier<br />

                            ' . percent($heure_week_r, $heure_week_precedent_r) . ' sur site

                        </span>

                    </td>

                </tr>';





    $sql = 'SELECT count(i.id_materiel) AS nb, m.nom 

        FROM interventions AS i 

        INNER JOIN materiels AS m 

        ON (i.id_materiel = m.id)

        AND (m.compte_principal = "' . $compte . '")

        WHERE i.compte_principal = "' . $compte . '"

        AND i.time_cloture BETWEEN "' . $lundi_matin . '" AND "' . $dimanche_soir . '"

        AND i.id_materiel > "0"

        GROUP BY i.id_materiel 

        ORDER BY nb DESC

        LIMIT 1';

    $db->Query($sql);

    $row = $db->Row();

    $materiel_week_name = $row->nom;

    $materiel_week_nb = $row->nb;



    $sql = 'SELECT count(i.id_materiel) AS nb, m.nom 

        FROM interventions AS i 

        INNER JOIN materiels AS m 

        ON (i.id_materiel = m.id)

        AND (m.compte_principal = "' . $compte . '")

        WHERE i.compte_principal = "' . $compte . '"

        AND i.time_cloture BETWEEN "' . $lundi_precedent . '" AND "' . $dimanche_precedent . '"

        AND i.id_materiel > "0"

        GROUP BY i.id_materiel 

        ORDER BY nb DESC

        LIMIT 1';

    $db->Query($sql);

    $row = $db->Row();

    $materiel_week_precedent_name = $row->nom;

    $materiel_week_precedent_nb = $row->nb;



    $html .= '

                <tr>

                    <td>

                        Matériel le plus réparé

                    </td>

                    <td style="text-align: center">

                        <strong>' . $materiel_week_name . ' (' . $materiel_week_nb . ')</strong>

                    </td>

                    <td style="text-align: center">

                        <strong>' . $materiel_week_precedent_name . ' (' . $materiel_week_precedent_nb . ')</strong>

                    </td>

                    <td style="text-align: center">

                        <strong>-</strong>

                    </td>

                </tr>';





    $html .= '

            </table>';

   // echo $html;



    $mail = new Maileur('Récapitulatif hebdomadaire - '.$rowg->nom_societe.' - Managy.fr');

    $mail->addDest($rowg->mail_contact);

    $mail->AddTitle('Récapitulatif hebodmadaire');

    $mail->body($html);

    $mail->send();

}

