<?php session_start();

require('../classes/mysql.class.php');
require('../functions/parseDate.func.inc');
require('../templates/v4/classes/includes.php');
$db = new MySQL();

if($_SESSION['id'] == '1')
    $sql = 'SELECT staffs.id, staffs.prenom, staffs.nom, staffs.status_chat, staffs.last_action, cp.nom_societe
            FROM staffs
            INNER JOIN comptes_principaux AS cp
            ON (cp.id = staffs.compte_principal)
            WHERE staffs.id != "'.$_SESSION['id'].'"
            AND ("'.time().'" - last_action < 1200)
            AND status_chat != "status-offline"
            AND cp.bloque = "0"
            ORDER BY last_action DESC ';
else
    $sql = 'SELECT id, prenom, nom, status_chat, last_action FROM staffs WHERE (compte_principal = "'.$_SESSION['compte_principal'].'" OR id = "1") AND id != "'.$_SESSION['id'].'"  AND ("'.time().'" - last_action < 1200) AND status_chat != "status-offline" ORDER BY last_action DESC ';

$db->Query($sql);
$db2 = new MySQL();
while($row = $db->Row())
{


    $db2->Query('SELECT count(*) AS nb FROM chat WHERE id_expediteur = "'.$row->id.'" AND id_destinataire = "'.$_SESSION['id'].'" AND lu = "0" ');
    $r = $db2->Row();
    $nb = $r->nb;

    if($nb)
        $badge = '<span class="badge badge-danger">'.$nb.'</span>';
    else
        $badge = '';


    if(time() - $row->last_action > 1200)
        $status = 'status-offline';
    else
    {
        if(time() - $row->last_action > 300)
            $status = 'status-away';
        else
            $status = $row->status_chat;
    }


    if($_SESSION['template'] == 'v4')
    {
        if($_SESSION['id'] == '1')
            $societe = $row->nom_societe;
        else
            $societe = '';
        echo '<li class="media" id="'.$row->id.'"> 
                <div class="media-status">
                    '.$badge.'
                </div>
                <img class="media-object" src="https://placehold.it/50/FA6F57/fff&text='.strtoupper($row->prenom[0].$row->nom[0]).'" alt="...">
                <div class="media-body">
                    <h4 class="media-heading"><img src="./templates/melon/img/icons/packs/fugue/16x16/'.$status.'.png" id="img-status-chat"/> '.$row->prenom . '</h4>
                    <div class="media-heading-sub"> '.$societe.' </div>
                </div>
            </li>';
    }
    else
    {
        echo '
        <li id="collabo_' . $row->id . ' >
            <span class="collabo-img">
                <img src="./templates/melon/img/icons/packs/fugue/16x16/' . $status . '.png" id="img-status-chat"/>
            </span>
            <a href="javascript:void();" onclick="select_conversation(\'' . $row->id . '\'); setConversationName(\'Conversation avec ' . $row->prenom . ' ' . $tow->nom . '\')">
                ' . $row->prenom . ' ' . $badge . '
            </a>
        </li>';
    }
}


if($_SESSION['id'] == '1')
    $sql = 'SELECT staffs.id, staffs.prenom, staffs.nom, staffs.status_chat, staffs.last_action, cp.nom_societe
            FROM staffs
            INNER JOIN comptes_principaux AS cp
            ON (cp.id = staffs.compte_principal)
            WHERE staffs.id != "'.$_SESSION['id'].'"
            AND (
                ("'.time().'" - last_action >= 1200)
                OR status_chat = "status-offline"
                )
            AND cp.bloque ="0"
            ORDER BY last_action DESC ';
else
    $sql = 'SELECT id, prenom, nom, status_chat, last_action FROM staffs WHERE (compte_principal = "'.$_SESSION['compte_principal'].'" OR id = "1") AND id != "'.$_SESSION['id'].'"  AND (("'.time().'" - last_action >= 1200) OR status_chat = "status-offline") ORDER BY last_action DESC ';
$db2 = new MySQL();
$db->Query($sql);

while($row = $db->Row())
{

    $db2->Query('SELECT count(*) AS nb FROM chat WHERE id_expediteur = "'.$row->id.'" AND id_destinataire = "'.$_SESSION['id'].'" AND lu = "0" ');
    $r = $db2->Row();
    $nb = $r->nb;

    if($nb) {
        $badge = new Badge($nb);
    }
    else
        $badge = '';

    if($_SESSION['id'] == 1)
        $timer = ''; //'('.parse_date($row->last_action).') ';
            else
        $timer = '';

    if(time() - $row->last_action > 1200)
        $status = 'status-offline';
    else
    {
        if(time() - $row->last_action > 300)
            $status = 'status-away';
        else
            $status = $row->status_chat;
    }



    if($_SESSION['template'] == 'v4')
    {
        if($_SESSION['id'] == '1')
            $societe = $row->nom_societe;
        else
            $societe = '';
        echo '<li class="media" id="'.$row->id.'">
                    <div class="media-status">
                        '.$badge.'
                    </div>
                    <img class="media-object" src="https://placehold.it/50/FA6F57/fff&text='.strtoupper($row->prenom[0].$row->nom[0]).'" alt="...">
                    <div class="media-body">
                                                    
                        <h4 class="media-heading"><img src="./templates/melon/img/icons/packs/fugue/16x16/'.$status.'.png" id="img-status-chat"/> '.$row->prenom.'</h4>
                        <div class="media-heading-sub"> '.$societe.' </div>
                    </div>
                </li>';
    }
    else
    {
        echo '
        <li id="collabo_'.$row->id.' >

            <span class="collabo-img">
                <img src="./templates/melon/img/icons/packs/fugue/16x16/'.$status.'.png" id="img-status-chat"/>
            </span>
            <a href="javascript:void()" onclick="select_conversation(\''.$row->id.'\'); setConversationName(\'Conversation avec '.$row->prenom.' '.$tow->nom.'\')">
                '.$row->prenom.' '.$timer.$badge.'
            </a>
        </li>';
    }
}


?>