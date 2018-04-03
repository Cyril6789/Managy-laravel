<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 02/09/2017
 * Time: 10:13
 */
class Font
{
    private $icon;

    public function __construct($icon)
    {
        $this->icon = $icon;
    }

    public function __toString()
    {
        return '<i class="fa fa-'.$this->icon.'"></i>';
    }
}