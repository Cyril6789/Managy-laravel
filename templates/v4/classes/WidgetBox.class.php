<?php
/**
 * Created by PhpStorm.
 * User: Staff
 * Date: 10/02/2016
 * Time: 08:38
 */

class WidgetBox extends Col{


    private $title;
    private $collapse= '';
    private $link = '';
    private $toolbar_buttons= Array();
    private $scroller = false;
    private $height;
    private $collapsed = '';
    private $box_color = '';
    private $title_font_color = 'font-green-sharp';
    private $array_box_color;
    private $box_html = '';

    public function __construct($title, $width=12, $col_type='md')
    {
        $this->title = $title;
        $this->width = $width;
        $this->col_type = $col_type;
        $this->array_box_color = Array('red', 'blue', 'green', 'yellow', 'purple', 'yellow-lemon', 'red-pink', 'yellow-casablanca', 'green-jungle');
        $this->setBoxColor('green');
    }



    public function jump($width)
    {
        $this->jump = $width;
    }

    public function addColW($width, $type='md')
    {
        $this->addCol($width, $type);
    }

    public function scrollable($height=160)
    {
        $this->scroller = true;
        $this->height = $height;
    }

    public function Collapsed()
    {
        $this->collapsed = 'portlet-collapsed';
    }

    public function setCollapse()
    {
        $this->collapse = '
            <a href="javascript:;" class="collapse"> </a>
            ';
    }

    public function addToolbarButtons($content)
    {
       $this->toolbar_buttons[] = $content;
    }

    public function setLinkOnTitle($link)
    {
        $this->link = $link;
    }

    public function setBoxColor($color)
    {
        if(in_array($color, $this->array_box_color))
        {
            $this->box_color = $color;
            $this->title_font_color = '';
        }
    }


    public function forceBox()
    {
        $this->box_html = ' box '.$this->box_color;
    }

    public function setContent($content, $box=true, $id='')
    {
        /*if($box) {
            $box_html = ''; //' box '.$this->box_color;
        }
        else {
            $box_html = '';

        }
        */


        $html = '
<div class="portlet '.$this->box_html.' ">
    <div class="portlet-title">
            <div class="caption '.$this->title_font_color.' bold">';
            if($this->link)
                $html .= '<a href="'.$this->link.'">';

            $html .= $this->title;

            if($this->link)
                $html .= '</a>';

        $html .= '</div>
            <div class="actions">
                ';

        foreach ($this->toolbar_buttons AS $btn)
            $html .= $btn;

        $html .= $this->collapse.'
                
            </div>
    </div>
    <div class="portlet-body '.$this->collapsed.'" id="'.$id.'">';

        if($this->scroller)
            $html .= '
        <div class="scroller" data-height="'.$this->height.'px" data-always-visible="1" data-rail-visible="0">';



        $html .= '
            ' .$content;

        if($this->scroller)
            $html .= '
        </div>';

        $html .= '
    </div>
</div>';

       Col::setContent($html);
    }

    public function getHTML()
    {
        return (string) $this;
    }

    public function __toString()
    {
        if(empty($this->html))
            $this->setContent('');

        return $this->html;
    }

} 