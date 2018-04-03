<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 03/10/2015
 * Time: 09:33
 */

class SuiviColis {
    private $message;
    private $date;
    private $site;
    private $bloc;
    public $numero;

    public function __construct($numero)
    {
        if(!empty($numero))
        {

            $colis = new suiviColissimo($numero);

            $this->numero = $numero;
            $this->bloc = $colis->getSuivi();
            $this->message = str_replace('</td>', '', str_replace('<td headers="Libelle">', '', $colis->getSuivi()[0]['message']));
            $this->site = str_replace('</td>', '', str_replace('<td headers="site" class="last">', '', $colis->getSuivi()[0]['lieu']));
            $this->date = str_replace('</td>', '', str_replace('<td headers="Date">', '', $colis->getSuivi()[0]['date']));
        }

    }

    public function getBloc()
    {
        return $this->bloc;
    }

    public function getMessage()
    {

        if(empty($this->message) AND !empty($this->numero))
            return $this->getErreur();
        else
            return $this->message;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getLieu()
    {
        return $this->site;
    }

    public function getErreur()
    {
        return 'Ce numéro de colis est invalide.'; //$this->error;
    }

} 