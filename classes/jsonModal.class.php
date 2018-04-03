<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/08/2017
 * Time: 11:08
 */
class jsonModal
{
    private $tab;

    public function __construct($title)
    {
        $this->tab['title'] = $title;
        $this->width('50%');
    }

    public function content($content)
    {
        if(is_object($content))
           $this->tab['content'] = (string) $content;
        else
           $this->tab['content'] = $content;
    }

    public function width($width)
    {
        $this->tab['width'] = $width;
    }

    public function hideButtons()
    {
        $this->tab['hidebuttons'] = true;
    }

    public function form_id($id_form)
    {
        $this->tab['form_id'] = $id_form;
    }

    public function header_color($color)
    {
        $this->tab['color'] = $color;
    }

    public function error()
    {
        $this->header_color('#ED6B75');
        $this->width("40%");
    }

    public function returning()
    {
        return $this->tab;
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
        return json_encode($this->tab);
        //return (string) $this->tab;
    }
}