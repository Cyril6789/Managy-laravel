<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/02/2016
 * Time: 16:59
 */
class Feeds
{
    private $html;
    private $icones = true;

    public function __construct()
    {

    }

    public function noIcone()
    {
        $this->icones = false;
    }


    public function addLine($text, $time, $link= '')
    {
        $this->html .= '
    <li>';

        if($link)
            $this->html .='<a href="'.$link.'">
                                ';
        $this->html .= '
        <div class="col1">
            <div class="cont">';
        if($this->icones)
        {
            $this->html .= '
                <div class="cont-col1">
                    <div class="label label-sm label-info"><i class="fa fa-bullhorn"></i></div>
                </div>';
            $col = '2';
        }
        else
            $col = '1';
         $this->html .= '
                <div class="cont-col'.$col.'">
                    <div class="desc">'.$text.'</div>
                </div>
            </div>
        </div> <!-- /.col1 -->
        <div class="col2">
            <div class="date">'.$time.'</div>
        </div> <!-- /.col2 -->';
        if($link)
            $this->html .= '
                              </a>  ';
        $this->html .='
    </li>';
    }

    public function __toString()
    {
        return '
<ul class="feeds">
    '.$this->html.'
</ul>';
    }
}