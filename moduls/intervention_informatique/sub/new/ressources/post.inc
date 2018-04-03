<?php
 //GESTION DES POSTS

if(!empty($_POST['mail_ask_mail'])) {
    if(right('customers', 2)) {
        $client = new DataObject('clients');
        $client->find($_GET['id_customer']);
        $client->mail = $_POST['mail_ask_mail'];
        $client->update();
    }

    header('location: ./intervention-new-customer-'.$_GET['id_customer']);
    die();
}
?>