<?php session_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function AccesActivableModul($modul, $cp=0)
{
    if($cp)
        $compte_principal = $cp;
    else
        $compte_principal = $_SESSION['compte_principal'];
    $acces = false;

    $db = new MySQL();
    $module = $db->SQLfix($modul);
    $db->Query('SELECT mc.type, mc.prix, ma.date_activation, ma.fin_abo, ma.acces, ma.date_fin
                FROM modules_comptes AS mc
                LEFT JOIN modules_acces AS ma
                ON (ma.nom = mc.nom)
                AND (ma.compte_principal = "'.$compte_principal.'")
                WHERE mc.nom="'.$module.'" AND mc.compte_principal="'.$compte_principal.'"');

    $row = $db->Row();

    $type = $row->type;
    if($type == 2) //Activable
    {
        if($row->acces) //Activé ?
        {
            if($row->prix > 0) //Payant ?
            {
                if($row->fin_abo OR ($row->date_fin > time())) //Accès jusqu'a la fin de l'abo de base, ou date de fin future
                    $acces = true;
                else
                {
                    if ($row->date_activation + (15 * 24 * 60 * 60) > time()) //Periode d'essai
                        $acces = true;
                    else
                        $acces = false;
                }
            }
            else
                $acces = true;
        }
        else
            $acces = false;
    }
    if($type == 1) //activé de force
        $acces = true;

    return $acces;
}


?>
