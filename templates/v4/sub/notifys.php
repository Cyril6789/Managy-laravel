<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 01/02/2017
 * Time: 18:31
 */
?>
<script>
    function toggle_notify(id_notify, div) {
        if ($('input[id=' + id_notify + ']').is(':checked')) {
            $('#'+div).html(parseInt( $('#'+div).html()) + 1);
            klient = new XMLHttpRequest();
            klient.open("GET", "./ajax/notifys.php?id_notify=" + id_notify + "&action=delete");
            klient.send(null);
        }
        else {
            $('#'+div).html(parseInt( $('#'+div).html()) - 1);
            klient = new XMLHttpRequest();
            klient.open("GET", "./ajax/notifys.php?id_notify=" + id_notify + "&action=insert");
            klient.send(null);
        }
    }
</script>
<?php


$tabs = new Tab('left');
$tabs->fullWidth();
foreach($tab_cat AS $cat)
{
    $widget = new FormLayout('Recevoir une notification par mail lorsque : ');
    $widget->setFormControls('notifications');
    $widget->setWidth(4);
    $nb_noti = 0;
    $nb_noti_checked = 0;
    foreach ($tab_notifys AS $notify)
    {
        if($cat['id'] == $notify['id_categorie']) {
            $noti = new Switches('noti_' . $notify['id']);
            if (!in_array($notify['id'], $tab_disabled_notifys)) {
                $noti->checked();
                $nb_noti_checked++;
            }
            $noti->onChange('toggle_notify(\'noti_' . $notify['id'] . '\', \'checked_'.$cat['id'].'\')');
            $widget->addLine($notify['nom'], $noti);
            $nb_noti++;
        }
    }
    $tabs->addPane($cat['nom'].' (<span id="checked_'.$cat['id'].'">'.$nb_noti_checked.'</span>/'.$nb_noti.')', $widget);
}

$row = new Row($tabs);
echo $row;


?>