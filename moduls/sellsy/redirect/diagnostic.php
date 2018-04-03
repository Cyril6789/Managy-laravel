<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 22/07/2017
 * Time: 10:23
 */
require_once ('./../../../classes/mysql.class.php');
require_once('./../../../functions/right.func.inc');
$db = new MySQL();
require_once('./../../../functions/AccesActivableModul.func.inc');

require_once ('./../../../classes/DataObject.class.php');


if(is_numeric($_GET['id_inter'])) {


    if (AccesActivableModul('sellsy')) {


        if (is_file('../classes/sellsyConnect.php'))
            include('../classes/sellsyConnect.php');

        $id_inter = $db->SQLFix($_GET['id_inter']);
        $sql = 'SELECT i.id, i.prefix, i.type_atelier_rdv, c.id_sellsy 
                FROM interventions AS i 
                INNER JOIN clients AS c
                ON (c.id = i.id_client)
                AND (c.compte_principal = "'.$_SESSION['compte_principal'].'")
                WHERE id_inter="'.$id_inter.'" 
                AND i.compte_principal="'.$_SESSION['compte_principal'].'"';

        $db->Query($sql);
        echo $db->Error();
        $row = $db->Row();

        if($row->id) {


            $sellsy = new DataObject('comptes_principaux');
            $sellsy->find($_SESSION['compte_principal'], 'id', false);

            if ($sellsy->sellsy_doc_create_before_invoice == 'devis')
                $type = 'estimate';
            else
                $type = 'proforma';

            if ($sellsy->id_diag_sellsy) {


                $request = array(
                    'method' => 'Catalogue.getOne',
                    'params' => array(
                        'type' => 'service',
                        'id' => $sellsy->id_diag_sellsy,
                    )
                );


                $response = json_encode(sellsyConnect::load()->requestApi($request));
                $parsed_json = json_decode($response);
                $tab_diag = (array)$parsed_json->response;

                if($row->type_atelier_rdv == 1)
                    $name = $sellsy->sellsy_subject_inter_atelier;
                else
                    $name = $sellsy->sellsy_subject_inter_site;

                echo $row->id_sellsy;

                $request = array(
                    'method' => 'Document.create',
                    'params' => Array(
                        'document' => Array(
                            'doctype' => $type,
                            'thirdid' => $row->id_sellsy,
                            'subject' => $name.$row->prefix.$id_inter
                        ),
                        'row' => Array(
                            '1' => Array(
                                'row_type' => 'item',
                                'row_linkedid' => $sellsy->id_diag_sellsy,
                                'row_name' => $tab_diag['name'],
                                'row_notes' => '<strong>' . $tab_diag['name'] . ' : </strong>' . $tab_diag['notes'],
                                'row_unitAmount' => $tab_diag['unitAmount']
                            )

                        )
                    )
                );
                $response = json_encode(sellsyConnect::load()->requestApi($request));
                $parsed_json = json_decode($response);
                $tab = (array)$parsed_json->response;

                print_r($tab);

                if($tab['error'])
                    echo 'Erreur lors de la creation';
                else
                    header('location: https://www.sellsy.fr/?_f=invoiceForm&from='.$tab['doc_id']);


            }
            else
                echo 'id diag non choisi';
        }
        else
            echo 'id inter invalide';
    }
    else
        echo 'Modul non activé';
}
else
    echo 'pas d\'id';