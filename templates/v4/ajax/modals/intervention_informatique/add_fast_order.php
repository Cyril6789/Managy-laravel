<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 26/11/2017
 * Time: 10:57
 */

$modal = new jsonModal(new Font('cart-plus').' Ajouter une commande rapide');
$modal->width('50%');


require_once('./../moduls/customers/classes/SelectCustomer.class.inc');
require_once('./../moduls/customers/langs/' . $_SESSION['hl'] . '.inc');
require_once('./../moduls/intervention_informatique/sub/new/langs/' . $_SESSION['hl'] . '.inc');

$selecteur_page = new SelectCustomer('', '');
$selecteur_page->NoChangePage('reload_modal_fast_order');

//$selecteur->no_parent();
$content1 = '<div id="selecteur">'.$selecteur_page->getHTML().'</div>';


$script = '
<script>
    function reload_modal_fast_order(id)
    {
        //alert(id);
        $(\'#id_client\').val(id);
        $(\'#selecteur\').toggle(\'slow\');
        $(\'#order\').toggle(\'slow\');
        $(\'#intervention_informatique_add_fast_order\').find(\'.modal-footer\').toggle(\'slow\');
    }

</script>

';

$order = new FormLayout('<a href="javascript:void();" onclick="reload_modal_fast_order(0)">'.new Font('user-times').' Retour</a>');
$order->setFormControls('post_add_fast_order');
$modal->form_id('post_add_fast_order');

$id_client = new Hidden('id_client');
$titre = new Text('titre');
$order->addLine('Titre de la commande', $id_client.$titre);

$fournisseur = new Text('fournisseur');
$order->addLine('Fournisseur', $fournisseur);

$num_commande = new Text('num_commande');
$order->addLine('Numéro de commande', $num_commande);

$date_reception = new Text('date_reception');
$date_reception->datePicker();
$order->addLine('Date de réception estimée', $date_reception);

$note = new Textarea('note');
$order->addLine('Notes', $note);


$content2 = '<div id="order" style="display: none;">'.$order.'</div>';

$content = $script.$content1.$content2;

$modal->content($content);
$modal->hideButtons();




echo $modal;