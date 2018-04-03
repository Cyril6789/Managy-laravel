<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 10/02/2016
 * Time: 13:27
 */
class Label
{

    private $label;
    private $content;
    private $link;
    private $id;
    private $onclick='';

    public function __construct($label='success', $content, $link='', $id='')
    {
        $tab_color = Array('danger', 'info', 'warning', 'success');
        if(in_array(strtolower($label), $tab_color))
            $this->label = $label;
        else
            $this->label = 'default';
        $this->content = $content;
        $this->link = $link;
        $this->id = $id;
    }

    /**
     * @param mixed $onclick
     */
    public function setOnclick($onclick)
    {
        $this->onclick = $onclick;
    }

    public function __toString()
    {
        if(empty($this->link))
            $html = '
        <span class="label label-sm label-'.$this->label.'">'.$this->content.'</span>';
        else
            $html = '
        <a href="'.$this->link.'" id="'.$this->id.'" onclick="'.$this->onclick.'"><span class="label label-sm label-'.$this->label.'">'.$this->content.'</span></a>';

        return $html;
    }


}