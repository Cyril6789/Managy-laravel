$(function(){
$('.chosen-search input').live('keyup', function(){
    
    var taille = $('.svilles');
    if(taille.length)
    {
        
        $('.chosen-search input').autocomplete({
            minLength: 1,
            source: function( request, response ) {
                //alert('1');
                $.ajax({
                    url: "./ajax/ajax_villes.php?debut="+request.term,
                    dataType: "json",
                    beforeSend: function(){ $('ul.chosen-results').empty(); $("#les_villes").empty(); }
                }).done(function( data ) {
                    var i=true;
                        response( $.map( data, function( item ) {
                            $('#les_villes').append('<option value="'+ item.cp + '_' + item.ville + '">'+ item.cp + ' ' + item.ville + ' (' + item.departement + ', ' + item.regio + ') </option>');
                            $("#les_villes").trigger("chosen:updated");
                            $('.chosen-search input').val(request.term);
                            if(i)
                            {
                                $("#settings-cp").val(item.cp);
                                $("#customer_cp").val(item.cp);
                                $("#affaire_cp").val(item.cp);
                                $("#settings-ville").val(item.ville);
                                $("#affaire_ville").val(item.ville);
                                $("#customer_city").val(item.ville);
                                i = false;
                            }
                            
                        }));

                       
                       
                });

            }
        });
        
    
    }
   

});

});



function java_mktime(hour,minutes,month,day,year) {
    return new Date(year, month - 1, day, hour, minutes, 0, 0).getTime() / 1000;
}