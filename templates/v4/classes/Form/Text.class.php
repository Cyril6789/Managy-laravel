<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/02/2016
 * Time: 11:56
 */
class Text
{
    private $id;
    private $name;
    private $value='';
    private $width;
    private $required='';
    private $onkeyup='';
    private $dataMask = '';
    private $picker = '';
    private $placeholder='';
    private $disabled='';
    private $autocomplete='';
    private $datetimepicker;
    private $onchangefunction;
    private $data_color = '';
    private $spinner;
    private $spinner_min;
    private $spinner_max;
    private $spinner_step;


    public function __construct($name, $id='', $width=12)
    {
        $this->name = $name;
        if($id)
            $this->id = $id;
        else
            $this->id = $name;
        $this->width = $width;

    }

    public function noAutocomplete()
    {
        $this->autocomplete = 'autocomplete="off"';
    }

    public function dateTimePicker($function='')
    {
        $this->picker = 'datetimepicker-perso';
        $this->datetimepicker = true; //'datetimepicker';
        if($function)
            $this->onchangefunction = "
         $('#".$this->id."').on('changeDate', function(){
               ".$function."
        });
        ";
        $this->dataMask('99/99/9999 99:99');
    }

    public function datePicker()
    {
        $this->picker = 'date-picker';
        $this->dataMask('99/99/9999');
    }

    public function timePicker()
    {
        $this->picker = 'timepicker timepicker-24';
    }

    public function placeHolder($placeholder)
    {
        $this->placeholder = 'placeholder="'.$placeholder.'"';
    }


    public function required()
    {
        $this->required = 'required';

    }

    public function dataMask($dataMask)
    {
        $this->dataMask = 'data-mask="'.$dataMask.'"';
    }

    public function onKeyUp($onkeyup)
    {
        $this->onkeyup = 'onkeyup="'.$onkeyup.'"';
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function colorPicker()
    {
        $this->picker = 'bs-colorpicker';
        $this->data_color = 'data-color-format="hex"';

    }

    public function disabled()
    {
        $this->disabled = 'disabled';
    }

    public function spinner($min = '', $max = '', $step='')
    {
        $this->spinner_min = $min;
        $this->spinner_max = $max;
        $this->spinner_step = $step;
        $this->spinner = true;
    }

    private function script()
    {

        if($this->spinner)
        {
            if(is_numeric($this->spinner_min))
                $min = 'min: '.$this->spinner_min.',';
            if(is_numeric($this->spinner_max))
                $max = 'max: '.$this->spinner_max.',';
            if(is_numeric($this->spinner_step))
                $step = 'step: '.$this->spinner_step.',';

            $script = '
            <script>
                $(function(){
                    $(\'#'.$this->id.'\').spinner({
                    '.$min.'
                    '.$max.'
                    '.$step.'
                    });
                });
            </script>
            ';
        }
        else {
            if ($this->datetimepicker)
                $script = "
<script>
    $(function() {
        $('.datetimepicker-perso').datetimepicker({
            format: 'dd/mm/yyyy hh:ii',
            todayBtn: true,
            autoclose: true,
            weekStart: 1,
            todayHighlight: true,
            time: false
        })
        " . $this->onchangefunction . "
    });
</script>";
            else
                $script = '<style>.colorpicker {
  z-index: 9999;
}</style>';
        }
        return $script;
    }


    public function __toString()
    {
       return $this->script().'<input type="text" name="'.$this->name.'" '.$this->autocomplete.' '.$this->disabled.' id="'.$this->id.'" value="'.$this->value.'" class="form-control col-md-'.$this->width.' '.$this->required.' '.$this->picker.'" '.$this->onkeyup.' '.$this->dataMask.' '.$this->data_color.' '.$this->placeholder.'/>';
    }

}