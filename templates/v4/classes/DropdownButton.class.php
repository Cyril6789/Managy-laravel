<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 01/04/2016
 * Time: 08:34
 */
class DropdownButton
{
    private $value_opener;
    private $dropup;
    private $size = '';
    private $tab_sub_buttons = Array();
    private $i = 0;
    private $id;

    public function __construct($value_opener='<i class="fa fa-bars"></i>', $id='')
    {
        $this->value_opener=$value_opener;
        $this->setSize();
        $this->id = $id;
    }

    public function setDropUp()
    {
        $this->dropup = '';
    }

    public function setSize($size='xs')
    {
        $this->size = 'btn-'.$size;
    }

    public function addSubButton($value, $link='', $onclick='')
    {
        $this->tab_sub_buttons[$this->i]['value'] = $value;
        $this->tab_sub_buttons[$this->i]['link'] = $link;
        $this->tab_sub_buttons[$this->i]['onclick'] = $onclick;
        $this->i++;
    }

    public function __toString()
    {
        $html = '
        <div class="btn-group '.$this->dropup.'">
            <a class="btn btn-default '.$this->size.' " id="'.$this->id.'" href="javascript:;" data-toggle="dropdown">
                '.$this->value_opener.'
                <i class="fa fa-angle-down"></i>
            </a>
            <ul class="dropdown-menu pull-right">
        ';

        foreach($this->tab_sub_buttons AS $btn)
        {
            $html .= '
                <li><a href="'.$btn['link'].'" onclick="'.$btn['onclick'].'">'.$btn['value'].'</a></li>';
        }

        $html .= '
            </ul>
        </div>';

        return $html;
    }

}