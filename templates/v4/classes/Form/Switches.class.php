<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 01/02/2017
 * Time: 18:09
 */
class Switches extends CheckBox
{
    private $dataon = '';
    private $dataoff = '';
    private $dataontext = '';
    private $dataofftext = '';
    private $size = '';
    private $tab_colors = Array();

    public function __construct($name, $id='')
    {
        $this->name = $name;
        if ($id)
            $this->id = $id;
        else
            $this->id = $name;


        $this->tab_colors = Array('primary', 'info', 'success', 'warning', 'danger', 'default');

        $this->setclass('make-switch');

        $this->setDataOn();
        $this->setDataOff();
        $this->setSize('normal');
    }

    public function setSize($size)
    {
        $array = Array('mini', 'small', 'normal', 'large');
        if(in_array(strtolower($size), $array))
            $this->size = 'data-size="'.strtolower($size).'"';
        else
            $this->size = 'data-size="normal"';

    }

    public function setDataOn($dataon="success", $text='On')
    {
        if(in_array(strtolower($dataon), $this->tab_colors))
            $this->dataon = ' data-on-color="'.strtolower($dataon).'" ';
        else
            $this->dataon = ' data-on-color="success" ';


        $this->dataontext = ' data-on-text=\''.$text.'\' ';

    }

    public function setDataOff($dataoff="danger", $text='Off')
    {
        if(in_array(strtolower($dataoff), $this->tab_colors))
            $this->dataoff = ' data-off-color="'.strtolower($dataoff).'" ';
        else
            $this->dataoff = ' data-off-color="danger" ';

        $this->dataofftext = ' data-off-text=\''.$text.'\' ';
    }

    public function getChecked()
    {
        return $this->checked;
    }

    private function generateData()
    {
        return $this->dataon.' '.$this->dataoff.' '.$this->dataofftext.' '.$this->dataontext.' '.$this->size;
    }

    public function __toString()
    {

        $this->addData($this->generateData());

        return parent::__toString();
    }

}