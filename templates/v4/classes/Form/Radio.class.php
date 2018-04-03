<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 23/02/2016
 * Time: 18:26
 */
class Radio
{
    private $name;
    private $value;
    private $id;
    private $checked='';
    private $onchange='';
    private $disabled='';

    public function __construct($name, $value, $id='')
    {
        $this->name = $name;
        $this->value = $value;
        if(!empty($id))
            $this->id = $id;
        else
            $this->id = $name;
    }

    public function checked()
    {
        $this->checked = 'checked';
    }

    public function disabled()
    {
        $this->disabled = 'disabled';
    }

    public function onChange($onchange)
    {
        $this->onchange = 'onchange="'.$onchange.'"';
    }


    public function __toString()
    {
        return '<input type="radio" '.$this->checked.' value="'.$this->value.'" name="'.$this->name.'" id="'.$this->id.'" '.$this->onchange.' '.$this->disabled.'/>';
    }
}