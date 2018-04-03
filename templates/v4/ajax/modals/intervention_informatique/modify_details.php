<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/09/2017
 * Time: 18:44
 */
$modal = new jsonModal('Modifier les détails de l\'intervention');
$modal->form_id('modify_details');
$modal->width('45%');

if(!empty($_POST['id_inter']))
{
    $inter = new DataObject('interventions');
    $inter->find($_POST['id_inter'], 'id_inter');
    if($inter->id)
    {
        if($inter->type_atelier_rdv)
        {
            if (!$inter->time_cloture)
            {
                $db = new MySQL();
                $db->Query('SELECT COUNT(*) AS nb FROM prise_en_charge WHERE id_intervention = "'.$inter->id_inter.'" AND id_staff = "'.$_SESSION['id'].'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                $row = $db->Row();
                $pris_en_charge = $row->nb; //true; //false;

                if($pris_en_charge) {

                    if (right('inter', 13)) {
                        $form_details = new FormLayout('Saisie');
                        $form_details->setFormControls('modify_details');
                        $form_details->setWidth(5, 7);

                        $matosf = new Select('id_materiel', 'id_materiel');
                        $matosf->withSearch();

                        $materiels = new DataObject('materiels');
                        $tab_materiels = $materiels->findAll();

                        foreach ($tab_materiels AS $materiel) {
                            if ($materiel->id == $inter->id_materiel)
                                $selected = true;
                            else
                                $selected = false;
                            $matosf->addOption($materiel->id, stripslashes($materiel->nom), $selected);
                        }
                        $form_details->addLine('Matériel', $matosf);

                        $codesf = new Text('mdp', 'mdp');
                        $codesf->setValue($inter->mdp);
                        $form_details->addLine("Mot de passe, codes<br /><a href=\"javascript:void();\" onclick=\"$('#div_pattern').toggle('slow');\">Schéma de dévérouillage</a>", $codesf);

                        $pattern_edit = new Pattern('pattern_edit', 'pattern_edit_value', $inter->pattern);

                        if ($inter->pattern)
                            $display = "";
                        else
                            $display = "display: none;";
                        $form_details->addLine('', $pattern_edit, false, 'div_pattern', $display);


                        $ses = new Select('id_os', 'id_os');
                        $ses->withSearch();
                        $ses->addOption('0', '--');

                        $sess = new DataObject('se');
                        $tab_ses = $sess->findAll();

                        foreach ($tab_ses AS $se) {
                            if ($se->id == $inter->id_se)
                                $selected = true;
                            else
                                $selected = false;
                            $ses->addOption($se->id, stripslashes($se->nom), $selected);
                        }
                        $form_details->addLine('Système d\'exploitation', $ses);


                        $avf = new Select('id_antivirus', 'id_antivirus');
                        $avf->withSearch();
                        $avf->addOption('0', '--');

                        $antivirus = new DataObject('antivirus');
                        $tab_antivirus = $antivirus->findAll();

                        foreach ($tab_antivirus AS $antivirus) {
                            if ($antivirus->id == $inter->id_antivirus)
                                $selected = true;
                            else
                                $selected = false;
                            $avf->addOption($antivirus->id, stripslashes($antivirus->nom), $selected);
                        }
                        $form_details->addLine('Antivirus', $avf);

                        $garantief = new CheckBox('garantie', 'garantie');
                        if ($inter->garantie)
                            $garantief->checked();
                        $form_details->addLine('Prise en charge sous garantie', $garantief);

                        $urgentef = new CheckBox('urgente', 'urgente');
                        if ($inter->urgente)
                            $urgentef->checked();
                        $form_details->addLine('Urgente', $urgentef);

                        $tarif = new Text('tarif');
                        $tarif->setValue($inter->tarif_estimatif);
                        $form_details->addLine('Tarif estimatif (€)', $tarif);


                        $content = $form_details;
                    } else {
                        $content = new Danger('Vous n\'avez pas le droit de modifier les détails de cette intervention', false);
                        $modal->error();
                    }
                }
                else
                {
                    $content = new Danger('Vous ne pouvez pas modifier les détails de cette interventions car vous ne l\'a prenez pas en charge.', false);
                    $modal->error();
                }
            }
            else
                {
                $content = new Danger('Cette intervention est clôturée. Vous ne pouvez plus la modifier', false);
                $modal->error();
            }
        }
        else
        {
            $content = new Danger('Cette intervention n\'est pas modifiable', false);
            $modal->error();
        }
    }
    else
    {
        $content = new Danger('Cette intervention n\'existe pas', false);
        $modal->error();
    }
}
else
{
    $content = new Danger('Aucune intervention renseignée', false);
    $modal->error();
}

$modal->content($content);

echo $modal;