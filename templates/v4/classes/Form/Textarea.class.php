<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/02/2016
 * Time: 11:42
 */
class Textarea
{
    private $name;
    private $id;
    private $value;
    private $rows;
    private $cols;
    private $disabled;
    private $style;
    private $elastic;
    private $wysiwyg;
    private $script;
    private $placeholder = '';

    public function __construct($name, $id='')
    {
        $this->name = $name;
        if($id)
            $this->id = $id;
        else
            $this->id = $name;

    }

    public function Wysiwyg()
    {
        $this->wysiwyg = 'wysihtml5';
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function disable()
    {
        $this->disabled = true;
    }

    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    public function placeholder($placeholder)
    {
        $this->placeholder = 'placeholder="'.$placeholder.'"';
    }

    public function setcols($cols)
    {
        $this->cols = $cols;
    }

    public function setStyle($style)
    {
        $this->style = $style;
    }

    public function elastic()
    {
        $this->elastic = 'autosizeme';
    }

    public function noNl()
    {
        $this->script = '
        <script>
            $(function() {
                $(\'#'.$this->id.'\').on(\'keypress keypress blur change\', function(e) {
                    if (e.keyCode === 13) {
                        $(\'#'.$this->id.'\').val($(\'#'.$this->id.'\').val()+\' \');
                        if (e.preventDefault) e.preventDefault();
                        return false;
                    }
                    $(\'#'.$this->id.'\').val( $(\'#'.$this->id.'\').val().replace(/[\r\n]+/g, \' \') );
                    //
                    //
                });
            });
        </script>';
    }

    public function __toString()
    {
        if($this->disabled)
            $disabled = 'disabled';
        else
            $disabled = '';

        return $this->script.'<textarea name="'.$this->name.'" id="'.$this->id.'" style="'.$this->style.'" '.$this->placeholder.' rows="'.$this->rows.'" cols="'.$this->cols.'" '.$disabled.' class="'.$this->elastic.' '.$this->wysiwyg.' form-control" >'.$this->value.'</textarea>';
    }

}