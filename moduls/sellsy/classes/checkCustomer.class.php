<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 03/07/2017
 * Time: 19:25
 */
class checkCustomer
{
    private $text;
    private $modal_compare = '';

    public function __construct($id_client)
    {

        $page = str_replace('/', '', $_SERVER['REQUEST_URI']);

        $client = new GetInfosCustomer($id_client);
        if($client->getIdSellsy())
        {

            $request = array(
                'method' => 'Client.getOne',
                'params' => array(
                    'clientid'  =>  $client->getIdSellsy()
                )
            );

            $response = json_encode(sellsyConnect::load()->requestApi($request));
            $parsed_json = json_decode($response);

            $retour = (array) $parsed_json->response->client;
            $client_sellsy = (array) $parsed_json->response;
            //print_r($parsed_json->response);

      //      print_r($parsed_json);
    //echo 'ici'.$parsed_json->error->message;


            $unlink = '<a href="./sellsy?action=d&id_c='.$id_client.'&redirect='.$page.'" title="Désassocier le client Sellsy">'.new Font('chain-broken').'</a> ';
            //$this->text = $unlink;

            if($parsed_json->error->message == 'Object third not loadable')
            {
                $widget = new WidgetBox($unlink.' Client associé à une entité introuvable sur Sellsy');
                $widget->setBoxColor('red-pink');
                $widget->forceBox();
                $widget->setContent('');
                $widget->setCollapse();
                $widget->Collapsed();

                $this->text = (string) $widget;
               // $this->text .= 'Client associé à une entité introuvable sur Sellsy';
            }
            else
            {

                $same = $this->compare($client, $client_sellsy);

                if(!$same)
                    $add = '<a href="'.$this->modal_compare->getHref().'">'.new Font('refresh').'</a>';// $this->modal_compare->getModalOpen();

                $widget = new WidgetBox($unlink.' Client associé à <a href="'.$client->getLinkSellsy().'" target="_blank" title="Ouvrir la fiche client de '.$retour['name'].' sur Sellsy (dans un nouvel onglet)">' .$retour['name'].'</a> sur Sellsy '.$add);
                $widget->setCollapse();
                $widget->Collapsed();
                if($same)
                    $widget->setBoxColor('green-jungle');
                else
                    $widget->setBoxColor('yellow-casablanca');
                $widget->forceBox();
                $widget->setContent('');

                $this->text = (string) $widget;

                if(!$same)
                    $this->text .= (string) $this->modal_compare->getModalHtml();

            }
                //$this->text .= ;



        }
        else
        {
            if($client->getPropart() == 1)
                $type = 'corporation';
            else
                $type = 'person';
            $request = array(
                'method' => 'Client.getList',
                'params' => array (
                    'order' => array(
                        'direction' => 'ASC'
                    ),
                    'pagination' => array (
                        'pagenum'   => 1,
                        'nbperpage' => 5000
                    ),
                    'search' => array(
                        'name'         	=> $client->GetNom(),
                        'contains'      => $client->GetPrenom(),
                        'type'          => $type,
                        'actif'        => 'Y'
                    )
                )
            );
            $response = json_encode(sellsyConnect::load()->requestApi($request));
            $parsed_json = json_decode($response);

            $retour = (array) $parsed_json->response->result;

            $liste = new Select('liste_sellsy');
            $nb = 0;
            foreach ($retour AS $res)
            {
                $re = (array) $res;
                $liste->addOption($re['thirdid'], $re['name']);
                $nb++;
            }

            $button = new Button('Associer ce client', 'javascript:void();', 'Associer ce client Sellsy à Managy');
            $button->setClasse('btn-primary btn-xs');
            $button->onClick("$(location).attr('href', '/sellsy?action=a&id_c=".$id_client."&id_s='+$('#liste_sellsy').val()+'&redirect=".$page."')");

            $create = new Button('Créer le client', 'Javascript:void();', 'Créer le client sur Sellsy');
            $create->setClasse('btn-success btn-xs');
            $create->onClick("$(location).attr('href', '/sellsy?action=c&id_c=".$id_client."&redirect=".$page."')");



            if($nb) {
                $widget = new WidgetBox('Liste des clients Sellsy trouvés ('.$nb.')');
                $widget->setCollapse();
                //$widget->Collapsed();
                $widget->setBoxColor('yellow-casablanca');
                $widget->forceBox();
                $widget->setContent($liste . $button . ' ' . $create);
            }
            else
            {
                $widget = new WidgetBox('Aucun client sellsy trouvé');
                $widget->setCollapse();
                //$widget->Collapsed();
                $widget->setBoxColor('red-pink');
                $widget->forceBox();
                $widget->setContent($create);
            }

            //print_r($parsed_json);
            $this->text = (string) $widget;

        }

    }

