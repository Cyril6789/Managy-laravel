<?php session_start();

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of addLog
 *
 * @author Cyril
 */
class addLog {
    
    private $texte;
    private $id_inter;
    private $id_client;
    private $query;
    private $cp = 0;


    public function __construct($texte, $cp=0)
    {
        $this->query = new MySQL();
        $this->texte = $this->query->SQLfix($texte);
        $this->cp = $cp;
    }
    
    
    public function setInter($id)
    {
        
        $this->id_inter = $this->query->SQLfix($id);
    }
    
    public function setClient($id) 
    {
        $this->id_client = $this->query->SQLfix($id);
    }
    
    
    public function insert() 
    {
        $sql = 'INSERT INTO logs (id_staff, ';
        if(!empty($this->id_inter))
            $sql .= 'id_inter, ';
        if(!empty($this->id_client))
            $sql .= 'id_client, ';

        if($_SESSION['id'] > 0)
            $staff = $_SESSION['id'];
        else
            $staff = -1;

        $sql .= 'texte, time, compte_principal) VALUES ("'.$staff.'", ';
        if(!empty($this->id_inter))
            $sql .= '"'.$this->id_inter.'", ';
        if(!empty($this->id_client))
            $sql .= '"'.$this->id_client.'", ';

        if($this->cp)
            $cp = $this->cp;
        else
            $cp = $_SESSION['compte_principal'];
        $sql .= '"'.$this->texte.'", "'.time().'", "'.$cp.'" )';
        
        $this->query->Query($sql);
//        echo $sql;
//        echo $db->Error();
                
    }
    
}
