<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/08/2017
 * Time: 09:54
 */
//die('ok');
require_once './../templates/v4/classes/includes.php';
require_once './../classes/jsonModal.class.php';
require_once ('./../classes/mysql.class.php');
require_once './../classes/DataObject.class.php';
require_once './../classes/DatabaseWorker.class.php';
require_once './../classes/Maileur.class.php';
require_once './../functions/right.func.inc';
require_once './../functions/crypt_pass.func.php';
require_once './../functions/parseDate.func.inc';
require_once './../functions/AccesActivableModul.func.inc';
require './../moduls/customers/classes/GetInfosCustomer.class.php';
require './../moduls/sellsy/classes/sellsyConnect.php';
$db = new MySQL();
include_once './../constructPage/settings.gen.inc';

$db->Query('SELECT session_id FROM staffs WHERE id="' . $_SESSION['id'] . '"');
$row = $db->Row();
$session_id = $row->session_id;

//echo './../templates/'.TEMPLATE_NAME.'/ajax/modals/' . $_POST['modul'] . '/' . $_POST['action'] . '.php';
if($_SESSION['id'] >0 AND ($session_id == session_id() OR $_SESSION['id'] == 1))
{

    if (is_file('./../templates/'.TEMPLATE_NAME.'/ajax/modals/' . $_POST['modul'] . '/' . $_POST['action'] . '.php'))
        require './../templates/'.TEMPLATE_NAME.'/ajax/modals/' . $_POST['modul'] . '/' . $_POST['action'] . '.php';
    else
    {
        $modal = new jsonModal('Erreur de chemin');
        $modal->width("40%");
        $modal->content(new Danger('Le chemin spécifié pour cette modal n\'existe pas', false));
        echo $modal;
    }
}
else
{
    if (is_file('./../templates/v4/ajax/modals/general/connexion.php'))
    {
        $id_modal = $_POST['id_modal'];
        include './../templates/v4/ajax/modals/general/connexion.php';
    }
    else
    {
        $modal = new jsonModal('Erreur de chemin');
        $modal->width("40%");
        $modal->content(new Danger('Le chemin spécifié pour la connexion de cette modal n\'existe pas', false));
        echo $modal;
    }
}