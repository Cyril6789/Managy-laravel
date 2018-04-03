<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/02/2016
 * Time: 11:56
 */
class Password
{
    private $id;
    private $name;
    private $value='';
    private $width;
    private $required='';
    private $onkeyup='';
    private $dataMask = '';
    private $date = '';
    private $script = '';
    private $autocomplete='';

    public function __construct($name, $id='', $width=12)
    {
        if($id != '')
            $this->id = $id;
        else
            $this->id = $name;
        $this->name = $name;
        $this->width = $width;

    }

    public function noAutocomplete()
    {
        $this->autocomplete = 'autocomplete="off"';
    }

    public function datePicker()
    {
        $this->script();
        $this->date = 'datepicker';
    }

    private function script()
    {
        $this->script = '
<script>
        /*
 * general_ui.js
 *
 * Demo JavaScript used on General UI-page.
 */

"use strict";

$(document).ready(function(){

	//===== Date Pickers & Time Pickers & Color Pickers =====//
	$( ".datepicker" ).datepicker({
		defaultDate: +7,
		showOtherMonths:true,
		autoSize: true,
		appendText: \'<span class="help-block">(jj-mm-yyyy)</span>\',
		dateFormat: \'dd-mm-yy\'
	});

	$(\'.inlinepicker\').datepicker({
		inline: true,
		showOtherMonths:true
	});

	$(\'.datepicker-fullscreen\').pickadate();
	$(\'.timepicker-fullscreen\').pickatime();


});

</script>';
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

    public function __toString()
    {
       return $this->script.'<input type="password" name="'.$this->name.'" '.$this->autocomplete.' id="'.$this->id.'" value="'.$this->value.'" class="col-md-'.$this->width.' '.$this->required.' '.$this->date.'" '.$this->onkeyup.' '.$this->dataMask.' />';
    }

}