<?php session_start();
//require_once './classes/inputSelect.php';
//require_once './classes/requete.php';
//require_once './classes/SmsFactorClass.php';
//require_once './fonctions/droit_dacces.php';
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sms
 *
 * @author Cyril
 */
class Smseur {
    
    private $numeros;
    private $message;
    private $signature;
    private $expediteur;
    private $erreur;
    private $cp = 0;
    private $mail = '';
    private $id_customer;
    private $id_staff;
    private $id_inter;


    public function __construct($message, $cp=0, $mail = '')
    {
        $this->message = $message;
        $this->numeros = Array();
        $this->erreur = '';
        $this->cp = $cp;
        $this->mail = $mail;
    }
    
    public function AddNumero($numero)
    {
        $this->numeros[] = $numero;
    }

    public function setIdCustomer($id)
    {
        $this->id_customer = $id;
    }

    public function setIdInter($id)
    {
        $this->id_inter = $id;
    }

    public function setIdStaff($id)
    {
        $this->id_staff = $id;
    }

    public function setComptePrincipal($cp)
    {
        $this->cp = $cp;
    }

    private function ParseNumero($num)
    {
        $numero = $num;
        $numero = str_replace('+', '', $numero);
        $numero = str_replace('(0)', '', $numero);
        $numero = str_replace(' ', '', $numero);
        return $numero;
    }

        public function AddSignature($signature) 
    {
        $this->signature = $signature;
    }
    
    public function AddExpediteur($expediteur) 
    {
        $this->expediteur = $expediteur;
    }
    
    public function getError()
    {
        return $this->erreur;
    }

    public function envoie()
    {
        
        if(!empty($this->message) AND !(empty($this->numeros)))
        {
            if(!empty($this->signature))
                $message = $this->message."\n".$this->signature;
            else
                $message = $this->message;
            foreach ($this->numeros AS $numero)
            {
                
                $longueur = strlen($message);
                
                if($longueur<=160)
                {
                    $nb_sms = 1;
                }
                else
                {
                    $nb_sms = round(($longueur-1)/154)+1;
                }
                        
                global $db;

                if(empty($_SESSION['compte_principal']))
                    $db->Query('SELECT * FROM stocks_sms WHERE compte_principal="'.$this->cp.'" ');
                else
                    $db->Query('SELECT * FROM stocks_sms WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');

                echo $db->Error();

                $row = $db->Row();
                $_SESSION['conso_sms'] = $row->conso;
                $_SESSION['commande_sms'] = $row->commande;
                
                $sms_restant = $_SESSION['commande_sms'] - $_SESSION['conso_sms'];
                if($sms_restant > $nb_sms)
                {
                    $dossier = getcwd();
                    $tab_dossier = explode('/', $dossier);
                    $current = $tab_dossier[count($tab_dossier) - 1 ];

                    if($current != 'managy') {
                        require_once('./../classes/SmsFactor.class.php');
                        require_once('./../classes/Maileur.class.php');
                        require_once('./../classes/notificationMail.class.php');
                    }


                    $sms = new SmsFactor("contact@depaninfo67.com", "6167bchs", $this->expediteur);
                    $reponse = $sms->sendSMS($message, $this->ParseNumero($numero));
                    if(empty($_SESSION['compte_principal']))
                        $db->Query('UPDATE stocks_sms set conso = conso + "'.$nb_sms.'" WHERE compte_principal="'.$this->cp.'" ');
                    else
                        $db->Query('UPDATE stocks_sms set conso = conso + "'.$nb_sms.'" WHERE compte_principal="'.$_SESSION['compte_principal'].'" ');

                    $_SESSION['conso_sms'] += $nb_sms;


                    $id_inter = $db->SQLFix($_GET['id_inter']);


                    $notification = new notificationMail('4', $this->cp);
                    if(!$_SESSION['prenom'])
                        $prenom = 'Managy';
                    else
                        $prenom = $_SESSION['prenom'];
                    $notification->tab_parse(Array('%id_inter%' => $id_inter, '%message%' => stripslashes($message), '%nb_sms%' => $nb_sms, '%prenom_staff%' => $prenom));
                    $notification->sendMail();


                    if($this->id_staff > 0)
                        $staff = $this->id_staff;
                    else
                        $staff = $_SESSION['id'];

                    if($this->id_inter > 0)
                        $inter = $this->id_inter;
                    else
                        $inter = $db->SQLFix($_GET['id_inter']);

                    if($this->cp > 0)
                        $cp = $this->cp;
                    else
                        $cp = $_SESSION['compte_principal'];

                    $db->Query('INSERT INTO sms_histo
                              (destinataire, id_inter, id_client, message, id_staff, timestamp, compte_principal)
                              VALUES
                              ("'.$numero.'", "'.$inter.'", "'.$this->id_customer.'", "'.$message.'", "'.$staff.'", "'.time().'", "'.$cp.'")
                              ');

                    
                }
                else
                {
                    $this->erreur = '1';
                }
            }
        }
    }
}
    
?>
