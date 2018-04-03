<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 23/02/2017
 * Time: 08:22
 */


if(!AccesActivableModul('sms') AND !AccesActivableModul('mail'))
{
    $danger = new Danger('Au moins l\'un des deux modules suivants doit être activé pour utiliser les automatismes. Module "SMS" ou module "Mail". Consultez la liste des modules complémentaires, ou notre FAQ pour plus d\'informations.', false);
    echo $danger;
}
else {
    ?>
    <script>
        function planned_chk(id='') {
            if ($('input[id=planned' + id + ']').is(':checked')) {
                $('#on_action' + id).hide('slow');
                $('#day' + id).show('slow');
                $('#heure_fixe' + id).show('slow');
                if ($('input[id=heurefixe' + id+']').is(':checked')) {
                    $('#hour' + id).hide('slow');
                    $('#minutes' + id).hide('slow');
                    $('#timer' + id).show('slow');
                }
                else {
                    $('#hour' + id).show('slow');
                    $('#minutes' + id).show('slow');
                    $('#timer' + id).hide('slow');
                }

            }
            else {
                $('#on_action' + id).show('slow');
                $('#day' + id).hide('slow');
                $('#heure_fixe' + id).hide('slow');
                $('#hour' + id).hide('slow');
                $('#minutes' + id).hide('slow');
                $('#timer' + id).hide('slow');
            }
        }

        function heurefixecoche(id='') {
            if ($('input[id=heurefixe' + id + ']').is(':checked')) {
                $('#hour' + id).hide('slow');
                $('#minutes' + id).hide('slow');
                $('#timer' + id).show('slow');
            }
            else {
                $('#hour' + id).show('slow');
                $('#minutes' + id).show('slow');
                $('#timer' + id).hide('slow');
            }
        }

        function delete_auto(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette action automatique ?'))
                document.location.href = '?suppr=' + id;
        }
    </script>

    <?php

    $add_auto_modal = new modalAjax('automatismes', 'add');

    $btn_add = new Button(new Font('plus'), $add_auto_modal->getHref());
    $btn_add->setClasse('btn-primary');


    $widget_auto = new WidgetBox('Liste des automatismes');
    $widget_auto->addToolbarButtons($btn_add);

    $tableau = new HtmlTable('', 'table table-bordered table-hover table-responsive datatable');
    $tableau->addTSection('thead');
    $tableau->addRow();
    $tableau->addCell('Intervention', '', 'thead');
    $tableau->addCell('Evénement', '', 'thead');
    $tableau->addCell('Titre de la notification', '', 'thead');
    $tableau->addCell('Destinataire', '', 'thead');
    $tableau->addCell('Déclenchement', '', 'thead');
    $tableau->addCell('Actions', '', 'thead', Array('style' => 'width:5%;'));
    $tableau->addTSection('tbody');

    $tab_modal_edit = Array();
    foreach ($tab_auto AS $auto) {
        $tableau->addRow();
        if ($auto['type_atelier_rdv'] == 1)
            $tableau->addCell('Atelier');
        else
            $tableau->addCell('Sur site');

        $creation_affiche = '';
        switch ($auto['action']) {
            case 'creation':
                $action_affiche = 'Création de l\'intervention';
                break;
            case 'cloture':
                $action_affiche = 'Clôture de l\'intervention';
                break;
            case 'sauvegarde':
                $action_affiche = 'Enregistrement de modifications';
                break;
            case 'pec':
                $action_affiche = 'Un technicien prend en charge l\'intervention';
                break;
            case 'commande':
                $action_affiche = 'Une commande est passée';
                break;
            case 'rcommande':
                $action_affiche = 'Reception de la commande';
                break;
            case 'stt':
                $action_affiche = 'Envoie en sous-traitance';
                break;
            case 'rstt':
                $action_affiche = 'Retour de la sous-traitance';
                break;
            case 'heure':
                $action_affiche = 'Heure du rendez-vous (si intervention sur site)';
                break;
            case 'change_rdv':
                $action_affiche = 'Décalage d\'un rendez-vous sur site';
                break;
            default:
                $action_affiche = 'erreur';
                break;
        }
        $tableau->addCell($action_affiche);

        if ($auto['id_sms'] > 0)
            $tableau->addCell('SMS : ' . $auto['nom_sms']);

        if ($auto['id_mail'] > 0)
            $tableau->addCell('Mail : ' . $auto['nom_mail']);

        if($auto['dest'] == '1')
            $tableau->addCell('Client');
        if($auto['dest'] == '2')
            $tableau->addCell('Technicien en charge de l\'intervention');

        if ($auto['immediat'])
            $tableau->addCell('À l\'entrée dans l\'événement');
        else {
            $plus = '';
            if ($auto['j'] > 0)
                $plus = '+';
            $cellule = 'J' . $plus . $auto['j'];
            if ($auto['heurefixe'])
                $cellule .= ' à ' . $auto['htime'] . 'h' . $auto['mtime'];
            else {
                $plus_h = '';
                if ($auto['h'] > 0)
                    $plus_h = '+';
                if ($auto['m'] > 0)
                    $plus_m = '+';
                $cellule .= ', H' . $plus_h . $auto['h'] . ', M' . $plus_m . $auto['m'];

                $time = ($auto['j'] * 60 * 60 * 24) + ($auto['h'] * 60 * 60) + ($auto['m'] * 60);
            }

            $tableau->addCell($cellule);
        }

        $dropdown_action = New DropdownButton();

        $edit_auto_modal = new modalAjax('automatismes', 'edit', $auto['id']);

        $edit_auto_modal->settings(array('id_auto' => $auto['id']));
        //$edit_auto_modal->setDebug();


        $dropdown_action->addSubButton(new Font('pencil').' Modifier', $edit_auto_modal->getHref());
        $dropdown_action->addSubButton(new Font('remove').' Supprimer', 'javascript:void();', 'delete_auto(' . $auto['id'] . ');');

        $tab_modal_edit[] = $edit_auto_modal->getModalHtml();
        $tableau->addCell($dropdown_action);
    }

    $widget_auto->setContent($tableau, false);

    $row = new Row($widget_auto);
    echo $row;

    echo $add_auto_modal->getModalHtml();
    foreach ($tab_modal_edit As $mod)
        echo $mod;


}

?>