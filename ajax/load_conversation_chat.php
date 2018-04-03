<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/03/2016
 * Time: 17:40
 */


require('../classes/mysql.class.php');
require('../moduls/staffs/classes/GetInfosStaff.class.php');
require('../functions/parseDate.func.inc');
require('../functions/right.func.inc');
require('../templates/v4/classes/Font.class.php');
$db = new MySQL();

$db->Query('SELECT last_chat, visible_chat FROM staffs WHERE id="'.$_SESSION['id'].'" ');
$r = $db->Row();

$last_chat = $r->last_chat;
$visible = $r->visible_chat;

if($last_chat)
{
    $db->Query('SELECT * FROM (SELECT * FROM chat WHERE (id_expediteur = "'.$_SESSION['id'].'" AND id_destinataire = "'.$last_chat.'") OR (id_destinataire = "'.$_SESSION['id'].'" AND id_expediteur = "'.$last_chat.'") ORDER BY id DESC LIMIT 45) r ORDER BY r.id ASC ');
    while($row = $db->Row()) {

        $staff = new GetInfosStaff($row->id_expediteur, true);
        if ($_SESSION['template'] == 'v4')
        {
            if ($row->id_expediteur == $_SESSION['id']) {
                $cote = 'out';
                $color = '55C1E7';
                if($row->lu)
                    $lu = ' <span style="color: #55C1E7;" title="Message lu">'.new Font('check').'</span>';
                else
                    $lu = '';

            } else {
                $cote = 'in';
                $color = 'FA6F57';
                $lu ='';
            }

            $texte = $row->message;

            $texte = preg_replace('#http://[a-z0-9._/-?-{}&%\#]+#i', '<a href="$0" target="_blank">$0</a>', $texte);
            $texte = preg_replace('#https://[a-z0-9._/-?-{}&%\#]+#i', '<a href="$0" target="_blank">$0</a>', $texte);

            echo '<div class="post '.$cote.'" id="msg-' . $row->id . '">
                                <img class="avatar" alt="" src="https://placehold.it/50/'.$color.'/fff&text='.strtoupper($staff->GetPrenom()[0].$staff->GetNom()[0]).'" />
                                <div class="message">
                                    <span class="arrow"></span>
                                    <a href="./s'.$row->id_expediteur.'" class="name">' . $staff->GetPrenom() . ' ' . $staff->GetNom() . '</a>
                                    <span class="datetime">' . parse_date($row->timestamp) . $lu.'</span>
                                    <span class="body"> ' . $texte .  ' </span>
                                </div>
                            </div>';

        }
        else
        {
            if ($row->id_expediteur == $_SESSION['id']) {
                $cote = 'right';
                $color = '55C1E7';
            } else {
                $cote = 'left';
                $color = 'FA6F57';
            }

            $email = $staff->GetMail();
            $default = "mm"; //"https://cdn4.iconfinder.com/data/icons/linecon/512/photo-48.png"; //http://placehold.it/50/'.$color.'/fff&text='.$staff->GetPrenom()[0].$staff->GetNom()[0].'";
            $size = 48;

            $grav_url = "http://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?d=" . urlencode($default) . "&s=" . $size;

            $texte = $row->message;

            $texte = preg_replace('#http://[a-z0-9._/-?-{}&%\#]+#i', '<a href="$0" target="_blank">$0</a>', $texte);
            $texte = preg_replace('#https://[a-z0-9._/-?-{}&%\#]+#i', '<a href="$0" target="_blank">$0</a>', $texte);

            echo '
<li class="' . $cote . ' clearfix" id="msg-' . $row->id . '">
    <span class="chat-img pull-' . $cote . '">
        <img src="' . $grav_url . '" alt="User Avatar" class="img-square" />
    </span>
    <div class="chat-body clearfix">
        <div class="header">
            <strong class="primary-font">' . $staff->GetPrenom() . ' ' . $staff->GetNom() . '</strong> <small class="pull-right text-muted">
                <span class="glyphicon glyphicon-time"></span>' . parse_date($row->timestamp) . '</small>
        </div>
        <p>
            ' . $texte . '
        </p>
    </div>
</li>';
        }
    }

    if($visible == 'open' OR $_SESSION['template'] == 'v4')
    {
        $db->Query('UPDATE chat SET lu="1" WHERE id_expediteur = "'.$last_chat.'" AND id_destinataire="'.$_SESSION['id'].'" ');
    }
}



?>
