<?php session_start();

$sellsy = new DataObject('comptes_principaux');
$sellsy->find($_SESSION['compte_principal'], 'id', false);

if($sellsy->un == '' OR $sellsy->sellsy_oauth_access_token_secret == '' OR $sellsy->sellsy_oauth_consumer_key == '' Or $sellsy->sellsy_oauth_consumer_secret == '')
{
    $warning = new Warning('Tous les éléments ne sont pas renseignés sur le connecteur Sellsy.', false);

    $warning = new Row($warning);
    echo $warning;

}
else
{
    $request = array(
        'method' => 'Infos.getInfos',
        'params' => array()
    );

    $response = json_encode(sellsyConnect::load()->requestApi($request));

    $parsed_json = json_decode($response);

    $array  = (array) $parsed_json->response->consumerdatas;
    $infos = (array) $array['infos'];

    $id = $array['id'];
    if($id)
    {
        $success = new Success('<strong>Félicitations !</strong> Vous êtes connecté à Sellsy en tant que <strong>'.$infos['consumer_i_owner_name'].'</strong>.', false);
        $success = new Row($success);
        echo $success;
        $connexion = true;
    }
    else
    {
        $error = new Danger('Vos identifiants de connexions à Sellsy sont incorrects !', false);
        $error = new Row($error);
        echo $error;
    }

}


$sellsy = new DatabaseWorker('comptes_principaux', false);
$sellsy->setWidget('Connecteur Sellsy <a href="https://www.sellsy.fr/?_f=prefsApi" target="_blank">'.new Font('question-circle').'</a>');
$sellsy->noDatatable();
$sellsy->addWhere('id="' . $_SESSION['compte_principal'] . '"');
$sellsy->displayedFields(Array('sellsy_oauth_consumer_key', 'sellsy_oauth_consumer_secret', 'sellsy_oauth_access_token', 'sellsy_oauth_access_token_secret'));
$sellsy->labelsDisplayedFields(Array('consumer token', 'consumer secret', 'utilisateur token', 'utilisateur secret'));
$sellsy->activeModify('./sellsy', 'update_sellsy');
$row = new Row($sellsy);
echo $row;


if($connexion)
{

    if($id_diag_sellsy)
    {
        $request =  array(
            'method' => 'Catalogue.getOne',
            'params' => array (
                'type'              => 'service',
                'id'                => $id_diag_sellsy,
                )
            );

        $response = json_encode(sellsyConnect::load()->requestApi($request));
        $parsed_json = json_decode($response);

        if($parsed_json->error->message == 'Object item not loadable')
        {
            $request = array(
                'method' => 'Catalogue.getList',
                'params' => array (
                    'type'          => 'service',
                    'order' => array(
                        'direction' => 'ASC',
                        'order'     => 'item_name'
                    ),
                    'pagination' => array (
                        'pagenum'   => 1,
                        'nbperpage' => 1000
                    ),
                    'search' => array(
                        'actif'         	=> 'Y'
                    )
                )
            );

            $response = json_encode(sellsyConnect::load()->requestApi($request));
            $parsed_json = json_decode($response);
            $tab_services = (array) $parsed_json->response->result;
//print_r($tab_services);

            $services = new Select('services');
            foreach ($tab_services AS $service)
            {
                $serv = (array) $service;
                $services->addOption($serv['id'], $serv['name']);
            }
            $services->withSearch();
        }
        else
           $texte = 'Prestation Sellsy "<a href="https://www.sellsy.fr/?_f=catalogueitem&id='.$parsed_json->response->id.'" target="_blank">'.$parsed_json->response->name.'</a>" associée à Managy avec succès';

    }
    else
    {
        $request = array(
            'method' => 'Catalogue.getList',
            'params' => array (
                'type'          => 'service',
                'order' => array(
                    'direction' => 'ASC',
                    'order'     => 'item_name'
                ),
                'pagination' => array (
                    'pagenum'   => 1,
                    'nbperpage' => 1000
                ),
                'search' => array(
                    'actif'         	=> 'Y'
                )
            )
        );

        $response = json_encode(sellsyConnect::load()->requestApi($request));
        $parsed_json = json_decode($response);
        $tab_services = (array) $parsed_json->response->result;
//print_r($tab_services);

        $services = new Select('services');
        foreach ($tab_services AS $service)
        {
            $serv = (array) $service;
            $services->addOption($serv['id'], $serv['name']);
        }
        $services->withSearch();
    }


    $widget_diag = new WidgetBox('Connexion à la prestation "diagnostic" de Sellsy');

    if(is_object($services)) {

        $button = new Button('Associer cette prestation', 'javascript:void();', 'Associer cette prestation Sellsy en tant que diagnostic Managy');
        $button->setClasse('btn-primary');
        $button->onClick("$(location).attr('href', '/sellsy?action=diag&id_p='+$('#services').val())");


        $widget_diag->setContent($services.$button);

    }
    else
        $widget_diag->setContent($texte);

    $row = new Row($widget_diag);
    echo $row;



    $widget_subjects = new DatabaseWorker('comptes_principaux', false);
    $widget_subjects->noDatatable();
    $widget_subjects->addWhere('id="' . $_SESSION['compte_principal'] . '"');
    $widget_subjects->setWidget('Objects des documents créés');
    $widget_subjects->displayedFields(Array('sellsy_doc_create_before_invoice', 'sellsy_subject_inter_atelier', 'sellsy_subject_inter_site'));
    /*$help = new PopOver('Document tampon', 'test');
    $help->setLink('<i class="fa fa-question-circle"></i>');*/
    $widget_subjects->labelsDisplayedFields(Array('Document "tampon" créé avant la facture '.$help, 'Objet pour les interventions atelier', 'Objet pour les interventions sur site'));
    $widget_subjects->activeModify();

    $row = new Row($widget_subjects);
    echo $row;

    //echo 'ici'.$id_diag_sellsy;
}




?>