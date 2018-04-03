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
		appendText: '<span class="help-block">(dd-mm-yyyy hh:ii)</span>',
		dateFormat: 'dd-mm-yy hh:ii'
	});

	$('.inlinepicker').datepicker({
		inline: true,
		showOtherMonths:true
	});

	$('.datepicker-fullscreen').pickadate();
	$('.timepicker-fullscreen').pickatime();


});