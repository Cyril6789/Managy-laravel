<?php session_start();
if(!is_array($sidebar['updates']))
    $sidebar['updates'] = Array();
$sidebar['updates']['first'] = 'arrow-circle-double|'.UPDATES_SB_UPDATES;

global $db;
$db->Query('SELECT COUNT(*) AS new_maj FROM maj WHERE NOT EXISTS (SELECT NULL FROM vu_maj WHERE vu_maj.id_maj = maj.id AND vu_maj.id_staff="'.$_SESSION['id'].'")');
$row = $db->Row();

$sidebar['updates'][] = 'updates|1|'.UPDATES_SB_SEE_UPDATES.'|updates|'.$row->new_maj;
$sidebar['updates'][] = 'updates|-1|'.UPDATES_SB_LIST_UPDATES.'|updates-list';

?>