    private function compare($client, $client_sellsy)
    {
        $same = true;





        $this->modal_compare = new Modal('Comparaison Managy / Sellsy', 'compare_'.$client->getId());
        $this->modal_compare->setWidth("70%");
        $this->modal_compare->openLink(new Font('refresh'));

        $this->modal_compare = new modalAjax('sellsy', 'compare');
        $settings = array(
            'id_client'     => $client->getId(),
            'id_sellsy'     => $client->getIdSellsy()
        );

        $this->modal_compare->settings($settings);
        //$this->modal_compare->setDebug();





        if($client->GetPropart() == 1) //pro
        {


            $retour = (array) $client_sellsy['corporation'];
            //print_r($retour);

            $fields = '';



            if($retour['name'] != $client->getNom())
                $same = false;

            if($retour['email'] != $client->getMail())
                $same = false;



            $fixe_compare = str_replace('(0)', '', str_replace(' ', '', $client->getFixe()));
            if($retour['tel'] != $fixe_compare)
                $same = false;


            $mobile_compare = str_replace('(0)', '', str_replace(' ', '', $client->getPort()));
            if(trim($retour['mobile']) != trim($mobile_compare))
                $same = false;


            $request = array(
                'method' => 'Client.getAddress',
                'params' => array (
                    'clientid' =>  $client->getIdSellsy(),
                    'addressid' =>  $retour['mainaddressid']
                )
            );
            $response = json_encode(sellsyConnect::load()->requestApi($request));
            $parsed_json = json_decode($response);
            $retour = (array) $parsed_json->response;


            if($retour['part1'] != $client->GetAdresse())
                $same = false;



            if($retour['part2'] != $client->GetAdresseSuite())
                $same = false;

            if($retour['zip'] != $client->GetCp())
                $same = false;


            if($retour['town'] != $client->GetVille())
                $same = false;



        }
        else //particulier
        {

            $retour = (array) $client_sellsy['contact'];



            $fields = '';
            if(($retour['civil'] == 'man' AND $client->GetTitre() != 'M') OR ($retour['civil'] == 'woman' AND $client->GetTitre() != 'Mme') OR ($retour['civil'] == 'lady' AND $client->GetTitre() != 'Mlle'))
                $same = false;

            if($retour['civil'] == 'man')
                $titre = 'M';
            if($retour['civil'] == 'woman')
                $titre = 'Mme';
            if($retour['civil'] == 'lady')
                $titre = 'Mlle';


            if($retour['forename'] != $client->getPrenom())
                $same = false;

            if($retour['name'] != $client->getNom())
                $same = false;

            if($retour['email'] != $client->getMail())
                $same = false;



            $fixe_compare = str_replace('(0)', '', str_replace(' ', '', $client->getFixe()));

            if($retour['tel'] != $fixe_compare)
                $same = false;

            $mobile_compare = str_replace('(0)', '', str_replace(' ', '', $client->getPort()));

            if(trim($retour['mobile']) != trim($mobile_compare))
                $same = false;



            $retour = (array) $client_sellsy['address'][$retour['mainAddressID']];


            if($retour['part1'] != $client->GetAdresse())
                $same = false;


            if($retour['part2'] != $client->GetAdresseSuite())
                $same = false;


            if($retour['zip'] != $client->GetCp())
                $same = false;


            if($retour['town'] != $client->GetVille())
                $same = false;

        }



        return $same;
    }

    public function __toString()
    {
        $row = new Row($this->text);
        return (string) $row;
    }
}