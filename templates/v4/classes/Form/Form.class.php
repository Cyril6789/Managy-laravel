<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/02/2016
 * Time: 10:57
 */
class Form
{

    private $action;
    private $method;
    private $id;
    private $content;
    private $file='';

    public function __construct($id='', $action='', $method='post')
    {
        $this->id = $id;
        $this->action = $action;
        $this->method = $method;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getHTML()
    {
        return (string) $this;
    }

    public function file()
    {
        $this->file = 'enctype="multipart/form-data"';
    }

    public function __toString()
    {
        $html = '
        <form id="'.$this->id.'" action="'.$this->action.'" method="'.$this->method.'" class="form-horizontal" '.$this->file.'>
                '.$this->content.'
        </form>
        ';

        return $html;
    }
}