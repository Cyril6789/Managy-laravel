<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 17/09/2017
 * Time: 16:41
 */

$modal = new jsonModal('Changer le client pour cette intervention');
$modal->width("70%");


if(!empty($_POST['id_inter']))
{
    $inter = new DataObject('interventions');
    $inter->find($_POST['id_inter'], 'id_inter');
    if ($inter->id)
    {
        if (!$inter->time_cloture)
        {
            if (right('inter', 23))
            {
                require_once('./../moduls/customers/classes/SelectCustomer.class.inc');
                require_once('./../moduls/customers/langs/' . $_SESSION['hl'] . '.inc');
                require_once('./../moduls/intervention_informatique/sub/new/langs/' . $_SESSION['hl'] . '.inc');

                $selecteur_page = new SelectCustomer('intervention-change-customer-' . $_POST['id_inter'] . '-', 'intervention-change-customer-' . $_POST['id_inter']);

                //$selecteur->no_parent();
                $content = $selecteur_page->getHTML();
                $modal->hideButtons();
            }
            else
            {
                $content = new Danger('Vous n\'avec pas le droit de modifier le client de cette intervention', false);
                $modal->error();
            }
        }
        else
        {
            $content = new Danger('Cette intervention est clôturée. Vous ne pouvez plus la modifier', false);
            $modal->error();
        }
    }
    else
    {
        $content = new Danger('Cette intervention n\'existe pas', false);
        $modal->error();
    }
}
else
{
    $content = new Danger('Aucune intervention renseignée', false);
    $modal->error();
}


$modal->content($content);
echo $modal;