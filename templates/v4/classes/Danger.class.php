<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/02/2016
 * Time: 14:24
 */
class Danger extends Alert
{

    public function __construct($content, $removable=true)
    {
        $this->content = $content;
        $this->removable = $removable;
        $this->type = 'danger';
    }

    public function __toString()
    {
        return (string) $this->getHTML();
    }
}