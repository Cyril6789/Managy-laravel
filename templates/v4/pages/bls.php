<?php session_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$success = new Success('Le mail a bien été envoyé !');
echo '<div id="succes_mail" style="display: none;">'.$success.'</div>';

$widget_results = new WidgetBox('Tableaux', 8);
$widget_results->setContent('', false, 'result_bls');
$form_filters = new FormLayout('Filtres', 4);

$form_filters->setFormControls('search');

$select_customer = new Select('form_id_client');
$select_customer->onChange("param();");
$select_customer->withSearch();
$select_customer->addOption('0', '--');
foreach($tab_clients AS $cl)
    $select_customer->addOption($cl['id'], $cl['titre'].' '.$cl['nom'].' '.$cl['prenom']);

$form_filters->addLine('Client', $select_customer);

$form_filters->addLine('', 'Dates');

$debut = new Text('debut');
$debut->dataMask('99/99/9999');
$debut->onKeyUp("param()");
$form_filters->addLine('Début', $debut);

$fin_d = new Text('fin');
$fin_d->dataMask('99/99/9999');
$fin_d->onKeyUp("param();");
$form_filters->addLine('Fin', $fin_d);

$form_filters->addLine('', 'Numéros de BL');

$debut_n = new Text('debut_n');
$debut_n->onKeyUp("param();");
$form_filters->addLine('Début', $debut_n);

$fin_n = new Text('fin_n');
$fin_n->onKeyUp("param();");
$form_filters->addLine('Fin', $fin_n);

$btn_generer_pdf = new Button('<span class="icon icon-file-text-alt"></span> Générer PDF', '#');
$btn_generer_pdf->onClick("redirectpdf();");
$btn_generer_pdf->setClasse('btn-primary');
$btn_generer_pdf->setFullWidth();

$form_filters->addLine('', $btn_generer_pdf);




echo new Row($widget_results.$form_filters);


$no_title = true;
$right = '<div class="right-sidebar">

<form action="">
    <h3>Pour le client</h3>
    <div class="block">';


$right .= '

    </div>
    <h3>Dates</h3>
    <div class="block">
       Début : <input type="date" id="debut0" size="6" onkeyup="param();" /><br /><br />
       Fin : <input type="date" id="fin0" size="6" onkeyup="param();"/>
    </div>
    <h3>Numéro BL  </h3>
    <div class="block">
        De : <input type="text" id="debut_n" size="3" onkeyup="param();" /><br /><br />
        À : <input type="text" id="fin_n" size="3" onkeyup="param();" />

    </div>
    <h3>Générer un PFD unique</h3>
    <div class="block">


     <span onclick="redirectpdf();" style="color: #ffffff;" class="badge block blue dark"><span class="icon icon-file-text-alt"></span> Générer PDF</span>

    </div>
</form>

</div><!-- End of right sidebar -->
';
?>


<script language="JavaScript">

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    function mailing_ajax(id, pdf) //Champs non rempli
    {
        if(!isEmail($('#mail_'+id).val()) || $('#text_'+id).val() == '' || $('#sujet_'+id).val() == '')
        {
            if(!isEmail($('#mail_'+id).val()))
                $('#mail_' + id).css('border-color', 'red');
            else
                $('#mail_' + id).css('border-color', '');

            if($('#text_'+id).val() == '')
                $('#text_' + id).css('border-color', 'red');
            else
                $('#text_' + id).css('border-color', '');

            if($('#sujet_'+id).val() == '')
                $('#sujet_' + id).css('border-color', 'red');
            else
                $('#sujet_' + id).css('border-color', '');



        }
        else //tout est ok
        {
            //alert('ok');
            $('#text_' + id).css('border-color', '');
            $('#mail_' + id).css('border-color', '');
            $('#mail_bl_' + id).modal('hide');

            //alert($('#sujet_'+id).val());

            klient = new XMLHttpRequest();
            klient.open("POST", "./ajax/send_bl.php");
            klient.setRequestHeader("Content-type","application/x-www-form-urlencoded");
            klient.send("id="+id+"&pdf="+pdf+"&mail="+$('#mail_' + id).val()+"&texte="+$('#text_' + id).val()+"&sujet="+$('#sujet_'+id).val());

            setTimeout(function(){
                    $('#succes_mail').show('slow');
                    param();
                },
                1000
            )

            setTimeout(function(){
                    $('#succes_mail').hide('slow');
                },
                5000
            )



        }

        //alert('ok');
    }


    function redirectpdf()
    {
        var id_client = document.getElementById('form_id_client').value;
        var debut = document.getElementById('debut').value;
        var fin = document.getElementById('fin').value;
        var debut_n = document.getElementById('debut_n').value;
        var fin_n = document.getElementById('fin_n').value;
        document.location.href="bl.pdf?id_client="+id_client+"&debut="+debut+"&fin="+fin+"&debut_n="+debut_n+"&fin_n="+fin_n;
    }

    function param()
    {
        var id_client = document.getElementById('form_id_client').value;
        var debut = document.getElementById('debut').value;
        var fin = document.getElementById('fin').value;
        var debut_n = document.getElementById('debut_n').value;
        var fin_n = document.getElementById('fin_n').value;
        load_bls(id_client, debut, fin, debut_n, fin_n);
    }


    function load_bls(id_client, debut, fin, debut_n, fin_n)
    {

        $('#result_bls').html('Chargement en cours...');
        klient = new XMLHttpRequest();
        klient.onreadystatechange = retourBls;
        klient.open("GET", "./ajax/load_list_bls.php?id_client="+id_client+"&debut="+debut+"&fin="+fin+"&debut_n="+debut_n+"&fin_n="+fin_n+"&template_name=v4");
        klient.send(null);

    }

    function retourBls() {


        document.getElementById("result_bls").innerHTML= klient.responseText;
    }

    $( document ).ready(function(){
       load_bls('0', '0', '0', '0', '0');
    });


    $(function() {
        $( "#debut" ).datepicker({
            onSelect: function(){
                param();
            },
            dateFormat: 'dd/mm/yy',
            closeText: 'Fermer',
            prevText: 'Précédent',
            nextText: 'Suivant',
            currentText: 'Aujourd\'hui',
            monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
            dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
            dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
            weekHeader: 'Sem.'

        });
        $( "#fin" ).datepicker({
            onSelect: function(){
                param();
            },
            dateFormat: 'dd/mm/yy',
            closeText: 'Fermer',
            prevText: 'Précédent',
            nextText: 'Suivant',
            currentText: 'Aujourd\'hui',
            monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
            dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
            dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
            weekHeader: 'Sem.'

        });
    });
</script>

