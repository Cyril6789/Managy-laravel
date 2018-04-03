<?php session_start();
//GESTION DES GETS

function parse_tel($tel)
{
    return str_replace('+33 (0)', '0', $tel);
}



/*
 * Diagnostic
 */
if($_GET['action'] == 'diag' AND is_numeric($_GET['id_p']) )
{
    $db = new MySQL();
    $id_diag_sellsy = $db->SQLFix($_GET['id_p']);


    $sql = 'UPDATE comptes_principaux SET id_diag_sellsy="'.$id_diag_sellsy.'" WHERE id="'.$_SESSION['compte_principal'].'" ';
    $db->Query($sql);


    header('location: ./sellsy');

    //echo $sql;
    die();
}


/*
 * Associer
 */
if($_GET['action'] == 'a' AND is_numeric($_GET['id_c']) AND is_numeric($_GET['id_s'])) //Association d'un client sellsy à managy
{
    $db = new MySQL();

    $id_sellsy = $db->SQLFix($_GET['id_s']);
    $id_managy = $db->SQLFix($_GET['id_c']);

    $sql = 'UPDATE clients SET id_sellsy="'.$id_sellsy.'" WHERE id="'.$id_managy.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ';
    $db->Query($sql);

    if(!empty($_GET['redirect']))
        header('location: ./'.$_GET['redirect']);
    else
        header('location: ./c'.$id_managy);

    //echo $sql;
    die();
}



/*
 * Désassocier
 */
if($_GET['action'] == 'd' AND is_numeric($_GET['id_c']) )
{
    $db = new MySQL();
    $id_managy = $db->SQLFix($_GET['id_c']);


    $sql = 'UPDATE clients SET id_sellsy="0" WHERE id="'.$id_managy.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ';
    $db->Query($sql);

    if(!empty($_GET['redirect']))
        header('location: ./'.$_GET['redirect']);
    else
        header('location: ./c'.$id_managy);

    //echo $sql;
    die();
}

/*
 * Créer
 */
if($_GET['action'] == 'c' AND is_numeric($_GET['id_c']) )
{
    $db = new MySQL();

    $id_managy = $db->SQLFix($_GET['id_c']);

    $client = new GetInfosCustomer($id_managy);

    if($client->getPropart() == '1') {
        $request = array(
            'method' => 'Client.create',
            'params' => array(
                'third' => array(
                    'name' => $client->getFullName(),
                    'type' => 'corporation',
                    'email' => $client->GetMail(),
                    'tel' => parse_tel($client->getFixe()),
                    'mobile' =>parse_tel($client->getPort())
                ),
                'address' => array(
                    'name' => 'Bureau',
                    'part1' => $client->GetAdresse(),
                    'part2' => $client->GetAdresseSuite(),
                    'zip' => $client->getCp(),
                    'town' => $client->getVille()
                )
            )
        );
    }
    else
    {
        if($client->GetTitre() == 'M')
            $civil = 'man';
        if($client->GetTitre() == 'Mme')
            $civil = 'woman';
        if($client->GetTitre() == 'Mlle')
            $civil = 'lady';

        $request = array(
            'method' => 'Client.create',
            'params' => array(
                'third' => array(
                    'name' => $client->GetNom().' '.$client->GetPrenom(),
                    'type' => 'person',
                    'email' => $client->GetMail(),
                    'tel' => parse_tel($client->getFixe()),
                    'mobile' => parse_tel($client->getPort())
                ),
                'contact' => array(
                    'name' => $client->GetNom(),
                    'forename' => $client->GetPrenom(),
                    'email' => $client->GetMail(),
                    'tel' => parse_tel($client->getFixe()),
                    'mobile' => parse_tel($client->getPort()),
                    'civil' => $civil
                ),
                'address' => array(
                    'name' => 'Adresse 1',
                    'part1' => $client->GetAdresse(),
                    'part2' => $client->GetAdresseSuite(),
                    'zip' => $client->getCp(),
                    'town' => $client->getVille()
                )
            )
        );
    }

    $response = json_encode(sellsyConnect::load()->requestApi($request));
    $parsed_json = json_decode($response);
    //print_r($response);
    $id_sellsy = $db->SQLFix($parsed_json->response->client_id);




    $sql = 'UPDATE clients SET id_sellsy="'.$id_sellsy.'" WHERE id="'.$id_managy.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ';
    $db->Query($sql);

    if(!empty($_GET['redirect']))
        header('location: ./'.$_GET['redirect']);
    else
        header('location: ./c'.$id_managy);

    //echo $sql;
    die();
}
?>