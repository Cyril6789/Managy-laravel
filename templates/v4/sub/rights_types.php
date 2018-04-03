<?php session_start();

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 */

$form_right = new FormLayout(RIGHTS_CHECK_FOR_APPLY_RIGHTS);
$form_right->setFormControls('rights');
$form_right->setValueButton('Valider');


foreach ($tab_rights AS $modul => $sub_tab)
{
    $content = '';
    foreach ($sub_tab AS $num => $tab)
    {
        $chkbx = new CheckBox($modul.'_'.$num, $modul.'_'.$num);
        if($tab['checked'])
            $chkbx->checked();
        $chkbx->setValue($modul.'_'.$num);
        $content .= $chkbx.' <label for="'.$modul.'_'.$num.'">'.$tab['value'].'</label><br />';
    }

    $form_right->addLine(RIGHTS_MODUL.' "'.$modul.'"', $content.'<hr />');
}
$ids = new Hidden('id_staff');
$ids->setValue($id_staff);
$apply = new Hidden('apply_rights');
$apply->setValue(1);
$form_right->addLine('', $ids.$apply);

echo $form_right;

?>
