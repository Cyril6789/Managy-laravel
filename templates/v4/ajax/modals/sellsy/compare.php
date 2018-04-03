<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 03/09/2017
 * Time: 14:17
 */



$modal = new jsonModal('Comparer client Managy / Sellsy');
$modal->width("70%");


$client = new GetInfosCustomer($_POST['id_client']);


if($client->getId())
{

    $request = array(
        'method' => 'Client.getOne',
        'params' => array(
            'clientid' => $_POST['id_sellsy']
        )
    );

    $response = json_encode(sellsyConnect::load()->requestApi($request));
    $parsed_json = json_decode($response);

    $retour = (array)$parsed_json->response->client;
    $client_sellsy = (array)$parsed_json->response;


//$client_sellsy = unserialize($_POST['client_sellsy']);

    $table = new HtmlTable();
    $table->addTSection('thead');
    $table->addRow();
    $table->addCell('Managy', '', 'thead', array('width' => '45%'));
    $table->addCell('', '', 'thead', array('width' => '10%', 'style' => 'text-align: center;'));
    $table->addCell('Sellsy', '', 'thead');
    $table->addTSection('body');


    if ($client->GetPropart() == 1) //pro
    {


        $retour = (array)$client_sellsy['corporation'];
        //print_r($retour);

        $fields = '';


        $table->addRow();
        $table->addCell($client->GetNom());
        if ($retour['name'] != $client->getNom()) {
            $button = new Switches('change_nom');
            if (empty($client->GetNom()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_nom';
            $same = false;
        } else {
            if (empty($retour['name'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_nom');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_nom');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['name']);


        $table->addRow();
        $table->addCell($client->GetMail());
        if ($retour['email'] != $client->getMail()) {
            $same = false;
            $button = new Switches('change_mail');
            if (empty($client->GetMail()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_mail';

        } else {
            if (empty($retour['email'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_mail');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_mail');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['email']);

        $fixe_compare = str_replace('(0)', '', str_replace(' ', '', $client->getFixe()));
        $table->addRow();
        $table->addCell(str_replace('+33 (0)', '0', $client->getFixe()));
        if ($retour['tel'] != $fixe_compare) {
            $same = false;
            $button = new Switches('change_fixe');

            if (empty($client->GetFixe()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_fixe';

        } else {
            if (empty($retour['tel'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_fixe');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_fixe');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['formatted_tel']);

        $mobile_compare = str_replace('(0)', '', str_replace(' ', '', $client->getPort()));
        $table->addRow();
        $table->addCell(str_replace('+33 (0)', '0', $client->getPort()));
        //echo $retour['mobile'].'<br />';echo$mobile_compare;
        if (trim($retour['mobile']) != trim($mobile_compare)) {
            $same = false;
            $button = new Switches('change_mobile');
            if (empty($client->GetMail()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_mobile';
        } else {
            if (empty($retour['mobile'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_mobile');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_mobile');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['formatted_mobile']);

//echo $retour['mainaddressid'];


        $request = array(
            'method' => 'Client.getAddress',
            'params' => array(
                'clientid' => $client->getIdSellsy(),
                'addressid' => $retour['mainaddressid']
            )
        );
        $response = json_encode(sellsyConnect::load()->requestApi($request));
        $parsed_json = json_decode($response);
        $retour = (array)$parsed_json->response;


        $table->addRow();
        $table->addCell($client->GetAdresse());
        //echo $retour['part1'].'<br />'.$client->GetAdresse();
        if ($retour['part1'] != $client->GetAdresse()) {
            $same = false;
            $button = new Switches('change_adresse');
            if (empty($client->GetAdresse()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_adresse';
        } else {
            if (empty($retour['part1'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_adresse');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_adresse');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['part1']);

        $table->addRow();
        $table->addCell($client->GetAdresseSuite());
        if ($retour['part2'] != $client->GetAdresseSuite()) {
            $same = false;
            $button = new Switches('change_adressesuite');
            if (empty($client->GetAdresseSuite()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_adressesuite';
        } else {
            if (empty($retour['part2'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_adressesuite');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_adressesuite');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['part2']);

        $table->addRow();
        $table->addCell($client->GetCp());
        if ($retour['zip'] != $client->GetCp()) {
            $same = false;
            $button = new Switches('change_cp');
            if (empty($client->GetCp()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_cp';
        } else {
            if (empty($retour['zip'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_cpe');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_cp');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['zip']);

        $table->addRow();
        $table->addCell($client->GetVille());
        if ($retour['town'] != $client->GetVille()) {
            $same = false;
            $button = new Switches('change_ville');
            if (empty($client->GetVille()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_ville';
        } else {
            if (empty($retour['town'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_ville');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_ville');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['town']);


        //echo $retour['tel'].$fixe_compare;
        //print_r($retour);
    } else //particulier
    {

        $retour = (array)$client_sellsy['contact'];


        $table->addRow();
        $table->addCell($client->GetTitre());
        $fields = '';
        if (($retour['civil'] == 'man' AND $client->GetTitre() != 'M') OR ($retour['civil'] == 'woman' AND $client->GetTitre() != 'Mme') OR ($retour['civil'] == 'lady' AND $client->GetTitre() != 'Mlle')) {
            $same = false;

            $button = new Switches('change_civil');
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_civil';

        } else {
            $button = new Button(new Font('check'), 'javascript:;', '', 'change_civil');
            $button->disable();
            $button->setClasse('green-haze btn-outline btn-circle btn-sm');
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
        }

        if ($retour['civil'] == 'man')
            $titre = 'M';
        if ($retour['civil'] == 'woman')
            $titre = 'Mme';
        if ($retour['civil'] == 'lady')
            $titre = 'Mlle';
        $table->addCell($titre);


        $table->addRow();
        $table->addCell($client->GetPrenom());
        if ($retour['forename'] != $client->getPrenom()) {

            $button = new Switches('change_prenom');
            if (empty($client->GetPrenom()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_prenom';
            $same = false;
        } else {
            if (empty($retour['forename'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_prenom');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_prenom');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['forename']);


        $table->addRow();
        $table->addCell($client->GetNom());
        if ($retour['name'] != $client->getNom()) {
            $button = new Switches('change_nom');
            if (empty($client->GetNom()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_nom';
            $same = false;
        } else {
            if (empty($retour['name'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_nom');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_nom');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['name']);


        $table->addRow();
        $table->addCell($client->GetMail());
        if ($retour['email'] != $client->getMail()) {
            $same = false;
            $button = new Switches('change_mail');
            if (empty($client->GetMail()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_mail';

        } else {
            if (empty($retour['email'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_mail');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_mail');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['email']);

        $fixe_compare = str_replace('(0)', '', str_replace(' ', '', $client->getFixe()));
        $table->addRow();
        $table->addCell(str_replace('+33 (0)', '0', $client->getFixe()));
        if ($retour['tel'] != $fixe_compare) {
            $same = false;
            $button = new Switches('change_fixe');
            if (empty($client->getFixe()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_fixe';

        } else {
            if (empty($retour['tel'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_fixe');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_fixe');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['formatted_tel']);

        $mobile_compare = str_replace('(0)', '', str_replace(' ', '', $client->getPort()));
        $table->addRow();
        $table->addCell(str_replace('+33 (0)', '0', $client->getPort()));
        //echo $retour['mobile'].'<br />';echo$mobile_compare;
        if (trim($retour['mobile']) != trim($mobile_compare)) {
            $same = false;
            $button = new Switches('change_mobile');
            if (empty($client->getPort()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_mobile';
        } else {
            if (empty($retour['mobile'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_mobile');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_mobile');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['formatted_mobile']);


        $retour = (array)$client_sellsy['address'][$retour['mainAddressID']];

        $table->addRow();
        $table->addCell($client->GetAdresse());
        //echo $retour['part1'].'<br />'.$client->GetAdresse();
        if ($retour['part1'] != $client->GetAdresse()) {
            $same = false;
            $button = new Switches('change_adresse');
            if (empty($client->GetAdresse()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_adresse';
        } else {
            if (empty($retour['part1'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_adresse');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_adresse');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['part1']);

        $table->addRow();
        $table->addCell($client->GetAdresseSuite());
        if ($retour['part2'] != $client->GetAdresseSuite()) {
            $same = false;
            $button = new Switches('change_adressesuite');
            if (empty($client->GetAdresseSuite()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_adressesuite';
        } else {
            if (empty($retour['part2'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_adressesuite');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_adressesuite');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['part2']);

        $table->addRow();
        $table->addCell($client->GetCp());
        if ($retour['zip'] != $client->GetCp()) {
            $same = false;
            $button = new Switches('change_cp');
            if (empty($client->GetCp()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_cp';
        } else {
            if (empty($retour['zip'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_cpe');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_cp');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['zip']);

        $table->addRow();
        $table->addCell($client->GetVille());
        if ($retour['town'] != $client->GetVille()) {
            $same = false;
            $button = new Switches('change_ville');
            if (empty($client->GetVille()))
                $button->checked();
            $button->setDataOn('success', new Font('arrow-left'));
            $button->setDataOff('success', new Font('arrow-right'));
            $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            $fields .= '#change_ville';
        } else {
            if (empty($retour['town'])) {
                $button = new Button(new Font('times'), 'javascript:;', '', 'change_ville');
                $button->disable();
                $button->setClasse('red-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            } else {
                $button = new Button(new Font('check'), 'javascript:;', '', 'change_ville');
                $button->disable();
                $button->setClasse('green-haze btn-outline btn-circle btn-sm');
                $table->addCell($button, '', 'tbody', array('style' => 'text-align: center;'));
            }
        }
        $table->addCell($retour['town']);


        //echo $retour['tel'].$fixe_compare;
        //print_r($retour);
    }

    $widget = new WidgetBox('Comparaison des élements du client ' . $client->getFullName());
    $client_m = new Hidden('client_m');
    $client_m->setValue($client->GetId());
    $client_s = new Hidden('client_s');
    $client_s->setValue($client->getIdSellsy());
    $fields_change = new Hidden('fields');
    $fields_change->setValue($fields);
    $widget->setContent('<form action="" method="post" id="form_compare">' . $table . $client_m . $client_s . $fields_change . '</form>');
//$this->modal_compare->setOnclickButton('Appliquer les modifications', '$(\'#form_compare\').submit();');


    $content = $widget;
    $modal->form_id('form_compare');

}
else {
    $modal->error();
    $content = new Danger('Ce client n\'existe pas ou vous n\'avez pas les accès nécessaires pour le voir', false);
}

$modal->content($content);
echo $modal;