<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 30/03/2017
 * Time: 17:36
 */
class PopOver
{
    private $titre;
    private $contenu;
    private $placement;
    private $link='';
    private $href;

    public function __construct($titre, $contenu)
    {
        $this->titre = $titre;
        $this->contenu = $contenu;
        $this->setPlacement();
        $this->setHref('javascript:;');
    }

     /**
     * @param mixed $href
     */
    public  function setHref($href)
    {
        $this->href = $href;
    }

    public function setLink($link)
    {
        $this->link = $link;
    }

    public function setPlacement($placement='top')
    {
        $this->placement = $placement;
    }

    public function __toString()
    {
        $html = '<a href="'.$this->href.'" class="popovers" data-trigger="hover" data-html="true"   data-container="body"   data-placement="'.$this->placement.'" data-content="'.$this->contenu.'" data-original-title="'.$this->titre.'">'.$this->link.'</a>';
        return $html;
    }
}