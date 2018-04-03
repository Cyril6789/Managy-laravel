$(function(){
   $('.select2-input').on('keyup', function(){
        var request = $('.select2-input').val();
       $('#les_villes').append('<option value="0">'+ request+'</option>');
       $('#les_villes').trigger('update.select2');



   });
});

$(document).ready(function() {


    $('#autocomplete-cp').typeahead({
        remote: './ajax/ajax_villes2.php?debut=%QUERY',
        limit: 15
    });

    $('#autocomplete-cp').on('typeahead:selected', function (e, item) {
        var cp = item['value'].substr(0, 5);
        var ville = item['value'].substr(6)
        $('#customer_cp').val(cp);
        $('#customer_city').val(ville);
    });

    $('#autocomplete-cp').keyup(function(){
        var cp = $('#autocomplete-cp').val().substr(0, 5);
        var ville = $('#autocomplete-cp').val().substr(6)
        $('#customer_cp').val(cp);
        $('#customer_city').val(ville);
    })

});


//date time picker
$(document).ready(function() {

    //===== Date Pickers & Time Pickers & Color Pickers =====//


    $('.datepicker-fullscreen').pickadate({
        monthsFull: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        weekdaysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
        today: 'aujourd\'hui',
        clear: 'Effacer',
        close: 'Fermer',
        formatSubmit: 'dd/mm/yyyy',
        format: 'dd/mm/yyyy'
    });
    $('.timepicker-fullscreen').pickatime({
        format: 'HH:i',
        clear: 'Effacer',
        interval: 5
    });

});




/*
$(document).ready(function() {

    //===== Autocomplete =====//
    // Using typehead.js-library
    $('#autocomplete-villes').typeahead({
        name: 'autocomplete-villes',
        remote: './ajax/ajax_villes2.php?debut=%QUERY',
        updater: function (item){
            alert('ok');
        }

    });

    $('#autocomplete-villes').on('typeahead:selected', function (e, datum) {
        alert(console.log(datum));
    });

});

/*

$(function(){

$('.select2-input').on('keyup', function(){

    var taille = $('.svilles');
        
        $('.select2-input').autocomplete({
            
            source: function( request, response ) {

                $('#les_villes').empty();

                $.ajax({

                    url: "./ajax/ajax_villes.php?debut="+request.term,
                    dataType: "json"

                })
                .success(function( data ) {

                        response( $.map( data, function( item ) {
                            
                            //$('body').html();
                            $('#les_villes').append('<option value="'+ item.cp + '_' + item.ville + '">'+ item.cp + ' ' + item.ville + ' (' + item.departement + ', ' + item.regio + ') </option>');
                            //$('.select2-results').append('<div class="select2-result-label"><span class="select2-match"></span>67210 Obernai (Rhin (Bas), Alsace) </div>');
                            
                        }));
                       
                });
        } 

});

});
});
*/


function java_mktime(hour,minutes,month,day,year) {
    return new Date(year, month - 1, day, hour, minutes, 0, 0).getTime() / 1000;
}

function set_end()
{
    var x = document.getElementById('date_heure').value;
    var tab_x = x.split(' ');

    var date = tab_x[0].split('/');

    var jour = date[0];
    var mois = date[1];
    var annee = date[2];

    var heures = tab_x[1].split(':');

    var heure = heures[0];
    var min = heures[1];

    var timestamp = java_mktime(heure, min, mois, jour, annee);

    var timestamp_fin = parseInt(timestamp) + parseInt(document.getElementById('ecart').value);

    var fin = new Date(timestamp_fin * 1000);
    jour = fin.getDate();
    if(jour < 10)
        jour = '0'+jour;

    mois = fin.getMonth() + 1;

    if(mois < 10)
        mois = '0'+mois;

    annee = fin.getFullYear();

    heure = fin.getHours();
    if(heure < 10)
        heure = '0'+heure;

    min = fin.getMinutes();
    if(min < 10)
        min = '0'+min;

    var final = jour+'/'+mois+'/'+annee+' '+heure+':'+min;
    //alert(final);

    document.getElementById('date_heure_fin'). value = final;
}

function set_ecart()
{
    var x = document.getElementById('date_heure').value;
    var tab_x = x.split(' ');

    var date = tab_x[0].split('/');

    var jour = date[0];
    var mois = date[1];
    var annee = date[2];

    var heures = tab_x[1].split(':');

    var heure = heures[0];
    var min = heures[1];

    var timestamp_debut = java_mktime(heure, min, mois, jour, annee);

    var x = document.getElementById('date_heure_fin').value;
    var tab_x = x.split(' ');

    var date = tab_x[0].split('/');

    var jour = date[0];
    var mois = date[1];
    var annee = date[2];

    var heures = tab_x[1].split(':');

    var heure = heures[0];
    var min = heures[1];

    var timestamp_fin = java_mktime(heure, min, mois, jour, annee);

    var ecart = parseInt(timestamp_fin) - parseInt(timestamp_debut);
    if(ecart > 0)
        document.getElementById('ecart').value = ecart;

}

$(window).bind('keydown', function(event) {
    if (event.ctrlKey || event.metaKey) {
        switch (String.fromCharCode(event.which).toLowerCase()) {
            case 'f':
                event.preventDefault();
                $('#text-search').focus();
                $('#text-search').css("border", "3px solid #8CCF48");

                setTimeout(function(){
                    $('#text-search').css("border", "0px solid");
                    setTimeout(function(){
                        $('#text-search').css("border", "3px solid #8CCF48");
                        setTimeout(function(){
                            $('#text-search').css("border", "0px solid");
                            setTimeout(function(){
                                $('#text-search').css("border", "3px solid #8CCF48");
                                setTimeout(function(){
                                    $('#text-search').css("border", "0px solid");
                                }, 500);
                            }, 80);
                        }, 80);
                    }, 80);
                }, 80);

                /*setTimeout(function(){
                    $('#text-search').css("border", "3px solid #8CCF48");
                }, 100);

                /*setTimeout(function(){
                    $('#text-search').css("border", "0px solid");
                }, 100);

                setTimeout(function(){
                    $('#text-search').css("border", "3px solid #8CCF48");
                }, 100);

                setTimeout(function(){
                    $('#text-search').css("border", "0px solid");
                }, 300);*/


                break;
        }
    }
});