<?php session_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$widget = new WidgetBox('Tableau des modules du compte courant');

$form = new Form('acces');

$tableau = new HtmlTable();
$tableau->addTSection('thead');
$tableau->addRow();
$tableau->addCell('Nom de la société', '', 'thead');
$tableau->addCell('Accès', '', 'thead');
$tableau->addCell('Prix', '', 'thead');
$tableau->addCell('Alias', '', 'thead');
$tableau->addCell('Nom propre', '', 'thead');
$tableau->addTSection('tbody');
$i=0;
foreach ($tab_modules AS $module)
{
    $tableau->addRow();
    $tableau->addCell($module['nom']);
    $radio1 = new Radio('acces_'.$module['nom'], 0);
    $radio2 = new Radio('acces_'.$module['nom'], 1);
    $radio3 = new Radio('acces_'.$module['nom'], 2);
    if($module['type'] == 0)
        $radio1->checked();
    if($module['type'] == 1)
        $radio2->checked();
    if($module['type'] == 2)
        $radio3->checked();
    $tableau->addCell($radio1.' Désactivé<br />'.$radio2.' Activé<br />'.$radio3.' Activable');
    $prix = New Text("prix_".$module['nom']);
    $prix->setValue($module['prix']);
    $tableau->addCell($prix);
    $alias = new Text('alias_'.$module['nom']);
    $alias->setValue($module['alias']);
    $tableau->addCell($alias);
    $nom_propre = new Text('nom_propre_'.$module['nom']);
    $nom_propre->setValue($module['nom_propre']);
    $tableau->addCell($nom_propre);

    $i++;
    if($i == 10)
    {
        $tableau->addRow();
        $tableau->addCell('Nom de la société', '', 'thead');
        $tableau->addCell('Accès', '', 'thead');
        $tableau->addCell('Prix', '', 'thead');
        $tableau->addCell('Alias', '', 'thead');
        $tableau->addCell('Nom propre', '', 'thead');
        $i=0;
    }
}
$hidden = new Hidden('send');
$hidden->setValue('1');

$bouton = new Button('Valider ces changements', 'javascript:void();');
$bouton->onClick("$('#acces').submit();");
$bouton->setClasse('btn-primary');

$form->setContent($tableau.$hidden.$bouton);

$widget->setContent($form);

$row = new Row($widget);
echo $row;

?>