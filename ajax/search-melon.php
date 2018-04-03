<?php session_start();
if(!$_SESSION['compte_principal'])
    die();
/*
 * This is an example for the search. It returns
 * a JSON array of objects:
 * Format:
   [
		{
			"href" : "link to target page",
			"text" : "text which is shown in the results",
			"img" : "a URL to a 50x50 pixel image which is displayd with the result (optional)"
		},
		[more results...]
   ]
 */

ob_start();





require('../classes/mysql.class.php');
require('../functions/AccesActivableModul.func.inc');
require('../functions/right.func.inc');
$db = new MySQL();


function buttons_client($id_client, $client)
{
    $db = new MySQL();

    if(right('customers', 1))
        $html = '<a href="./c'.$id_client.'" title="fiche client"><img src="./templates/mango/img/icons/packs/fugue/16x16/user.png" width="20px"/></a>';
    if(right('inter', 2) AND AccesActivableModul('intervention_immo'))
        $html .= '<a href="./affaire-new-customer-'.$id_client.'" title="Créer une intervention"><img src="./templates/mango/img/icons/packs/fugue/16x16/plus.png" width="20px"/></a>';
    if(right('inter', 2) AND AccesActivableModul('intervention_holtzmann') OR AccesActivableModul('intervention_informatique'))
        $html .= '<a href="./intervention-new-customer-'.$id_client.'" title="Créer une intervention"><img src="./templates/mango/img/icons/packs/fugue/16x16/plus.png" width="20px"/></a>';
    if(right('calendar', 2) AND AccesActivableModul('calendar'))
        $html .= '<a href="javascript:void();" onclick="$(\'#modal_add_rdv\').modal(\'show\'); $(\'#client_add_rdv\').html(\''.$client.'\'); $(\'#id_client_add_rdv\').val(\''.$id_client.'\');" title="Créer un rendez-vous"><img src="./templates/mango/img/icons/packs/fugue/16x16/calendar.png" width="20px"/></a>';

    $db->Query('SELECT id_inter, prefix, (SELECT COUNT(*) FROM prise_en_charge AS pec WHERE pec.id_intervention=interventions.id_inter AND  pec.compte_principal="'.$_SESSION['compte_principal'].'" ) AS nb_pec FROM interventions WHERE id_client = "'.$id_client.'" AND time_cloture="0" AND compte_principal="'.$_SESSION['compte_principal'].'"  ');


    while($data = $db->Row())
    {
        if($data->nb_pec)
            $couleur = 'orange';
        else
            $couleur = 'red';
        //if(AccesActivableModul('intervention_immo'))
        //{

         //   $html .= '<a style="display: inline-block;" href="./affaire-' . $data->id_inter . '" title="Aller à l\'affaire N°' . $data->id_inter . '"><span class="badge block ' . $couleur . '">' . $data->id_inter . '</span></a> ';
       // }
        //else
            //$html .= '<a style="display: inline-block;" href="./intervention-'.$data->id_inter.'" title="Aller à l\'intervention N°'.$data->id_inter.'"><span class="badge block '.$couleur.'">'.$data->id_inter.'</span></a> ';
    }

  /*  if(right('pack-maintenance', 1))
        echo 'ok';
    else
        echo 'ko';
        */
    if(AccesActivableModul('pack_maintenance')  AND right('pack-maintenance', 1) )
    {
        $requete = "SELECT SUM(mouvements) AS solde
        FROM maintenance
        WHERE id_client = '".$id_client."'
        AND compte_principal = '".$_SESSION['compte_principal']." '";
        $db->Query($requete);
        $d = $db->Row();

        if($d->solde)
        {
            $heures = floor(abs($d->solde));
            $minutes = (abs($d->solde) - $heures) * 60;
            if($d->solde < 0)
                $moins = '-';
            else
                $moins = '';

            if(round($minutes) < 10)
                $solde = $moins.$heures.'h0'.round($minutes);
            else
                $solde = $moins.$heures.'h'.round($minutes);
        }
        else
            $solde = 0;

        if($d->solde)
        {
            $solde_display = $solde;

            $sql = 'SELECT COUNT(*) AS nb
            FROM chrono_maintenance
            WHERE debut > 0
            AND id_client = "'.$id_client.'"
            AND compte_principal ="'.$_SESSION['compte_principal'].'"  ';
            $db->Query($sql);

            $d = $db->Row();

           /* if($d->nb)
                $html .= '<a href="?action=stop" title="Arrêter le chronomètre de la maintenance"><img src="./templates/mango/img/icons/packs/fugue/16x16/control-stop-square.png" width="20px" /></a>';
            else
                $html .= '<a href="?action=start" title="Démarrer le chronomètre de la maintenance"><img src="./templates/mango/img/icons/packs/fugue/16x16/control.png" width="20px" /></a>';
*/

            $html .= ' ('.$solde_display.' restants)';
        }
    }



    return $html;
}

$term = trim($_GET['request']);
$term = $db->SQLFix($term);

if(!is_numeric($term))
{

    $tab = explode(' ', $term);

    $sql = 'SELECT id, titre, nom, prenom FROM clients WHERE';

    foreach($tab AS $value)

        $sql .=' (nom like "%' . $value . '%" OR prenom LIKE "%'.$value.'%" ) AND';



    $sql .= ' compte_principal = "' . $_SESSION['compte_principal'] . '" AND archive="0" LIMIT 10 ';
    $db->Query($sql);
    $result = Array();
    $i = 0;

    while ($data = $db->Row()) {



        $result[$i] = array(
            'href' => './c' . $data->id,
            'title' => $data->titre . ' ' . $data->nom . ' ' . $data->prenom,
            'sub' => buttons_client($data->id, $data->titre . ' ' . $data->nom . ' ' . $data->prenom),
        );
        $i++;
    }

    $type = 'Clients';
    $icone = 'user';


}
else
{
    $sql = 'SELECT clients.titre, clients.nom, clients.prenom, id_inter, prefix
            FROM interventions
            INNER JOIN clients
            ON (clients.id = interventions.id_client)
            AND (clients.compte_principal = "'.$_SESSION['compte_principal'].'")
            WHERE interventions.id_inter LIKE "'.$term.'%"
            AND interventions.compte_principal ="'.$_SESSION['compte_principal'].'"
             LIMIT 10  ';

    $db->Query($sql);
    $result = Array();
    $i = 0;

    while ($data = $db->Row()) {
        $result[$i] = array(
            'href' => './i' . $data->id_inter,
            'title' => 'Intervention N°'.$data->prefix.$data->id_inter,
            'sub' => $data->titre . ' '.$data->nom.' '.$data->prenom,
        );
        $i++;
    }

    $type = 'Interventions';
    $icone = 'wrench';

}
//print_r($result);
if(is_file('../templates/melon/ajax/search.php'))
  include('../templates/melon/ajax/search.php');
else
    echo  'Erreur lors du chargement du fichier ajax';



ob_end_flush();
?>