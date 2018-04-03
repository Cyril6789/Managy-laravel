<?php session_start();
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 10/09/2016
 * Time: 15:36
 */


$compte_principal = 8; //NE SURTOUT PAS CHANGER CETTE VALEUR !! : CORRESPOND AU COMPTE DEMO
$interval_execution = 3;
$horaire_ouverture = 8;
$horaire_fermeture = 19;
$nb_intervention_journaliere = 26; // 25

include('./classes/mysql.class.php');
include('./moduls/logs/classes/addLog.class.php');
include('./classes/ExternalLink.class.php');
$db = new MySQL();

/*
 * Classe de génération de client aléatoire
 */
Class CreateRandomClient {

    private $titre;
    private $pro_part;
    private $nom;
    private $prenom='';
    private $portable;
    private $fixe;
    private $mail;
    private $adresse;
    private $cp;
    private $ville;

    public function __construct()
    {
        $db2 = new Mysql();

        if(rand(0,3))
            $this->pro_part = 2;
        else
            $this->pro_part = 1;

        if($this->pro_part == 2) {
            /*Selection d'un prénom aleatoirement dans la BDD */
            $SQL = 'SELECT prenom, genre FROM prenoms_demo ORDER BY RAND() LIMIT 1';
            $db2->Query($SQL);
            $r = $db2->Row();

            $prenom = $r->prenom;
            $prenom[0] = strtoupper($prenom[0]);


            $this->prenom = $prenom;


        if($r->genre == 'm')
            $this->titre = 'M';
        else
            if(!rand(0,5))
                $this->titre = 'Mlle';
            else
                $this->titre = 'Mme';
         }
        else
            $this->titre = 'Sté';

        //Selection d'un nom de famille aléatoirement dans la BDD
        $SQL = 'SELECT nom FROM noms_demo ORDER BY RAND() LIMIT 1';
        $db2->Query($SQL);
        $r = $db2->Row();

        $this->nom = $r->nom;
        $this->nom = trim(str_replace(' ', '', $this->nom));


        /* créatioon de l'adresse mail*/
        if($this->pro_part == 2)
            $this->mail = $this->wd_remove_accents(strtolower($this->nom)).'.'.$this->wd_remove_accents(strtolower($this->prenom)).'@'.$this->generephrase().'.com';
        else
            $this->mail = 'contact@'.$this->wd_remove_accents(strtolower($this->nom)).'.fr';

        $numero = rand(1, 150);
        $complement = Array ('A', 'B', 'C', 'D');
        if(!rand(0,10))
            $numero.= $complement[rand(0, count($complement)-1)];

        /*selection d'une rue aleatoir dans la BDD*/
        $SQL = 'SELECT nom FROM rues_demo ORDER BY RAND() LIMIT 1';
        $db2->Query($SQL);
        $r = $db2->Row();

        $this->adresse = $numero.' '.$r->nom;

        /*Selection d'une ville et de son code postal dans la bdd*/
        $sql = 'SELECT code_postal, nom_ville FROM villes ORDER BY RAND() LIMIT 1';

        $db2->Query($sql);
        $r = $db2->Row();

        $this->cp = $r->code_postal;
        $this->ville = $r->nom_ville;

        $this->portable = '+33 (0)'.rand(6,7).' 12 34 56 78';
        $this->fixe = '+33 (0)'.rand(1,5).' 12 34 56 78';


    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function getProPart()
    {
        return $this->pro_part;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function getAdresse()
    {
        return $this->adresse;
    }

    public function getCp()
    {
        return $this->cp;
    }

    public function getVille()
    {
        return utf8_encode($this->ville);
    }

    public function getPortable()
    {
        return $this->portable;
    }

    public function getFixe()
    {
        return $this->fixe;
    }

    private function generephrase($nb_mot = 1)
    {

        $voyelles = Array('a', 'e', 'i', 'o', 'u', 'y');
        $consonnes = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'z');

        $mot = '';

        for ($i = 0; $i < $nb_mot; $i++) {
            $nombre_syllabes = rand(2, 5);
            $consonne_dabord = rand(0, 1);

            if ($consonne_dabord)
                for ($j = 0; $j < $nombre_syllabes; $j++)
                    $mot .= $consonnes[rand(0, count($consonnes) - 1)] . $voyelles[rand(0, count($voyelles) - 1)];
            else
                for ($j = 0; $j < $nombre_syllabes; $j++)
                    $mot .= $voyelles[rand(0, count($voyelles) - 1)] . $consonnes[rand(0, count($consonnes) - 1)];

            if ($nb_mot - 1 > $i)
                $mot .= ' ';
        }

        return $mot;

    }

    private function wd_remove_accents($str, $charset='utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
    }


}

function generephrase($nb_mot = 1)
{

    $voyelles = Array ('a', 'e', 'i', 'o', 'u', 'y');
    $consonnes = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'z');

    $mot = '';

    for($i=0; $i<$nb_mot; $i++)
    {
        $nombre_syllabes = rand(2, 5);
        $consonne_dabord = rand(0, 1);

        if($consonne_dabord)
            for($j=0; $j<$nombre_syllabes; $j++)
                $mot .= $consonnes[rand(0, count($consonnes) - 1)].$voyelles[rand(0, count($voyelles) - 1)];
        else
            for($j=0; $j<$nombre_syllabes; $j++)
                $mot .= $voyelles[rand(0, count($voyelles) - 1)].$consonnes[rand(0, count($consonnes) - 1)];

        if($nb_mot - 1 > $i)
            $mot .= ' ';
    }

    return $mot;
}



