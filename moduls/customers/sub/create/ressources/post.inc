<?php session_start();
 global $db;
 if(!empty($_POST['customer_name']) OR !empty($_POST['customer_fname']))
 {
     if($_POST['type'] == 'pro')
     {
        $name = $db->SQLfix($_POST['customer_name']);
        $lname = '';
        $titre = 'Sté';
        $type= 1;
     }
     else
     {
        $name = $db->SQLfix($_POST['customer_fname']);
        $lname = $db->SQLfix($_POST['customer_lname']);
        $titre = $db->SQLfix($_POST['customer_titre']);
        $type = 2;
     }
     $ancien_id = $db->SQLfix($_POST['ancien_id']);
     $mail = $db->SQLfix($_POST['customer_mail']);
     $adress = $db->SQLfix($_POST['customer_adress']);
     $adress_suite = $db->SQLfix($_POST['customer_adress_suite']);
     $cp = str_replace(' ', '', $db->SQLfix($_POST['customer_cp']));
     $city = $db->SQLfix($_POST['customer_city']);
     $fixe = $db->SQLfix($_POST['customer_fixe']);
     $gsm = $db->SQLfix($_POST['customer_gsm']);


    function parse_tel($tel)
    {
        return str_replace('+33 (0)', '0', $tel);
    }



     if(AccesActivableModul('sellsy'))
     {
         $request = array(
             'method' => 'Client.getList',
             'params' => array(
                 'order' => array(
                     'direction'	=> 'ASC'
                 ),
                 'pagination' => array (
                     'pagenum'   => 1,
                     'nbperpage' => 5000
                 ),
                 'search' => array(
                     'contains'          => $lname,
                     'name'              => $name
                 )

             )
         );

         $response = json_encode(sellsyConnect::load()->requestApi($request));


         $parsed_json = json_decode($response);
//var_dump($parsed_json);
         $array  = (array) $parsed_json->response->result;
         $cnt = count($array);

        if(!$cnt) {

            if($_POST['type'] == 'pro') {
                $request = array(
                    'method' => 'Client.create',
                    'params' => array(
                        'third' => array(
                            'name' => $name,
                            'type' => 'corporation',
                            'email' => $mail,
                            'tel' => parse_tel($fixe),
                            'mobile' =>parse_tel($gsm)
                        ),
                        'address' => array(
                            'name' => 'Bureau',
                            'part1' => $adress,
                            'part2' => $adress_suite,
                            'zip' => $cp,
                            'town' => $city
                        )
                    )
                );
            }
            else
            {
                if($titre == 'M')
                    $civil = 'man';
                if($titre == 'Mme')
                    $civil = 'woman';
                if($titre == 'Mlle')
                    $civil = 'lady';

                $request = array(
                    'method' => 'Client.create',
                    'params' => array(
                        'third' => array(
                            'name' => $name.' '.$lname,
                            'type' => 'person',
                            'email' => $mail,
                            'tel' => parse_tel($fixe),
                            'mobile' => parse_tel($gsm)
                        ),
                        'contact' => array(
                            'name' => $name,
                            'forename' => $lname,
                            'email' => $mail,
                            'tel' => parse_tel($fixe),
                            'mobile' => parse_tel($gsm),
                            'civil' => $civil
                        ),
                        'address' => array(
                            'name' => 'Adresse 1',
                            'part1' => stripslashes($adress),
                            'part2' => stripslashes($adress_suite),
                            'zip' => $cp,
                            'town' => $city
                        )
                    )
                );
            }

            $response = json_encode(sellsyConnect::load()->requestApi($request));
            $parsed_json = json_decode($response);
            //print_r($response);
            $id_sellsy = $db->SQLFix($parsed_json->response->client_id);
            //die();

        }

     }



     
     $db->Query('INSERT INTO clients (titre, nom, prenom, pro_part, mail, adresse, adresse_suite, cp, ville, fixe, portable, compte_principal, ancien_id, id_sellsy) Values ("'.$titre.'", "'.$name.'", "'.$lname.'", "'.$type.'", "'.$mail.'", "'.$adress.'","'.$adress_suite.'", "'.$cp.'", "'.$city.'", "'.$fixe.'", "'.$gsm.'", "'.$_SESSION['compte_principal'].'", "'.$ancien_id.'", "'.$id_sellsy.'" )');
     
     $last_id = $db->GetLastInsertID();

     if(!$_SESSION['gerant']) {
         $notif = new notificationMail(8);
         $notif->tab_parse(Array('%client%' => $last_id, '%prenom_staff%' => $_SESSION['prenom']));
         $notif->sendMail();
     }
     
     $log = New addLog('a créé le client');
     $log->setClient($last_id);
     $log->insert();
     echo $last_id;
     if(!$no_redirect)
         header('Location: '.$_POST['redirect_link'].$last_id);
     
 }
 else
    header('Location: '.$_POST['bad_redirect_link'].$last_id); //redirection vers la page création inter ou rdv...
     
?>