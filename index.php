<?php session_start();
$microtime = microtime(true);

if(isset($_GET['debug']))    
    $_SESSION['debug'] = $_GET['debug'];
$_SESSION['requetes'] = 0;
$_SESSION['rights'] = array();

require_once './classes/PhpMailer/PHPMailerAutoload.php';
if(is_file('./moduls/logs/classes/addLog.class.php'))
    require_once ('./moduls/logs/classes/addLog.class.php');
if(is_file('./moduls/staffs/classes/GetInfosStaff.class.php'))
    require_once ('./moduls/staffs/classes/GetInfosStaff.class.php');
if(is_file('./moduls/customers/classes/GetInfosCustomer.class.php'))
    require_once ('./moduls/customers/classes/GetInfosCustomer.class.php');
if(is_file('./moduls/dropbox/classes/Dropbox.class.php'))
    require_once('./moduls/dropbox/classes/Dropbox.class.php');

//require_once ('./classes/sellsyConnect.php');

require_once ('./classes/DataObject.class.php');
require_once ('./classes/automatismes.class.php');
require_once ('./classes/elFinder.class.php');
require_once ('./classes/SmsFactor.class.php');
require_once ('./classes/colissimo.class.php');
require_once ('./classes/SuiviColis.class.php');
require_once ('./classes/DatabaseWorker.class.php');
require_once ('./classes/BubbleBox.class.php');
require_once ('./classes/smseur.class.php');
require_once ('./classes/Smsform.class.php');
require_once ('./classes/Mailform.class.php');
require_once ('./classes/Maileur.class.php');
require_once ('./classes/ExternalLink.class.php');
require_once ('./classes/notificationMail.class.php');
require_once ('./classes/mysqlancien.class.php');
require_once ('./classes/mysql.class.php');
$dbancien = new MySQLancien();
$db = new MySQL();
require_once('./functions/changements_interventions.func.inc');
require_once('./functions/AccesActivableModul.func.inc');
require_once('./functions/parseDate.func.inc');
require_once('./functions/constructPage.func.inc');
require_once('./functions/addMonthsToDate.php');
constructPage();
require_once('./functions/right.func.inc');
require_once('./functions/loadRightsStaff.func.inc');
loadRightsStaff();
require_once('./functions/loadRightsSettings.func.inc');
$rights = loadRightsSettings();
require_once('./functions/loadLanguageModuls.func.inc');
loadLanguageModuls();
include('./classes/RaspiSMS.class.php');
if(AccesActivableModul('tasks'))
    if(is_file('./moduls/tasks/classes/tasks.class.php'))
        include('./moduls/tasks/classes/tasks.class.php');
if(AccesActivableModul('sellsy')) {
    if (is_file('./moduls/sellsy/classes/sellsyConnect.php'))
        include('./moduls/sellsy/classes/sellsyConnect.php');
    if (is_file('./moduls/sellsy/classes/checkCustomer.class.php'))
        include('./moduls/sellsy/classes/checkCustomer.class.php');
}
$_SESSION['conso_sms'] = 0;
$_SESSION['commande_sms'] = 0;

$db->Query('SELECT * FROM stocks_sms WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');
$row = $db->Row();
$_SESSION['conso_sms'] = $row->conso;
$_SESSION['commande_sms'] = $row->commande;


require_once ('./classes/modalAjax.class.php');


//ajouter dans connectés
$db->QUERY('SELECT COUNT(*) AS nb FROM connectes WHERE id_staff="'.$_SESSION['id'].'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');

$row = $db->Row();
if($row->nb AND $_SESSION['id'])
{
    $db->Query('UPDATE connectes SET time="'.time().'" WHERE id_staff="'.$_SESSION['id'].'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
  
}
else
{
    $staff = new GetInfosStaff($_SESSION['id']);
    $db->QUERY('SELECT COUNT(*) AS nb FROM connectes WHERE id_staff="'.$_SESSION['id'].'" AND compte_principal="'.$staff->getCompte().'" ');
    $row = $db->Row();
    if($row->nb)
        $db->Query('UPDATE connectes SET time="'.time().'" WHERE id_staff="'.$_SESSION['id'].'" AND compte_principal="'.$staff->getCompte().'" ');
    else
    {
        if($_SESSION['id'])
            $db->Query('INSERT INTO connectes (id_staff, time, compte_principal) VALUES ("'.$_SESSION['id'].'", "'.time().'", "'.$staff->getCompte().'") ' );
    }
    
}

include('./constructPage/post_general.php');



$page = new Page();
$db->Close();

$microtime_fin = microtime(true);
$diff = $microtime_fin-$microtime;
echo "
<!--";
echo $diff;
echo "-->";
echo "
<!--";
echo $_SERVER['PHP_SELF'];
echo "-->";
?>

