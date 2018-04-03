<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 23/02/2016
 * Time: 18:26
 */
class CheckBox
{
    protected $name;
    protected $id;
    protected $checked='';
    protected $onchange;
    protected $value='on';
    protected $class = '';
    protected $disabled='';
    private $data;

    public function __construct($name, $id='')
    {
        $this->name = $name;
        if($id)
            $this->id = $id;
        else
            $this->id = $name;
    }

    public function disabled()
    {
        $this->disabled = 'disabled';
    }


    public function setValue($value)
    {
        $this->value = $value;
    }

    public function checked()
    {
        $this->checked = 'checked';
    }


    public function onChange($onchange)
    {
        $this->onchange = 'onchange="'.$onchange.'"';
    }

    public function setclass($class)
    {
        $this->class .= $class;
    }

    public function addData($data)
    {
        $this->data = $data;
    }

    public function __toString()
    {
        return '<input type="checkbox" value="'.$this->value.'" '.$this->checked.' class="'.$this->class.'" name="'.$this->name.'" id="'.$this->id.'" '.$this->disabled.' '.$this->onchange.' '.$this->data.' />';
    }
}