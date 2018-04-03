<?php session_start();
if(!is_array($sidebar['customers']))
    $sidebar['customers'] = Array();
$sidebar['customers']['first'] = 'user|'.CUSTOMERS_SB_CUSTOMERS;
$sidebar['customers'][] = 'customers|1|'.CUSTOMERS_SB_CUSTOMERS_LIST.'|customers';
?>
