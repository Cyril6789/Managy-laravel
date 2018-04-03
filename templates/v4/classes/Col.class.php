<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 10/02/2016
 * Time: 08:28
 */

class Col {

    protected  $width;
    protected $html;
    protected $col_type;
    protected $jump;
    private $cols = Array();
    private $i = 0;
    private $id;
    private $scrollable='';
    private $attr='';

    public function __construct($width=12, $col_type='lg', $id='')
    {

        $this->width = $width;
        $this->col_type = $col_type;
        $this->id = $id;
    }

    public function addCol($width, $type='lg')
    {
        $this->cols[$this->i]['width'] = $width;
        $this->cols[$this->i]['type'] = $type;
    }

    public function jump($width)
    {
        $this->jump = $width;
    }

    public function setContent($content)
    {
        if(!empty($this->jump))
            $class_add = 'col-'.$this->col_type.'-offset-'.$this->jump;
        else
            $class_add = '';

        foreach($this->cols AS $col)
            $class_add .= ' col-'.$col['type'].'-'.$col['width'];

        $this->html = '
<div class="col-'.$this->col_type.'-'.$this->width.' '.$class_add.' '.$this->scrollable.'" '.$this->attr.' id="'.$this->id.'">
        '.$content.'
</div>';

    }

    public function scrollable($heigh = '250px')
    {
        $this->scrollable = '';
        $this->attr = 'style="height:'.$heigh.'; overflow:auto"';
    }

    public function __tostring()
    {
        if(!empty($this->jump))
            $class_add = 'col-'.$this->col_type.'-offset-'.$this->jump;
        else
            $class_add = '';

        foreach($this->cols AS $col)
            $class_add .= ' col-'.$col['type'].'-'.$col['width'];

        if(empty($this->html))
            return '
<div class="col-'.$this->col_type.'-'.$this->width.' '.$class_add.' '.$this->scrollable.'" '.$this->attr.' id="'.$this->id.'">
    &nbsp;
</div>';
        else
            return $this->html;
    }

} 