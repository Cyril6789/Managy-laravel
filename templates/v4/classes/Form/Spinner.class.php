<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/02/2016
 * Time: 11:56
 */
class Spinner
{
    private $id;
    private $name;
    private $value='';
    private $width;
    private $required='';
    private $onkeyup='';
    private $dataMask;
    private $min;
    private $max;
    private $step;
    private $disabled ='';
    private $time=false;

    public function __construct($name, $id='', $width=12)
    {
        $this->name = $name;
        if($id)
            $this->id = $id;
        else
            $this->id = $name;

        $this->width = $width;

    }

    public function setBornes($min, $max)
    {
        $this->min = $min;
        $this->max = $max;
    }

    public function setStep($step)
    {
        $this->step = $step;
    }

    public function dataMask($dataMask)
    {
        $this->dataMask = 'data-mask="'.$dataMask.'"';
    }

    private function script()
    {
        $html = '
        <script>
            "use strict";

            $(document).ready(function(){';


        $html .= '
                if ($.fn.spinner) {';

        if($this->time) {
             $html .= '
                    $( "#'.$this->id.'" ).timespinner({
                        step: 60*1000*'.$this->step.'
                    });
                    $("#'.$this->id.'").val("'.$this->value.'");
                    var current = $( "#'.$this->id.'" ).timespinner( "value" );
                    Globalize.culture("de-DE");
                    //$( "#'.$this->id.'" ).timespinner( "value", current );';
        }
        else
        $html .= '
                    $( "#'.$this->id.'" ).spinner({
                        min: '.$this->min.',
                        max: '.$this->max.',
                        step: '.$this->step.',
                        start: '.$this->value.'
                    });';

        $html .= '
                }
            });

        </script>
        ';
        return ''; //////////////////$html;
    }


    public function disabled()
    {
        $this->disabled = 'disabled';
    }

    public function required()
    {
        $this->required = 'required';

    }


    public function onKeyUp($onkeyup)
    {
        $this->onkeyup = 'onkeyup="'.$onkeyup.'"';
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function Time()
    {
        $this->time = true;
        $this->dataMask('99:99');
    }

    public function __toString()
    {
       return $this->script().'<input type="text" name="'.$this->name.'" '.$this->disabled.' id="'.$this->id.'"  value="'.$this->value.'" class="col-md-'.$this->width.' '.$this->required.' '.$this->picker.'" '.$this->onkeyup.' '.$this->dataMask.' />';
    }

}