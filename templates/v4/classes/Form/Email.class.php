<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/02/2016
 * Time: 11:56
 */
class Email
{
    private $id;
    private $name;
    private $value='';
    private $width;
    private $required='';
    private $onkeyup='';

    public function __construct($name, $id='', $width=12)
    {
        $this->id = $id;
        $this->name = $name;
        $this->width = $width;

    }

    public function required()
    {
        $this->required = 'form-control required';

    }

    public function onKeyUp($onkeyup)
    {
        $this->onkeyup = 'onkeyup="'.$onkeyup.'"';
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return '<input type="email" name="'.$this->name.'" id="'.$this->id.'" value="'.$this->value.'" class="form-control '.$this->required.'" '.$this->onkeyup.'/>';
    }

}