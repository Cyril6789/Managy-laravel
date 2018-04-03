<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 10/02/2016
 * Time: 13:36
 */
class ProgressBar
{

    private $value;
    private $value_max;
    private $text;
    private $animate;
    private $bar;
    private $ratio;
    private $small = '';
    private $display_text = false;

    public function __construct($value, $value_max, $text = "jours")
    {
        $this->value = $value;
        $this->value_max = $value_max;
        $this->text = $text;

        if($value > $value_max)
            $this->animate = 'active';
        else
            $this->animate = "";


        if(!$value_max)
            $ratio = 0;
        else
            $ratio = round($value * 100 / $value_max);
        $this->ratio = $ratio;

        if($ratio > 75)
            $this->bar = 'danger';
        if($ratio <= 75)
            $this->bar = 'warning';
        if($ratio <= 50)
            $this->bar = 'success';
        if($ratio <= 25)
            $this->bar = 'info';
    }

    public function small()
    {
        $this->small = 'progress-small';
    }

    public function forceAnimate()
    {
        $this->animate = 'active';
    }

    public function display_text()
    {
        $this->display_text = true;
    }

    public function __toString()
    {
        $html = '
        <div class="progress progress-striped '.$this->small.' '.$this->animate.'">
            <div class="progress-bar progress-bar-'.$this->bar.'" style="width: '.$this->ratio.'%" '.$this->animate.'></div>
        </div>';

        if($this->display_text)
            $html .= '
            '.$this->value.' '.$this->text.' / '.$this->value_max;

        return $html;
    }

}