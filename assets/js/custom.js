/*
 * custom.js
 *
 * Place your code here that you need on all your pages.
 */

"use strict";



$(document).ready(function(){

	//===== Sidebar Search (Demo Only) =====//
	$('.sidebar-search').keyup(function () {


        if($('#text-search').val() == '')
            $('.sidebar-search-results').slideUp(200);
        else
		{
			$.ajax({
				url : './ajax/search-melon.php',
				type : 'GET',
				data : 'request='+ $('#text-search').val(),
				dataType : 'html',
				complete : function(resultat, statut){
					$('#sidebar-search-results-editable').html(resultat.responseText);
				}
			});
			$('.sidebar-search-results').slideDown(200);
		}
		return false;
	});

    $('.sidebar-search').submit(function (e){
        e.preventDefault();

        if($.isNumeric($('#text-search').val()))
            document.location.href="./i"+$('#text-search').val();
    });

	$('.sidebar-search-results .close').click(function() {
		$('.sidebar-search-results').slideUp(200);
	});

	//===== .row .row-bg Toggler =====//
	$('.row-bg-toggle').click(function (e) {
		e.preventDefault(); // prevent redirect to #

		$('.row.row-bg').each(function () {
			$(this).slideToggle(200);
		});
	});

	//===== Sparklines =====//

	$("#sparkline-bar").sparkline('html', {
		type: 'bar',
		height: '35px',
		zeroAxis: false,
		barColor: App.getLayoutColorCode('red')
	});

	$("#sparkline-bar2").sparkline('html', {
		type: 'bar',
		height: '35px',
		zeroAxis: false,
		barColor: App.getLayoutColorCode('green')
	});

	//===== Refresh-Button on Widgets =====//

	$('.widget .toolbar .widget-refresh').click(function() {
		var el = $(this).parents('.widget');

		App.blockUI(el);
		window.setTimeout(function () {
			App.unblockUI(el);
			noty({
				text: '<strong>Widget updated.</strong>',
				type: 'success',
				timeout: 1000
			});
		}, 1000);
	});

	//===== Fade In Notification (Demo Only) =====//
	setTimeout(function() {
		$('#sidebar .notifications.demo-slide-in > li:eq(1)').slideDown(500);
	}, 3500);

	setTimeout(function() {
		$('#sidebar .notifications.demo-slide-in > li:eq(0)').slideDown(500);
	}, 7000);
});