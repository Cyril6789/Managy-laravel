<div class="quick-nav-overlay"></div>
<!-- END QUICK NAV -->


<!--[if lt IE 9]>
<script src="./templates/v4/assets/global/plugins/respond.min.js"></script>
<script src="./templates/v4/assets/global/plugins/excanvas.min.js"></script>
<script src="./templates/v4/assets/global/plugins/ie8.fix.min.js"></script>
<![endif]-->
<!-- BEGIN CORE PLUGINS -->
<script src="./templates/v4/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="./templates/v4/assets/global/scripts/app.min.js" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN THEME LAYOUT SCRIPTS -->
<script src="./templates/v4/assets/layouts/layout/scripts/layout.min.js?r=2" type="text/javascript"></script>
<script src="./templates/v4/assets/layouts/layout/scripts/demo.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/layouts/global/scripts/quick-nav.min.js" type="text/javascript"></script>
<!-- END THEME LAYOUT SCRIPTS -->



<!-- Pickers -->

<script src="./templates/v4/assets/global/plugins/moment.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/clockface/js/clockface.js" type="text/javascript"></script>
<script src="./templates/v4/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/fullcalendar/fullcalendar.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/fullcalendar/lang/fr.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/bootstrap-toastr/toastr.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/typeahead/typeahead.bundle.min.js" type="text/javascript"></script>

<script src="./templates/v4/assets/global/plugins/bootstrap-wysihtml5/wysihtml5-0.3.0.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/bootstrap-markdown/lib/markdown.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/bootstrap-markdown/js/bootstrap-markdown.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/bootstrap-summernote/summernote.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/autosize/autosize.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./templates/v4/assets/global/plugins/bootstrap-inputmask/jquery.inputmask.js"></script>
<script src="./templates/v4/assets/global/plugins/amcharts/amcharts/amcharts.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/amcharts/amcharts/serial.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/amcharts/amcharts/pie.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/amcharts/amcharts/radar.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/amcharts/amcharts/themes/light.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/amcharts/amcharts/themes/patterns.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/amcharts/amcharts/themes/chalk.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/amcharts/ammap/ammap.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/amcharts/ammap/maps/js/worldLow.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/amcharts/amstockcharts/amstock.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/scripts/datatable.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>



<script type="text/javascript" src="./templates/v4/assets/apps/scripts/chat.js"></script>
<script type="text/javascript" src="./templates/v4/assets/apps/scripts/perso.js?r=8"></script>
<script src="./templates/v4/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.js" type="text/javascript"></script>
<script src="./templates/v4/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
<script src="./templates/v4/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>



<script type="text/javascript" src="plugins/typeahead/typeahead.min.js"></script> <!-- AutoComplete -->



<script>
    $(document).ready(function()
    {
        $('#clickmewow').click(function()
        {
            $('#radio1003').attr('checked', 'checked');
        });



        /*
        Fonction vérifiant si on est toujours connecté
        Sinon => modal de connexion
         */
        function is_logged() {

            $.ajax({
                url : './ajax/is_logged.php',
                type : 'GET',
                dataType : 'html',
                complete : function(resultat, statut){
                    var resp = resultat.responseText;
                    if(resp == "ko") //non connecté
                        $('#general_connexion').modal('show');
                    else
                        $('#general_connexion').modal('hide');

                }
            });

            setTimeout(function () {
                    is_logged();
                },
                1000);
        }

        is_logged();
    })
</script>

<?php
/*
 * génération de la modal de connexion si déconnecté
 */
$login_modal = new modalAjax('general', 'connexion');
$settings = Array('title' => 'Vous n\'êtes plus connecté', 'close_modal' => true);
$login_modal->settings($settings);
echo $login_modal->getModalHtml();

?>

<!--
<script data-main="./templates/v4/assets/elfinder/main.js?r=2" src="//cdnjs.cloudflare.com/ajax/libs/require.js/2.3.5/require.min.js"></script>-->

</body>

</html>