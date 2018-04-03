<?php session_start();
if(!is_numeric($id_inter))
{
    if(is_numeric($_GET['id_inter']) AND $_GET['id_inter'] > 0)
    {
        $id_inter = (int) $_GET['id_inter'];
    }
    else
        $id_inter = 0;

}
$page_title = str_replace('%num%', $id_inter, $page_title);
?>