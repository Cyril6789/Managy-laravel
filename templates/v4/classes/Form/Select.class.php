<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/02/2016
 * Time: 12:10
 */
class Select
{
    private $name;
    private $id;
    private $onchange;
    private $style;
    private $tab_options = Array();
    private $i=0;
    private $class;
    private $needle='';
    private $disabled='';
    
    public function __construct($name, $id='', $size=12)
    {
        $this->name = $name;
        if($id)
            $this->id = $id;
        else
            $this->id = $name;
        $this->class = 'col-md-'.$size;
    }

    public function disabled()
    {
        $this->disabled = 'disabled';
    }

    public function onChange($onchnage)
    {
        $this->onchange = $onchnage;
    }

    public function setStyle($style)
    {
        $this->style = $style;
    }

    public function addOption($value, $text, $selected=false, $disabled=false)
    {
        $this->tab_options[$this->i]['value'] = $value;
        $this->tab_options[$this->i]['text'] = $text;
        $this->tab_options[$this->i]['selected'] = $selected;
        $this->tab_options[$this->i]['disabled'] = $disabled;
        $this->i++;
    }

    public function setSelected($needle)
    {
        $this->needle = $needle;
    }

    public function withSearch($complement='')
    {
        $this->class .= ' select2 '.$complement;
    }

    public function __toString()
    {
        $html = '
        <select name="'.$this->name.'" id="'.$this->id.'" style="'.$this->style.'" onchange="'.$this->onchange.'" class="form-control '.$this->class.'" '.$this->disabled.'>';

        foreach($this->tab_options AS $option)
        {
            if($option['selected'])
                $selected = 'selected';
            else
                $selected = '';

            if($option['disabled'])
                $disable = 'disabled';
            else
                $disable = '';

            if($option['value'] == $this->needle)
                $selected = 'selected';
            $html .= '
            <option value="' . $option['value'] . '" '.$selected.' '.$disable.'>' . $option['text'] . '</option>';
        }

        $html .= '
        </select>';

        return (string) $html;
    }
    


}