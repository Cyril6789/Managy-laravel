/**
 * Created by Cyril on 09/05/2017.
 */



/* DATATABLE
 */
jQuery(document).ready(function() {
    jQuery().dataTable;
    var e = $(".table-datatable");
    e.dataTable({
        language: {
            aria: {
                sortAscending: ": activate to sort column ascending",
                sortDescending: ": activate to sort column descending"
            },
            emptyTable: "Aucune entrée dans ce tableau",
            info: "Affichage _START_ à _END_ de _TOTAL_ enregistrements",
            infoEmpty: "Aucune entrée trouvée",
            infoFiltered: "(filtered1 from _MAX_ total records)",
            lengthMenu: "Afficher _MENU_",
            search: "Rechercher :",
            zeroRecords: "Aucune résultat",
            paginate: {
                previous: "Précédent",
                next: "Suivant",
                last: "Dernier",
                first: "Premier"
            }
        },
        bStateSave: !0,
        lengthMenu: [
            [5, 15, 25, -1],
            [5, 15, 25, "Tout"]
        ],
        pageLength: 25,
        pagingType: "bootstrap_full_number",
        columnDefs: [{
            targets: [0]
        }, {
            targets: [0]
        }, {
            className: "dt-right"
        }]
    });
    e.find(".group-checkable").change(function() {
        var e = jQuery(this).attr("data-set"),
            t = jQuery(this).is(":checked");
        jQuery(e).each(function() {
            t ? ($(this).prop("checked", !0), $(this).parents("tr").addClass("active")) : ($(this).prop("checked", !1), $(this).parents("tr").removeClass("active"))
        })
    }), e.on("change", "tbody tr .checkboxes", function() {
        $(this).parents("tr").toggleClass("active")
    })
});


/*Recherche */
$(function() {

    $('#text-search').focus();

        //===== Sidebar Search (Demo Only) =====//
        $('#text-search').keyup(function () {
           // $('#home_sidebar').removeClass('page-sidebar-menu-closed');
            //$('body').removeClass('page-sidebar-closed');
//alert($('#text-search').val());
            if ($('#text-search').val() == '') {
                $('.sidebar-search-results').slideUp(200);
                //$('#home_sidebar').addClass('page-sidebar-menu-closed');
                //$('body').addClass('page-sidebar-closed');
            }
            else {

                $.ajax({
                    url: './ajax/search-v4.php',
                    type: 'GET',
                    data: 'request=' + $('#text-search').val(),
                    dataType: 'html',
                    complete: function (resultat, statut) {
                        $('#sidebar-search-results-editable').html(resultat.responseText);
                    }
                });


                $('.sidebar-search-results').slideDown(200);
            }
        });



    });

$(function(){



    $('.select2-input').on('keyup', function(){
        var request = $('.select2-input').val();
        $('#les_villes').append('<option value="0">'+ request+'</option>');
        $('#les_villes').trigger('update.select2');



    });
});



var ComponentsEditors = function() {
    var t = function () {
            jQuery().wysihtml5 && $(".wysihtml5").size() > 0 && $(".wysihtml5").wysihtml5({
                stylesheets: ["./templates/v4/assets/global/plugins/bootstrap-wysihtml5/wysiwyg-color.css"]
            })
        },
        s = function () {
            $("#summernote_1").summernote({
                height: 300
            })
        };
    return {
        init: function () {
            t(), s()
        }
    }
}();


var autocomplete_ville = function(){
    return {
        init: function () {
            $('#autocomplete-cp').typeahead({
                remote: './ajax/ajax_villes2.php?debut=%QUERY',
                limit: 15
            });

            $('#autocomplete-cp').on('typeahead:selected', function (e, item) {
                var cp = item['value'].substr(0, 5);
                var ville = item['value'].substr(6);
                $('#customer_cp').val(cp);
                $('#customer_city').val(ville);
            });


            $('#autocomplete-cp').keyup(function(){
                var cp = $('#autocomplete-cp').val().substr(0, 5);
                var ville = $('#autocomplete-cp').val().substr(6);
                $('#customer_cp').val(cp);
                $('#customer_city').val(ville);
            })
        }
    }

}();

$(document).ready(function() {

    ComponentsEditors.init();
    autocomplete_ville.init();



});







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

