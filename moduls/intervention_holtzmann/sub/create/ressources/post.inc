<?php session_start();


if(isset($_POST['projet']) AND !empty($_POST['projet']))
{
    global $db;

    $projet = $db->SQLfix($_POST['projet']);
    $ref_chanter = $db->SQLfix($_POST['ref_chantier']);
    $estimatif_temps = $db->SQLfix($_POST['temps']);
    $tab_estimatif = explode(':', $estimatif_temps);
    $est = $tab_estimatif[0] * 60 + $tab_estimatif[1];
    $id_client = $db->SQLfix($_POST['id_client']);
    $id_couleur = $db->SQLfix($_POST['id_couleur']);
    $id_finition = $db->SQLfix($_POST['id_finition']);
    $id_matiere = $db->SQLfix($_POST['id_matiere']);
    
    if($_POST['forfait'] == 'on')
        $forfait = 1;
    else
        $forfait = 0;

    
    $db->Query('SELECT id_inter FROM interventions WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id_inter DESC LIMIT 1');
    $row = $db->Row();
    
    $derniere_inter = $row->id_inter;
    $nouvelle_inter = $derniere_inter + 1;
    
    
    if($forfait)
        $db->Query('INSERT INTO interventions (id_inter, id_staff_ouverture, time_ouverture, id_client, projet, ref_chantier, estimatif_temps, id_couleur, id_finition, id_matiere, nombre_balancelle, temps_accroche_decroche, temps_application, temps_emballage, forfait, compte_principal) VALUES ("'.$nouvelle_inter.'", "'.$_SESSION['id'].'", "'.time().'", "'.$id_client.'", "'.$projet.'", "'.$ref_chanter.'", "'.$est.'", "'.$id_couleur.'", "'.$id_finition.'", "'.$id_matiere.'", "1", "00:15", "00:15", "00:15", "'.$forfait.'", "'.$_SESSION['compte_principal'].'" )');
    else
        $db->Query('INSERT INTO interventions (id_inter, id_staff_ouverture, time_ouverture, id_client, projet, ref_chantier, estimatif_temps, id_couleur, id_finition, id_matiere, forfait, compte_principal) VALUES ("'.$nouvelle_inter.'", "'.$_SESSION['id'].'", "'.time().'", "'.$id_client.'", "'.$projet.'", "'.$ref_chanter.'", "'.$est.'", "'.$id_couleur.'", "'.$id_finition.'", "'.$id_matiere.'", "'.$forfait.'", "'.$_SESSION['compte_principal'].'" )');


    if(!$_SESSION['gerant'])
    {
        $notification = new notificationMail(6);
        $notification->tab_parse(Array('%id_inter%' => $nouvelle_inter, '%client%' => $id_client, '%prenom_staff%' => $_SESSION['prenom']));
        $notification->sendMail();
    }
   
   $log = New addLog('a créé l\'intervention');
    $log->setInter($nouvelle_inter);
    $log->insert();
   
    if(!$forfait)
    {
        $db->Query('SELECT id FROM etapes WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');
        while ($row = $db->Row()) 
        {
            $db_2 = new Mysql();
            if($_POST['s'.$row->id] == 'on')
            {
                 $db_2->Query('INSERT INTO etapes_interventions (id_inter, id_etape, comptre_principal) VALUES ("'.$nouvelle_inter.'", "'.$row->id.'", "'.$_SESSION['compte_principal'].'" ) ');

            }
            $db_2->Close();
        }

        $db->Query('SELECT id FROM traitements WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');
        while ($row = $db->Row()) 
        {
            $db_2 = new Mysql();
            if($_POST['t'.$row->id] == 'on')
            {
                 $db_2->Query('INSERT INTO traitements_interventions (id_inter, id_traitement, compte_principal) VALUES ("'.$nouvelle_inter.'", "'.$row->id.'", "'.$_SESSION['compte_principal'].'" ) ');
            }
            $db_2->Close();

        }
    }
   header('Location: intervention-'.$nouvelle_inter);
    
}


?>