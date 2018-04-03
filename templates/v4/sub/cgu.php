<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 06/02/2017
 * Time: 13:39
 */

$form = new Form('cgu_form');


$textarea = new Textarea('cgu');
$textarea->Wysiwyg();


$textarea->setValue($texte);
$textarea->setRows(20);

$save = new Button('Enregistrer', 'javascript:void();', 'Valider ces CGU');
$save->setClasse('btn-primary');
$save->onClick('cgu_form.submit();');

$form->setContent($textarea.$save);


echo $form;
?>