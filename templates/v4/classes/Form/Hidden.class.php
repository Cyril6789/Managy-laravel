<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/02/2016
 * Time: 11:56
 */
class Hidden
{
    private $id;
    private $name;
    private $value;

    public function __construct($name, $id='')
    {

        if($id)
            $this->id = $id;
        else
            $this->id = $name;
        $this->name = $name;
    }

    public function setValue($value)
    {
        $this->value=$value;
    }

    public function __toString()
    {
        return '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'" value="'.$this->value.'"/>';
    }

}