if(date('H') > $horaire_ouverture AND date('H') < $horaire_fermeture AND date('N') < 7 OR $_GET['force'] == 1) //Verification de l'heure (8h->19h du lundi au samedi)
{
    $nombre_exection = ($horaire_fermeture - $horaire_ouverture) * 60 / $interval_execution;


    /*
     * ACTIONS SUR INTERVENTIONS EXISTANTES
     */


    $sql = 'SELECT COUNT(*) AS nb_staffs FROM staffs WHERE compte_principal="'.$compte_principal.'" ';
    $db->Query($sql);
    $r = $db->Row();

    if (rand(1, $nombre_exection) <= $nb_intervention_journaliere * 6 * $r->nb_staffs) {

        // On choisi un employé au hasard du compte demo
        $sql = 'SELECT id FROM staffs WHERE compte_principal="' . $compte_principal . '" ORDER BY RAND() LIMIT 1';
        $db->Query($sql);
        $r = $db->Row();
        $_SESSION['id'] = $r->id;

        echo ' staff '.$_SESSION['id'].' fait une ';

        $il_y_a_3_heures = time() - (3 * 60 * 60);

        $sql = 'SELECT id_inter, i.type_atelier_rdv
                FROM interventions AS i
                INNER JOIN prise_en_charge As pec
                ON (pec.id_intervention = i.id_inter)
                AND (pec.id_staff = "' . $_SESSION['id'] . '")
                AND (pec.compte_principal = "' . $compte_principal . '")
                WHERE i.time_cloture = "0"
                AND time_ouverture < "'.$il_y_a_3_heures.'"
                AND i.compte_principal = "' . $compte_principal . '"
                ORDER BY RAND() LIMIT 1';
        //echo $sql;
        $db->Query($sql);
        $row = $db->Row();

        $id_inter = $row->id_inter;
        $tar = $row->type_atelier_rdv;
        echo 'action sur inter ' . $id_inter . ' : ';

        $action = rand(1,10); //CHOIX d'une action au hasard

        echo 'rand : '.$action.' tar : '.$tar;


        if ($action == 1 AND $tar == 1) // AND !rand(0,3)) // ajout commande
        {
            echo 'ajout commmande';


            $add_jour = rand(1, 15);
            $time_debut = time() + $add_jour * 24 * 60 * 60;
            $date_r = mktime(0, 0, 0, date('m', $time_debut), date('d', $time_debut), date('Y', $time_debut));

            $sql = 'INSERT INTO commandes (id_inter, fournisseur, bdc, num_cde, colis, date_cde, date_reception, compte_principal) VALUES ("' . $id_inter . '", "' . generephrase() . '", "BDC-' . rand(1000, 4000) . '", "' . rand(2000, 10000) . '", "8A' . RAND(200000000000000, 800000000000000) . '", "' . time() . '", "' . $date_r . '", "' . $compte_principal . '" )';
            $db->Query($sql);
            //changements_interventions($id_inter);
            $log = New addLog('a créé une commande', $compte_principal);
            $log->setInter($id_inter);
            $log->insert();
        }

        if ($action == 2 AND $tar == 1) // reception commande
        {
            echo 'commande reçue';

            $db->Query('UPDATE commandes SET cde_recue="1", date_reception="' . time() . '"  WHERE id_inter="' . $id_inter . '" AND compte_principal="' . $compte_principal . '" ');
            //changements_interventions($id_inter);
            $db->Query($sql);
            $log = New addLog('a marqué la commande comme receptionnée', $compte_principal);
            $log->setInter($id_inter);
            $log->insert();

        }

        if ($action == 3 AND $tar == 1) // AND !rand(0,4)) //ajout sous-traitance
        {
            echo 'ajout-sous-traitance';
            $add_jour = rand(1, 15);
            $time_debut = time() + $add_jour * 24 * 60 * 60;
            $date_r = mktime(0, 0, 0, date('m', $time_debut), date('d', $time_debut), date('Y', $time_debut));

            $sql = 'INSERT INTO sous_traitances (id_inter, devis, nom, num_cde, colis_alle, colis_retour, date_sst, date_retour, compte_principal) VALUES ("' . $id_inter . '", "DEV-' . rand(1000, 5000) . '", "' . generephrase(rand(1, 2)) . '", "' . rand(2000, 10000) . '", "8A' . RAND(200000000000000, 800000000000000) . '", "", "' . time() . '", "' . $date_r . '", "' . $compte_principal . '" )';
            //changements_interventions($id_inter);
            $db->Query($sql);
            $log = New addLog('a envoyé l\'intervention en sous-traitance', $compte_principal);
            $log->setInter($id_inter);
            $log->insert();
        }

        if ($action == 4 AND $tar == 1) // reception sous-traitance
        {
            echo 'sous traitance de retour';

            $db->Query('UPDATE sous_traitances SET retour="1", date_retour="' . time() . '" WHERE id_inter="' . $id_inter . '" AND compte_principal="' . $compte_principal . '" ');
            //changements_interventions($id_inter);
            $db->Query($sql);
            $log = New addLog('a receptionné le retour de sous-traitance', $compte_principal);
            $log->setInter($id_inter);
            $log->insert();

        }

        if($action == 5) // Sauvegarde
        {
            echo "sauvegarde ";


            if(!rand(0,5))
                $message_interne = file_get_contents('http://loripsum.net/api/1/plaintext/short/');
            else
                $message_interne = '';

            $resolution = file_get_contents('http://loripsum.net/api/1/plaintext/short/');

            if(!rand(0,3))
                $message_client =file_get_contents('http://loripsum.net/api/1/plaintext/short/');
            else
                $message_client = '';

            if(!rand(0,2))
                $materiel_ajoute = $db->SQLfix($_POST['materiel_ajoute']);
            else
                $materiel_ajoute = '';


            $sql = 'UPDATE interventions
                        SET
                        message_interne="'.$message_interne.'",
                        resolution="'.$resolution.'",
                        message_client="'.$message_client.'",
                        materiel_ajoute ="'.$materiel_ajoute.'"';
                $log = New addLog('a sauvegardé une saisie', $compte_principal);
                $log->setInter($id_inter);
                $log->insert();


            $sql .= ' WHERE id_inter="'.$id_inter.'"  AND compte_principal="'.$compte_principal.'" ';
            $db->Query($sql);
        }

        if($action >= 6) //clôture !
        {
            echo 'cloture';


            $db->Query('SELECT COUNT(*) AS nb_cde FROM commandes WHERE cde_recue!="1" AND id_inter="' . $id_inter . '" AND compte_principal="' . $compte_principal . '" ');
            $row = $db->Row();
            $nb_cde = $row->nb_cde;

            $db->Query('SELECT COUNT(*) AS nb_sst FROM sous_traitances WHERE retour!="1" AND id_inter="' . $id_inter . '" AND compte_principal="' . $compte_principal . '" ');
            $row = $db->Row();
            $nb_sst = $row->nb_sst;

            if(!$nb_cde AND !$nb_sst) {

                if(rand(0,1))
                    $nb_prestation = 1;
                else
                    $nb_prestation = rand(1, 2);

                for ($j = 0; $j < $nb_prestation; $j++) {
                    $sql = 'SELECT * FROM prestations WHERE compte_principal="' . $compte_principal . '" ORDER BY RAND() LIMIT 1';
                    $db->Query($sql);
                    $row = $db->Row();

                    $sql = 'INSERT INTO prestations_effectuees (id_inter, id_presta, designation, duree, compte_principal) VALUES ("' . $id_inter . '", "' . $row->id . '", "' . $row->designation . '", "' . $row->duree_defaut . '", "' . $compte_principal . '")';
                    $db->Query($sql);
                }
                $resolution = file_get_contents('http://loripsum.net/api/1/plaintext/short/');

                $sql = 'UPDATE interventions
                        SET
                        time_cloture="' . time() . '",
                        resolution="' . $resolution . '"';
                $log = New addLog('a clôturé l\'intervention', $compte_principal);
                $log->setInter($id_inter);
                $log->insert();


                $sql .= ' WHERE id_inter="' . $id_inter . '"  AND compte_principal="' . $compte_principal . '" ';
                $db->Query($sql);
                echo $db->Error();
            }

        }

        echo ' <br />';
        $_SESSION['id'] = 0;
    }


    if (rand(1, $nombre_exection) <= $nb_intervention_journaliere*2) //Prise en charge
    {


        $sql = 'SELECT id FROM staffs WHERE compte_principal="' . $compte_principal . '" ORDER BY RAND() LIMIT 1';
        $db->Query($sql);
        $r = $db->Row();
        $_SESSION['id'] = $r->id;

        //Selection aléatoire d'une intervention prise en charge par personne
        $sql = 'SELECT inter.id_inter
                    FROM interventions AS inter
                    WHERE inter.compte_principal="'.$compte_principal.'"
                    AND inter.time_cloture = 0
                    AND NOT EXISTS (
                                    SELECT NULL
                                    FROM prise_en_charge
                                    WHERE id_intervention = inter.id_inter
                                    AND compte_principal="'.$compte_principal.'"
                                     )
                    ORDER BY RAND() LIMIT 1';
        $db->Query($sql);

        $row = $db->Row();

        $sql = 'INSERT INTO prise_en_charge (id_staff, id_intervention, time, compte_principal) VALUES ("' . $_SESSION['id'] . '", "' . $row->id_inter . '", "' . time() . '", "' . $compte_principal . '" )';
        //changements_interventions($id_inter);
        $db->Query($sql);
        $log = New addLog('a pris en charge l\'intervention', $compte_principal);
        $log->setInter($row->id_inter);
        $log->insert();

        echo 'prise en charge intervention '.$row->id_inter .' par staff '.$_SESSION['id'].'<br />';
        $_SESSION['id'] = 0;
    }


    /*
     * FIN ACTIONS SUR INTERVENTIONS EXISTANTES
     */

    /*
     * CREATION INTERVENTION
     */
    if (rand(1, $nombre_exection) <= $nb_intervention_journaliere) // on créé ?
    {
        echo 'on créé une intervention';
        $sql = 'SELECT id FROM staffs WHERE compte_principal="' . $compte_principal . '" ORDER BY RAND() LIMIT 1';
        $db->Query($sql);
        $r = $db->Row();
        $staff = $r->id;

        if (rand(0, 8)) //1, $nb_intervention_journaliere*2) < $nb_intervention_journaliere*2 - 1)
        {
            echo 'création clients : ';

            $client = new CreateRandomClient(); //Appel de la classe de génération aléatoire de client

            $titre = $client->getTitre();
            $pro_part = $client->getProPart();
            $nom = $client->getNom();
            $prenom = $client->getPrenom();
            $adresse = $client->getAdresse();
            $cp = $client->getCp();
            $ville = $client->getVille();
            $mail = $client->getMail();
            $portable = $client->getPortable();
            $fixe = $client->getFixe();

            echo $titre . ' ' . $nom . ' ' . $prenom . ' ' . $adresse . ' ' . $cp . ' ' . $ville;


            $db->Query('INSERT INTO clients (titre, nom, prenom, pro_part, mail, adresse, cp, ville, portable, fixe, compte_principal) Values ("' . $titre . '", "' . $nom . '", "' . $prenom . '", "'.$pro_part.'", "' . $mail . '", "' . $adresse . '", "' . $cp . '", "' . $ville . '","'.$portable.'", "'.$fixe.'", "' . $compte_principal . '" )');

            $id_client = $db->GetLastInsertID();

            $_SESSION['id'] = $staff;
            $log = New addLog('a créé le client', $compte_principal);
            $log->setClient($id_client);
            $log->insert();

        }
    else {
            $sql = 'SELECT id FROM clients WHERE compte_principal = "' . $compte_principal . '" ORDER BY RAND() LIMIT 1';
            $db->Query($sql);
            $r = $db->Row();
            $id_client = $r->id;
            echo $id_client . ' ';
        }


        echo '<br />';


        //On choisi un matériel aléatoirement (iphone, pc fixe, portable...)
        $sql = 'SELECT id FROM materiels WHERE compte_principal="' . $compte_principal . '" ORDER BY RAND() LIMIT 1';
        $db->Query($sql);
        $r = $db->Row();

        $id_materiel = $r->id;

        //On choisit un système d'exploitation aléatoirement
        $sql = 'SELECT id FROM se WHERE compte_principal="' . $compte_principal . '" ORDER BY RAND() LIMIT 1';
        $db->Query($sql);
        $r = $db->Row();

        $id_se = $r->id;

        //Un antivirus...
        $sql = 'SELECT id FROM antivirus WHERE compte_principal="' . $compte_principal . '" ORDER BY RAND() LIMIT 1';
        $db->Query($sql);
        $r = $db->Row();
        $id_antivirus = $r->id;

        $external = new ExternalLink();
        $ext = $db->SQLFix($external);

        $atelier = rand(0, 4); //Intervention sur site ou atelier ?


        $mdp = generephrase();
        $panne = file_get_contents('http://loripsum.net/api/1/plaintext/short/'); //Panne aleatoire issue du LOREM IPSUM
        $matos = file_get_contents('http://loripsum.net/api/1/plaintext/short/'); //Idem
        $tarif = rand(0, 300);


        if ($atelier) {
            $urgente = rand(0, 1);

            $garantie = rand(0, 1);
        }

        $db->Query('SELECT id_inter FROM interventions WHERE compte_principal="' . $compte_principal . '" ORDER BY id_inter DESC LIMIT 1');
        $row = $db->Row();


        $derniere_inter = $row->id_inter;
        $nouvelle_inter = $derniere_inter + 1;





        if ($atelier)
            $db->Query('INSERT INTO interventions (id_inter, id_staff_ouverture, time_ouverture, id_client, id_materiel, id_se, id_antivirus, urgente, mdp, garantie, panne, matos, tarif_estimatif, compte_principal, external_link) VALUES ("' . $nouvelle_inter . '", "' . $staff . '", "' . time() . '", "' . $id_client . '", "' . $id_materiel . '", "' . $id_se . '", "' . $id_antivirus . '", "' . $urgente . '", "' . $mdp . '", "' . $garantie . '", "' . $panne . '", "' . $matos . '", "' . $tarif . '", "' . $compte_principal . '", "'.$ext.'" )');
        else {
            $add_jour = rand(1, 15);
            $add_heure = rand(1, 2);

            $heure_debut = rand(8, 17);
            $minutes = Array(0, 15, 30, 45);
            $minute_debut = $minutes[rand(0, count($minutes) - 1)];

            $time_debut = time() + $add_jour * 24 * 60 * 60;


            $timestamp = mktime($heure_debut, $minute_debut, 0, date('m', $time_debut), date('d', $time_debut), date('Y', $time_debut));
            $timestampf = $timestamp + ($add_heure * 60 * 60);

            $external = new ExternalLink();
            $ext = $db->SQLFix($external);

            $db->Query('INSERT INTO interventions (id_inter, id_staff_ouverture, time_ouverture, id_client, panne, type_atelier_rdv, rdv_debut, rdv_fin, compte_principal, external_link) VALUES ("' . $nouvelle_inter . '", "' . $staff . '", "' . time() . '", "' . $id_client . '",  "' . $panne . '", "2", "' . $timestamp . '", "' . $timestampf . '",  "' . $compte_principal . '", "'.$ext.'" )');


            //+1}

            echo 'atelier : ' . $atelier;



        }
        $_SESSION['id'] = $staff;

        $log = New addLog('a créé l\'intervention', $compte_principal);
        $log->setInter($nouvelle_inter);
        $log->insert();

        $_SESSION['id'] = '';
    }

        /*
         * FIN CREATION INTERVENTION
         */


}
else
    echo 'fermé';



$db->Close();
