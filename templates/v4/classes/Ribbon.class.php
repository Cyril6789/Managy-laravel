<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 13/04/2017
 * Time: 17:09
 */
class Ribbon
{
    private $color;
    private $text;
    private $position;
    private $content;
    private $id;
    private $title;
    private $icone;
    private $classe;

    public function __construct($title, $text, $color='primary', $position='right', $id='')
    {
        $array_color = Array('primary', 'default', 'danger', 'success');

        if(in_array(strtolower($color), $array_color))
            $this->color = strtolower($color);
        else
            $this->color = 'default';

        //$this->color = $color;
        $this->position = $position;
        $this->text = $text;
        $this->id = $id;
        $this->title = $title;
    }

    /**
     * @param mixed $classe
     */
    public function setClasse($classe)
    {
        $this->classe = $classe;
    }

    public function setIcone($icone='layers')
    {
        $this->icone = $icone;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function __toString()
    {
        $html = '
        <div class="portlet mt-element-ribbon '.$this->classe.' light portlet-fit bordered" id="'.$this->id.'">
            <div class="ribbon ribbon-right ribbon-clip ribbon-shadow ribbon-border-dash-hor ribbon-color-'.$this->color.' uppercase">
                <div class="ribbon-sub ribbon-clip ribbon-'.$this->position.'"></div> '.$this->text.' </div>
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-'.$this->icone.' font-green"></i>
                    <span class="caption-subject font-green bold uppercase">'.$this->title.'</span>
                </div>
            </div>
            <div class="portlet-body"> '.$this->content.' </div>
        </div>
        ';

        return $html;
    }

}





