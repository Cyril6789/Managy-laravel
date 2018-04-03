<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/02/2016
 * Time: 11:22
 */
class Row
{
    private $content;
    private $classe;

    public function __construct($content, $class='')
    {
        $this->content = $content;
        $this->classe = $class;
    }

    public function __toString()
    {
        $html = '
        <div class="'.$this->classe.' row">
            '.$this->content.'
        </div>
        ';

        return $html;
    }
}