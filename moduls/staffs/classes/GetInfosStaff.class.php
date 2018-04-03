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
class GetInfosStaff {
    
    private $id;
    private $pseudo;
    private $prenom;
    private $nom;
    private $mail;
    private $compte;
    private $gerant;
    
    public function __construct($id, $force = false)
    {
        $db_staff = new MySQL();
        $id_staff = $db_staff->SQLfix($id);
        if(right('osef', '-1') OR $force)
            $db_staff->Query('SELECT * FROM staffs WHERE id="'.$id_staff.'" ');
        else
            $db_staff->Query('SELECT * FROM staffs WHERE id="'.$id_staff.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
        $row_staff = $db_staff->Row();

        if($row_staff->id) //Staff existant
        {
            $this->id = $row_staff->id;
            $this->pseudo = $row_staff->pseudo;
            $this->prenom = $row_staff->prenom;
            $this->nom = $row_staff->nom;
            $this->mail = $row_staff->mail;
            $this->compte = $row_staff->compte_principal;
            $this->gerant = $row_staff->gerant;
        }
        else 
        {
            if($id < 0)
            {
                $this->id = 0;
                $this->pseudo = 'Managy';
                $this->prenom = '';
                $this->nom = 'Managy';
                $this->mail = '';
                $this->compte = '0';
                $this->gerant = '';
            }
            else
            {
                $this->id = 0;
                $this->pseudo = 'Pseudo';
                $this->prenom = 'Prénom';
                $this->nom = 'Nom';
                $this->mail = '';
                $this->compte = '0';
                $this->gerant = '';
            }
        }
        
    }
    

    public function getGerant()
    {
        return $this->gerant;
    }
    
    public function GetPseudo() 
    {
        return $this->pseudo;
    }
    
    public function GetNom() 
    {
        return $this->nom;
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
    
    public function GetProfileLink()
    {
        return './s'.$this->id;
    }
}
