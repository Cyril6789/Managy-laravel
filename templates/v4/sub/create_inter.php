<?php session_start();

$form = new FormLayout('Spécificités de l\'intervention', 12, false);
$form->setFormControls('form_create', './intervention-create');
$form->setValueButton('Créer l\'intervention');
$form->setWidth(2, 10);

$id_customer = new Hidden('id_client');
$id_customer->setValue($_GET['id_customer']);
$projet = new Text('projet');
$form->addLine('Projet', $id_customer.$projet);

$couleurs = new Select('id_couleur');
$couleurs->withSearch();
$couleurs->addOption('0', '--');
foreach ($tab_colors AS $value_color)
    $couleurs->addOption($value_color['id'], $value_color['name'].' (Ral : '.$value_color['ral'].')');
$form->addLine('Couleur', $couleurs);

$aspects = new Select('id_finition');
$aspects->withSearch();
$aspects->addOption('0', '--');
foreach ($tab_aspects AS $value_aspect)
    $aspects->addOption($value_aspect['id'], $value_aspect['name']);
$form->addLine('Aspect', $aspects);

$ref_chantier = new Text('ref_chantier');
$form->addLine('Référence chantier', $ref_chantier);

$matieres = new Select('id_matiere');
$matieres->withSearch();
$matieres->addOption('0', '--');
foreach ($tab_matieres AS $matiere)
    $matieres->addOption($matiere['id'], $matiere['name']);
$form->addLine('Matière', $matieres);

$forfait = new CheckBox('forfait');
$forfait->onChange("$('.no_forfait').toggle('slow');");
$form->addLine('Forfait', $forfait);

foreach ($tab_traitements AS $value_traitements)
{
    $check_trait = new CheckBox('t'.$value_traitements['id']);
    $form->addLine($value_traitements['name'], $check_trait, false, '', '', 'no_forfait');
}

foreach ($tab_steps AS $value_steps)
{
    $check_steps = new CheckBox('s'.$value_steps['id']);
    $form->addLine($value_steps['name'], $check_steps, false, '', '', 'no_forfait');
}

$estimatif = new Spinner('temps');
$estimatif->setStep(15);
$estimatif->Time();
$estimatif->setValue('00:00');
$form->addLine('Estimatif temps', $estimatif, false, '', '', 'no_forfait');


$form = new Row($form);
echo $form;
?>