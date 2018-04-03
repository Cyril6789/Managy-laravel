<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 10/02/2016
 * Time: 15:08
 */
class Button
{
    private $value;
    private $link;
    private $data_toggle='';
    private $disable='';
    private $classe='';
    private $onclick;
    private $title;
    private $id;
    private $target_blank = false;

    public function __construct($value, $link, $title='', $id='')
    {
        $this->value = $value;
        $this->link = $link;
        $this->title = $title;
        $this->id = $id;
    }

    /**
     * @param bool $target_blank
     */
    public function setTargetBlank()
    {
        $this->target_blank = true;
    }

    public function data_toggle($value)
    {
        $this->data_toggle = 'data-toggle="'.$value.'"';
    }

    public function disable()
    {
        $this->disable = 'disabled';
    }

    public function setClasse($classe)
    {
        $this->classe = $classe;
    }

    public function setFullWidth()
    {
        $this->classe .= ' btn-block';
    }

    public function onClick($value)
    {
        $this->onclick = 'onclick="'.$value.'"';
    }

    public function getHTML()
    {
        return (string)$this;
    }


    public function __toString()
    {
        if($this->target_blank)
            $target = 'target="_blank"';

        if($this->disable)
            return '<a '.$target.' '.$this->data_toggle.' '.$this->disable.' class="btn '.$this->classe.'" id="'.$this->id.'">'.$this->value.'</a>';
        else
            return '<a '.$target.' '.$this->data_toggle.'  href="'.$this->link.'" '.$this->onclick.' class="btn '.$this->classe.'" title="'.$this->title.'" id="'.$this->id.'">'.$this->value.'</a>';
    }

}