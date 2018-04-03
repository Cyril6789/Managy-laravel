<?php session_start();


if(isset($_POST['send']) AND !empty($_POST['send']))
{
    global $db;

    $chantier = $db->SQLfix($_POST['chantier']);
    $id_client = $db->SQLfix($_POST['id_client']);
    if($_POST['forfait'] == 'on')
        $forfait = 1;
    else
        $forfait = 0;

    
    $db->Query('SELECT id_inter FROM interventions WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id_inter DESC LIMIT 1');
    $row = $db->Row();
    
    $derniere_inter = $row->id_inter;
    $nouvelle_inter = $derniere_inter + 1;
    

        $db->Query('INSERT INTO interventions (id_inter, id_staff_ouverture, time_ouverture, id_client, projet,   compte_principal) VALUES ("'.$nouvelle_inter.'", "'.$_SESSION['id'].'", "'.time().'", "'.$id_client.'", "'.$chantier.'", "'.$_SESSION['compte_principal'].'" )');

   
   
    $log = New addLog('a créé l\'intervention');
    $log->setInter($nouvelle_inter);
    $log->insert();
   

   header('Location: intervention-'.$nouvelle_inter);
    
}


?>