<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/02/2016
 * Time: 14:24
 */
class Success extends Alert
{

    public function __construct($content, $removable=true)
    {
        $this->content = $content;
        $this->removable = $removable;
        $this->type = 'success';
    }

    public function __toString()
    {
        return (string) $this->getHTML();
    }
}