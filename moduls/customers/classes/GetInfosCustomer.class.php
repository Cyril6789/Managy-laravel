<?php session_start();


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GetInfoStaff
 *
 * @author Cyril
 */
class GetInfosCustomer {
    
    private $id;
    private $titre;
    private $prenom;
    private $nom;
    private $mail;
    private $adresse;
    private $adresse_suite;
    private $cp;
    private $ville;
    private $fixe;
    private $port;
    private $propart;
    private $no_cp;
    private $id_sellsy;

    public function __construct($id, $no_cp = false, $replace_if_not_exist = true)
    {
        $db_clients = new MySQL();
        $id_staff = $db_clients->SQLfix($id);

        $this->no_cp = $no_cp;

        if(right('osef', '-1') OR $no_cp)
            $db_clients->Query('SELECT * FROM clients WHERE id="'.$id_staff.'" ');
        else
            $db_clients->Query('SELECT * FROM clients WHERE id="'.$id_staff.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
        $row_client = $db_clients->Row();
        if($row_client->id) //client existant
        {
            $this->id = $row_client->id;
            $this->titre = $row_client->titre;
            $this->prenom = $row_client->prenom;
            $this->nom = $row_client->nom;
            $this->mail = $row_client->mail;
            $this->adresse = $row_client->adresse;
            $this->adresse_suite = $row_client->adresse_suite;
            $this->cp = $row_client->cp;
            $this->ville = $row_client->ville;
            $this->fixe = $row_client->fixe;
            $this->port = $row_client->portable;
            $this->compte = $row_client->compte_principal;
            $this->propart = $row_client->pro_part;
            $this->id_sellsy = $row_client->id_sellsy;
        }
        else 
        {
            $this->id = 0;
            if($replace_if_not_exist) {
                $this->prenom = 'Prénom';
                $this->nom = 'Nom';
            }
            else
            {
                $this->prenom = '';
                $this->nom = '';
            }
            $this->mail = '';
            $this->compte = '0';
        }
        
    }


    public function getLinkSellsy()
    {

        return 'https://www.sellsy.fr/?_f=third&thirdid='.$this->getIdSellsy();
    }


    /**
     * @return mixed
     */
    public function getIdSellsy()
    {
        return $this->id_sellsy;
    }

    public function loadContacts()
    {
        $tab_contacts = Array();
        if($this->id)
        {
            $db_clients = new MySQL();
            if (right('osef', '-1') OR $this->no_cp)
                $db_clients->Query('SELECT id FROM clients WHERE id_parent="' . $this->id . '" ');
            else
                $db_clients->Query('SELECT id FROM clients WHERE id_parent="' . $this->id . '" AND compte_principal="' . $_SESSION['compte_principal'] . '" ');

            while($row = $db_clients->Row())
                $tab_contacts[] = new GetInfosCustomer($row->id);
        }

        return $tab_contacts;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPropart()
    {
        return $this->propart;
    }

    public function GetTitre()
    {
        return $this->titre;
    }

    public function GetNom() 
    {
        return $this->nom;
    }

    public function GetAdresse()
    {
        return $this->adresse;
    }

    public function GetAdresseSuite()
    {
        return $this->adresse_suite;
    }

    public function GetPrenom() 
    {
        return $this->prenom;
    }
    
    public function GetMail() 
    {
        return $this->mail;
    }
    
    public function getCompte() 
    {
        return $this->compte;
    }

    public function getCp()
    {
        return $this->cp;
    }

    public function getVille()
    {
        return $this->ville;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getFixe()
    {
        return $this->fixe;
    }

    public function getFullAdress()
    {
        return $this->adresse.' '.$this->cp.' '.$this->ville;
    }

    public function getFullName()
    {
        return $this->titre.' '.$this->prenom.' '.$this->nom;
    }


    public function getFullNameWithLink($full_link = false)
    {
        return '<a href="'.$this->GetProfileLink($full_link).'" class="">'.$this->getFullName().'</a>';
    }

    public function GetProfileLink($complete = false)
    {
        if($complete)
            return 'https://www.managy.fr/c'.$this->id;
        else
            return './c'.$this->id;
    }
}
