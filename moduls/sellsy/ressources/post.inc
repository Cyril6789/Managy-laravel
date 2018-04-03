<?php session_start();
 //GESTION DES POSTS


if(!empty($_POST['client_m']) AND !(empty($_POST['client_s'])))
{
    $fi = explode("#", $_POST['fields']);
    //print_r($fi);
    //print_r($_POST);


    $request = array(
        'method' => 'Client.getOne',
        'params' => array(
            'clientid'  =>  $_POST['client_s']
        )
    );

    $response = json_encode(sellsyConnect::load()->requestApi($request));
    $parsed_json = json_decode($response);

    $retour = (array) $parsed_json->response;

    $c_managy = new DataObject('clients');
    $c_managy->find($_POST['client_m']);

   // echo $c_managy->pro_part;

    if($c_managy->pro_part != 1)
        $type = 'contact';
    else
        $type = 'corporation';

    //print_r($retour);
    //echo $type;

    $contact = (array) $retour[$type];

    //print_r($contact);


   if($type == 'contact') {
       $adresse_id = $retour['corporation']->mainaddressid;
   }
   else
       $adresse_id = $contact['mainaddressid'];

   echo $adresse_id;

    $request = array(
        'method' => 'Client.getAddress',
        'params' => array (
            'clientid' =>  $_POST['client_s'],
            'addressid' =>  $adresse_id
        )
    );
    $response = json_encode(sellsyConnect::load()->requestApi($request));
    $parsed_json = json_decode($response);
    $adresse = (array) $parsed_json->response;
    //print_r($adresse);



    //die();

    foreach ($fi As $f) // update managy
    {
        if(!empty($f))
        {
            if($_POST[$f] == 'on')
            {
                switch ($f)
                {
                    case 'change_civil':
                        if($contact['civil'] == 'man')
                            $titre = "M";
                        if($contact['civil'] == 'woman')
                            $titre = "Mme";
                        if($contact['civil'] == 'lady')
                            $titre = "Mlle";
                        if($c_managy->type_pro_part != 1)
                            $c_managy->titre = $titre;
                        break;
                    case 'change_prenom':
                        if($c_managy->type_pro_part != 1)
                            $c_managy->prenom = $contact['forename'];
                        break;

                    case 'change_nom':
                        $c_managy->nom = $contact['name'];
                        break;
                    case 'change_mail':
                        $c_managy->mail = $contact['email'];
                        break;
                    case 'change_fixe':
                        $tfixe = $contact['formatted_tel'];
                        $tfixe[0] = '';
                        $fixe = '+33 (0)'.trim($tfixe);
                        $c_managy->fixe = $fixe;
                        break;
                    case 'change_mobile':
                        $tmobile = $contact['formatted_mobile'];
                        $tmobile[0] = '';
                        $mobile = '+33 (0)'.trim($tmobile);
                        echo $mobile;
                        $c_managy->portable = $mobile;
                        break;
                    case 'change_adresse':
                        $c_managy->adresse = trim($adresse['part1']);
                        break;
                    case 'change_adressesuite':
                        $c_managy->adresse_suite = trim($adresse['part2']);
                        break;
                    case 'change_cp':
                        $c_managy->cp = $adresse['zip'];
                        break;
                    case 'change_ville':
                        $c_managy->ville = trim($adresse['town']);
                        break;
                }
                //echo 'update managy :'.$f.'<br />';

            }

        }
    }

    $c_managy->update();

    $params = Array();
    $params['clientid'] = $_POST['client_s'];
    $adress = Array();

    foreach ($fi As $f) // update sellsy
    {
        if(!empty($f))
        {
            if($_POST[$f] != 'on') {
                switch ($f)
                {
                    case 'change_prenom':
                        if($type == 'contact')
                            $params[$type]['forename'] = trim($c_managy->prenom);
                        break;
                    case 'change_mail':
                        $params[$type]['email'] = $c_managy->mail;
                        break;
                    case 'change_fixe':
                        $params[$type]['tel'] =  str_replace('+33 (0)', '0', $c_managy->fixe);
                        break;
                    case 'change_mobile':
                        $params[$type]['mobile'] =  str_replace('+33 (0)', '0', $c_managy->portable);
                        break;
                    case 'change_adresse':
                        $adress['part1'] = trim($c_managy->adresse);
                        break;
                    case 'change_adressesuite':
                        $adress['part2'] = trim($c_managy->adresse_suite);
                        break;
                    case 'change_cp':
                        $adress['zip'] = $c_managy->cp;
                        break;
                    case 'change_ville':
                        $adress['town'] = trim($c_managy->ville);
                        break;
                }
               //echo 'update sellsy :' . $f . '<br />';
            }
        }
    }

    $params['third']['name'] = $c_managy->nom.' '.$c_managy->prenom;
    $params['third']['email'] = $c_managy->mail;
    $params['third']['tel'] = $c_managy->fixe;
    $params['third']['mobile'] = $c_managy->portable;
    $params[$type]['name'] = trim($c_managy->nom);
    if($c_managy->titre == 'M')
        $titre = "man";
    if($c_managy->titre == 'Mme')
        $titre = "woman";
    if($c_managy->titre == 'Mlle')
        $titre = "lady";
    if($type == 'contact')
        $params['contact']['civil'] = $titre;

    $request = array(
        'method' => 'Client.update',
        'params' => $params
    );
    $response = json_encode(sellsyConnect::load()->requestApi($request));
    //print_r($response);
    //die();
    $adress['id'] = $adresse['id'];
    $adress['name'] = $adresse['name'];

    $request = array(
        'method' => 'Addresses.update',
        'params' => $adress
    );
    $response = json_encode(sellsyConnect::load()->requestApi($request));

//die();
   //echo $_SERVER['REQUEST_URI'] ;
   header('location: .'.$_SERVER['REQUEST_URI']);


    die();

}

  //  die();
?>