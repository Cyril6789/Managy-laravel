<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/02/2016
 * Time: 13:24
 */
class Accordion
{
    private $widget;
    private $content;
    private $elements = Array();
    private $i=1;
    private $rand;

    public function __construct($title, $width=12)
    {
        $this->widget = new WidgetBox($title, $width);
        $this->rand = rand(1, 10000);
    }

    public function setCollapse()
    {
        $this->widget->setCollapse();
    }

    public function addContent($title, $content)
    {
        $this->elements[$this->i]['title'] = $title;
        $this->elements[$this->i]['content'] = $content;
        $this->elements[$this->i]['id'] = $this->i;
        $this->i++;
    }

    public function setContentWithoutPanel($content)
    {
        $this->content = $content;
    }

    public function __toString()
    {
        $html = '';
        foreach ($this->elements AS $element)
        {
            if($element['id'] == 1)
                $in = 'in';
            else
                $in = '';
            $html .='
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapse_'.$this->rand.'_'.$element['id'].'">
                        '.$element['title'].'
                    </a>
                    </h3>
                </div>
                <div id="collapse_'.$this->rand.'_'.$element['id'].'" class="panel-collapse collapse '.$in.'">
                    <div class="panel-body">
                        '.$element['content'].'
                    </div>
                </div>
            </div>
            ';
        }

        $html_final = $this->content.'
        <div class="panel-group accordion" id="accordion">
            '.$html.'
        </div>
        ';

        $this->widget->setContent($html_final, false);

        return $this->widget->getHTML();
    }




}