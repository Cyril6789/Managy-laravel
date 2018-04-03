<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/05/2016
 * Time: 13:39
 */
class File
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return '<input type="file" data-style="fileinput" name="'.$this->name.'">';
    }
}