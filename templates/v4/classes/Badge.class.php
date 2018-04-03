<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 19/04/2017
 * Time: 16:03
 */
class Badge
{
    private $color;
    private $value;
    private $id;
    private $class='';

    public function __construct($value, $id='', $color='danger')
    {
        $tab_color = Array('danger', 'default', 'warning', 'success');
        $this->value = $value;
        $this->id = $id;
        if(in_array(strtolower($color), $tab_color))
            $this->color = strtolower($color);
        else
            $this->color = 'danger';
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function __toString()
    {
        return '<span class="badge badge-'.$this->color.' '.$this->class.'" id="'.$this->id.'">'.$this->value.'</span>';
    }

}