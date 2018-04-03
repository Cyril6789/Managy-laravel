<?php session_start();
function aleat($longueur=10)
{
    $lettres = Array ('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');

    $string = '';
    $chiffre = false;
    for($i=0; $i<$longueur; $i++)
    {
        if($chiffre)
        {
            $string .= rand(0, 9);
            $chiffre = false;
        }
        else{
            $string .= $lettres[rand(0, count($lettres)-1)];
            $chiffre = true;
        }
    }
    return $string;
}


    if(right('staffs', 1))
    {
        
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/staffs.php'))
        {
            $gerant = new DatabaseWorker('staffs');
            $gerant->setWidget('Gérant');
            $gerant->displayedFields(Array('nom', 'prenom', 'mail'));
            $gerant->labelsDisplayedFields(Array('Nom', 'Prénom', 'Adresse e-mail'));
            $gerant->addWhere('gerant="1"');
            $gerant->addOpenLink('Profile', './staffs-staff-$', 'id', 'eye');

            $staffs = new DatabaseWorker('staffs');
            $staffs->setWidget('Collaborateurs');
            $staffs->displayedFields(Array('nom', 'prenom', 'mail', 'pass', 'cal_color', 'licence'));
            $staffs->labelsDisplayedFields(Array('Nom', 'Prénom', 'Adresse e-mail', 'Mot de passe', 'Couleur du calendrier', 'Licence associée'));
            $staffs->addColorFields('cal_color');
            $staffs->addWhere('gerant!="1"');



            $error = new Danger('Aucune licence', false);



            //$staffs->noPrintModals();
            //$staffs->addIfValue('licence', '0', $error, 'liences_staffs.id', 0);
            $staffs->passwordFields('pass');
            $staffs->innerJoin('licences_staffs', 'licence', 'nom', true, false, true);
            $staffs->addOpenLink('Profile', './staffs-staff-$', 'id', 'eye');
            $staffs->addOpenLink('Privilèges', './staffs-rights-$', 'id', 'key');
            $staffs->activeModify('./staffs');
            $hiddens = Array('cal'=> aleat());
            $staffs->activateAdd('./staffs', '', '', '', $hiddens);

            $sql = 'SELECT fin_abo FROM comptes_principaux WHERE id="'.$_SESSION['compte_principal'].'" ';
            $db->Query($sql);
            $row = $db->Row();
            $fin_abo = $row->fin_abo;



            $licences = new DatabaseWorker('licences_staffs');
            $licences->setWidget('Licences disponibles');
            $licences->displayedFields(Array('nom', 'date_fin', 'incluse'));
            $licences->labelsDisplayedFields(Array('Nom de la licence', 'Validité', 'Licence incluse à l\'abonnement ?'));


            if(FREE_ACCESS)
            {
                $licences->addIfValue('date_fin', '0', 'Illimitée', 'incluse', '0');
                $licences->addIfValue('date_fin', '*', 'Illimitée', 'incluse', '1');
                $licences->noModifyFields('incluse');
            }
            else
            {
                $licences->setMaxCountValue('incluse', 1, 'comptes_principaux', 'nombre_staff', 'id');
                $licences->addIfValue('date_fin', '0', 'Licence expirée', 'incluse', '0');
                $licences->addIfValue('date_fin', '*', date('d/m/Y', $fin_abo), 'incluse', '1');

                $prolongement_modal = new Modal('Prolonger une licence', 'prolonger_$');
                $prolongement_modal->noButton();

                $start = date('Y-m-d', $fin_abo);
                $end = date('Y-m-d');
                $datetime1 = new DateTime($start);
                $datetime2 = new DateTime($end);
                $interval = $datetime1->diff($datetime2);
                $nbmonth= $interval->format('%y')*12 +$interval->format('%m') + 1; //Retourne le nombre de mois


                $sql = 'SELECT prix_staff_suppl FROM comptes_principaux WHERE id = "'.$_SESSION['compte_principal'].'" ';
                $db->Query($sql);
                $row = $db->Row();
                $prix_staff_suppl = $row->prix_staff_suppl;

                $prix = $nbmonth * $prix_staff_suppl;


                $form_pro = new FormLayout('Prolonger une licence');
                $form_pro->setFormControls('prolon_$', './Paypal/prepareLicence.php', 'get');
                $form_pro->setValueButton('Payer avec PayPal');
                $form_pro->setWidth(6, 6);
                $hidden = new Hidden('id_licence');
                $hidden->setValue('$');
                $form_pro->addLine('Prolongement de la licence :', $prix.$hidden.'€ HT');

                $prolongement_modal->setContent($form_pro);
                $licences->addOpenLink('Prolonger', $prolongement_modal->getAhref(), 'id', 'eur', $prolongement_modal->getModalHtml(), Array('incluse', 'date_fin'), Array(0, 0));
            }


            $licences->activateAdd('./staffs');
            $licences->activeModify('./staffs');
            $licences->activeDelete('./staffs');
            $licences->addDateFields('date_fin');
            $licences->noModifyFields('date_fin');









            $sql = 'SELECT COUNT(*) AS counter FROM licences_staffs WHERE incluse="1"  AND compte_principal = "'.$_SESSION['compte_principal'].'"';
            $db->Query($sql);
            $row = $db->Row();
            $counter = $row->counter;

            $sql = 'SELECT nombre_staff FROM comptes_principaux WHERE id="'.$_SESSION['compte_principal'].'"';
            $db->Query($sql);
            $row = $db->Row();
            $max_licence = $row->nombre_staff;

            $reste_licence = $max_licence - $counter;

            $sql = 'SELECT COUNT(*) AS nb FROM licences_staffs WHERE compte_principal = "'.$_SESSION['compte_principal'].'"';
            $db->Query($sql);
            $row = $db->Row();
            $nb_licences = $row->nb;

            $sql = 'SELECT COUNT(*) AS nb
                    FROM staffs as s
                    INNER JOIN licences_staffs AS ls
                    ON (ls.id = s.licence)
                    AND (ls.compte_principal = "'.$_SESSION['compte_principal'].'")
                    WHERE s.licence > "0"
                    AND s.compte_principal = "'.$_SESSION['compte_principal'].'" ';
            $db->Query($sql);
            $row = $db->Row();
            $licences_used = $row->nb;

            $reste_collabo = $nb_licences - $licences_used;







            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/pages/staffs.php');
            $ModulContent = ob_get_contents();
            ob_end_clean();
        }
        
    }
    else
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/no_right.php'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/no_right.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
        }
    }
?>