<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/02/2016
 * Time: 14:24
 */
class Warning extends Alert
{

    public function __construct($content, $removable=true, $size='12')
    {
        $this->content = $content;
        $this->removable = $removable;
        $this->type = 'warning';
        $this->size = $size;
    }

    public function __toString()
    {
        return (string) $this->getHTML();
    }
}