<?php

session_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RaspiSMS
 *
 * @author Cyril
 */
class RaspiSMS {

    private $destinataire;
    private $ip;
    private $message;
    private $mail;
    private $pass;
    private $erreur;


    public function __construct($message) 
    {
        $this->message = str_replace(' ', '%20', $message);
        $this->ip = '88.169.99.242';
        $this->destinataire = Array();
        $this->destinataire[] = '0687470091';
        $this->mail = "contact@depaninfo67.com";
        $this->pass = '6167bchs';
    }
    
    public function addDest($num)
    {
        $this->destinataire[] = $num;
    }

    public function send()
    {
       $link =  'http://'.$this->ip.'/RaspiSMS/smsAPI/send/email_'.$this->mail.'/password_'.$this->pass;
       foreach ($this->destinataire as $dest) 
       {
           $link .= '/numbers_'.$dest;
       }
       
       $link .= '/text_'.$this->message;
       
       
       $curl = curl_init();
       curl_setopt($curl, CURLOPT_URL, $link);
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
       curl_setopt($curl, CURLOPT_COOKIESESSION, true);
       $return = curl_exec($curl);
       curl_close($curl);
       
       $erreur = str_replace('{', '', $return);
       $erreur = str_replace('}', '', $erreur);
       $erreur = str_replace('error', '', $erreur);
       $erreur = str_replace(':', '', $erreur);
       $erreur = str_replace('"', '', $erreur);
       
       $this->erreur = $erreur;
    }
    
    public function getError() 
    {
        return $this->erreur;
    }

}
