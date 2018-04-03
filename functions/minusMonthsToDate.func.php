<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 22/03/2016
 * Time: 09:37
 */

function minusMonthsToDate($date, $addMonths)
{
    $max_month = date('t', $date);
    $day = date('d', $date);
    $month = date('m', $date);
    $year = date('Y', $date);

    $mois_presque_final = $month - $addMonths; //on prend le mois actuel on lui soustrait le nombre de mois.

    if($mois_presque_final < 1) // si �a d�passe 1, c'est qu'on est � l'ann�e précédente
    {
        $mois_final = $mois_presque_final + 12;
        $annee_finale = $year - 1;
    }
    else
    {
        $mois_final = $mois_presque_final;
        $annee_finale = $year;
    }

    if($max_month == $day)
    {
        $temp = mktime(23, 59, 59, $mois_final, 1, $annee_finale); //on cr�� un timestamp du 1er du mois
        $max_temp = date('t', $temp);

        return mktime(23, 59, 59, $mois_final, $max_temp, $annee_finale);
    }
    else
    {
        if(date('m', mktime(23, 59, 59, $mois_final, $day, $annee_finale)) != $mois_final )
        {
            $temp = mktime(23, 59, 59, $mois_final, 1, $annee_finale); //on cr�� un timestampt du 1er du mois
            $max_temp = date('t', $temp);

            return mktime(23, 59, 59, $mois_final, $max_temp, $annee_finale);
        }
        else
            return mktime(23, 59, 59, $mois_final, $day, $annee_finale);
    }

}

?>