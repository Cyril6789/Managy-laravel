<?php session_start();


global $tab_messages_site;

//print_r($tab_messages_site);

$alert='';
foreach ($tab_messages_site AS $mess)
{
    if($mess['type'] == 'warning')
        $alert = new Warning($mess['message'], false);
    if($mess['type'] == 'danger')
        $alert = new Danger($mess['message'], false);
    if($mess['type'] == 'info')
        $alert = new Info($mess['message'], false);
    if($mess['type'] == 'success')
        $alert = new Success($mess['message'], false);
    $row = new Row($alert);
    echo $row;
}

$title = new Col('8');
$title->setContent('<h1 class="page-title">'.$page_title.'<br />
    <small>'.$sub_title.'</small>
</h1>');

//echo $title;
if($right_title) {
    $right_title_box = new Col('4');
    $right_title_box->setContent('<br />'.$right_title);
}

$row = new Row($title.$right_title_box);

echo $row;
//echo $right_title;


?>
<!-- BEGIN PAGE TITLE-->

<!--
-->
<!-- END PAGE TITLE-->
<!-- END PAGE HEADER-->
    

    <!-- BEGIN PAGE BASE CONTENT -->
<?php






    global $notification_maintenance;
    global $site_ferme;
    if($notification_maintenance)
    {
    $maintenance_prevue = new Warning($notification_maintenance, false);
    echo $maintenance_prevue;
    }

    if($site_ferme)
    {
    $maintenance_en_cours = new Danger($site_ferme, false);
    echo $maintenance_en_cours;
    }



if(END_SUBSCRIPTION < time() AND !FREE_ACCESS)
{
    if(END_SUBSCRIPTION < time() - (DAYS_BEFORE_RESTRICTION * 24 * 60 * 60))
        $acces_account_error = new Danger('Votre accès est restreint. Merci de prolonger votre abonnement', false);
    else
        $acces_account_error = new Danger('Votre accès sera restreint le '.date('d/m/Y', END_SUBSCRIPTION + (DAYS_BEFORE_RESTRICTION * 24 * 60 * 60) ).'. Merci de prolonger votre abonnement');


    $acces_account_error = new Row($acces_account_error);
    echo $acces_account_error;
}


    echo $html_body;


?>


</div>
<!-- END CONTENT BODY -->
</div>
<!-- END CONTENT -->