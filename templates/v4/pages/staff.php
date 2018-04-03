<?php session_start();

if($gerant)
    $sub_title =  'Gérant';
$tab = new Tab();
$tab->fullWidth();
$tab->setRowContent();


$col_avat = new Col(2, 'md');

$col_avat->setContent('<div class="list-group">
											<li class="list-group-item no-padding">
												<img src="assets/img/avatars/avatar.png" alt="" width="100%">
											</li>
											<a href="javascript:void(0);" class="list-group-item">Changer d\'avatar</a>
										</div>');



$col_profile = new Col(10, 'md');


$col_gauche = new Col(5, 'md');

$info = ''; //new Info('You will receive all future updates for free!', false);
//$info->noCol();

$col_gauche->setContent($info.'<h1>'.$prenom.' '.$nom.'</h1><dl class="dl-horizontal">
													<dt>Adresse mail :</dt>
													<dd>'.$mail.'</dd>	
									                <dt>Nombre de connexions :</dt>
									                <dd>'.$nb_login.'</dd>
									                <dt>Dernière connexion :</dt>
									                <dd>'.parse_date($date_derniere_connexion).'</dd>
									                <dt>Interventions clôturées :</dt>
									                <dd>'.$nb_inter.'</dd>
												</dl>');


$col_droite = new Col(7, 'md');

$graph_inter = new GraphsLines('inter');
$graph_inter->addColor('blue', 'Intervention atelier ');

$table_time_atelier = Array();
$table_exist_atelier = array();
foreach($tab_graph AS $val)
{
    if($val['type'] == 1)
    {
        $time = mktime(0,0,0,$val['mois'], 1, $val['annee']);
        $table_time_atelier[] = $time;
        $table_exist_atelier[$time]['val'] = $val['nb'];
        //$graph_inter->addData($val['nb'], $time);
    }

}

$y = date('Y');
for($m = date('n') - 11; $m<=date('n'); $m++) {
    if ($m < 1) {
        $mois = $m + 12;
        $annee = $y - 1;

    } else {
        $mois = $m;
        $annee = $y;
    }
    $timestamp = mktime(0, 0, 0, $mois, 1, $annee);

    if(!in_array($timestamp, $table_time_atelier))
        $graph_inter->addData(0, $timestamp);
    else
        $graph_inter->addData($table_exist_atelier[$timestamp]['val'], $timestamp);
}


$graph_inter->addColor('yellow', 'Intervention sur site');
$table_time_site = Array();
$table_exist_site = array();

foreach($tab_graph AS $val)
{
    if($val['type'] == 2)
    {
        $time = mktime(0,0,0,$val['mois'], 1, $val['annee']);
        $table_time_site[] = $time;
        $table_exist_site[$time]['val'] = $val['nb'];
        //$graph_inter->addData($val['nb'], $time);
    }

}

for($m = date('n') - 11; $m<=date('n'); $m++) {
    if ($m < 1) {
        $mois = $m + 12;
        $annee = $y - 1;

    } else {
        $mois = $m;
        $annee = $y;
    }
    $timestamp = mktime(0, 0, 0, $mois, 1, $annee);

    if(!in_array($timestamp, $table_time_site))
        $graph_inter->addData(0, $timestamp);
    else
        $graph_inter->addData($table_exist_site[$timestamp]['val'], $timestamp);
}
$col_droite->setContent($graph_inter);




$row_profile = new Row($col_gauche.$col_droite , 'profile-info');


$logs = new Feeds();

foreach ($tab_logs AS $log)
{
    $add_text = '';
    $lien = '';
    if($log['id_inter'])
    {
               
        $add_text = '(Intervention N°'.$log['prefix'].$log['id_inter'].')';
        $lien = './i'.$log['id_inter'];
    }

    if($log['id_client'])
    {
        $client = new  GetInfosCustomer($log['id_client']);
        $add_text = '('.$client->GetTitre().' '.$client->GetNom().' '.$client->GetPrenom().')';
        $lien = $client->GetProfileLink();
    }

    $logs->addLine($prenom.' '.$log['texte'].' '.$add_text, parse_date($log['time']), $lien);
}

$row_logs = new Row($logs);

$col_logs = new Col(12, 'md');
$col_logs->scrollable();
$col_logs->setContent($row_logs);

$col_profile->setContent($row_profile.'<br />'.$col_logs);


$tab->addPane('Vue d\'ensemble', $col_avat.$col_profile);

$col_full = new Col(12, 'md');
$col_full->setContent($tab);

$row_gen = new Row($col_full);

echo $row_gen;


?>

