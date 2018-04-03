<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 09/03/2016
 * Time: 10:45
 */
class Calendar
{
    private $box;
    private $id;
    private $widget;
    private $script;
    private $tab_events=Array();
    private $i=0;
    private $not_load=0;

    public function __construct($titre, $id='calendar', $width=12, $box=false)
    {
        $this->id = $id;
        $this->box = $box;

        $this->widget = new WidgetBox($titre, $width);
    }

    public function addEvent($id, $title, $start_year, $start_month, $start_day, $start_hour, $start_minute, $end_year, $end_month, $end_day, $end_hour, $end_minute, $description, $color='#4D7496', $all_day=false )
    {
        $this->tab_events[$this->i]['id'] = $id;
        $this->tab_events[$this->i]['title'] = $title;
        $this->tab_events[$this->i]['start_year'] = $start_year;
        $this->tab_events[$this->i]['start_month'] = $start_month;
        $this->tab_events[$this->i]['start_day'] = $start_day;
        $this->tab_events[$this->i]['start_hour'] = $start_hour;
        $this->tab_events[$this->i]['start_minute'] = $start_minute;
        $this->tab_events[$this->i]['end_year'] = $end_year;
        $this->tab_events[$this->i]['end_month'] = $end_month;
        $this->tab_events[$this->i]['end_day'] = $end_day;
        $this->tab_events[$this->i]['end_hour'] = $end_hour;
        $this->tab_events[$this->i]['end_minute'] = $end_minute;
        $this->tab_events[$this->i]['description'] = $description;
        $this->tab_events[$this->i]['color'] = $color;
        $this->tab_events[$this->i]['allDay'] = $all_day;
        $this->i++;

    }

    /**
     * @param int $not_load
     */
    public function setNotLoad()
    {
        $this->not_load = 1;
    }


    public function getReady()
    {
        $dollar = '

                //===== Calendar =====//


                var h = {};

        if ($(\'#'.$this->id.'\').width() <= 400) {
            h = {
                left: \'title\',
                center: \'\',
                right: \'prev,next\'
            };
        } else {
            h = {
                left: \'prev,next\',
                center: \'title\',
                right: \'month,agendaWeek,agendaDay\'
            };
        }

        $(\'#'.$this->id.'\').fullCalendar({
            locale: \'fr\',
            lang: \'fr\',
            timeFormat: \'H:mm\',
            disableDragging: false,
            header: h,
            editable: true,
            droppable: true,
            drop: function (date, allDay) { // this function is called when something is dropped

            var datefin = new Date(date);
            datefin.setHours(datefin.getHours()+1);
                            // retrieve the dropped element\'s stored Event Object
                            var originalEventObject = $(this).data(\'eventObject\');
                            // we need to copy it, so that multiple events don\'t have a reference to the same object
                            var copiedEventObject = $.extend({}, originalEventObject);

                            // assign it the date that was reported
                            copiedEventObject.id = \'s\'+$(this).attr(\'id\');
                            copiedEventObject.start = date;
                            copiedEventObject.end = datefin;
                            copiedEventObject.allDay = false;
                            copiedEventObject.className = $(this).attr("data-class");

                            // render the event on the calendar
                            // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
                            $(\'#full_cal\').fullCalendar(\'renderEvent\', copiedEventObject, true);

                            // is the "remove after drop" checkbox checked?

                                // if so, remove the element from the "Draggable Events" list
                                $(this).remove();
                                 //alert($(this).attr(\'id\'));
                                // alert(date);
                                 
                                 
            

            $.ajax({
                url : \'./ajax/modifier_rdv.php\',
                type : \'GET\',
                data : \'new_time=\'+date+\'&new_end=\'+datefin+\'&id_rdv=\'+$(this).attr(\'id\'),
                dataType : \'html\'
            });
            
            $(location).attr(\'href\',"./rdv");

                        },
            eventDrop: function( event, delta, revertFunc, jsEvent, ui, view ) {
                SetAjax(event);
               
            },

            eventResize: function( event, jsEvent, ui, view ) {
                SetAjax(event);
            },
            eventClick: function(calEvent, jsEvent, view) {

                var id_rdv = calEvent.id;
                if(id_rdv[0] == \'s\')
                {
                    var inter = id_rdv.replace(\'s\', \'\');
                    //alert(\'ok\');
                    $("#btn-delete").hide();
                    $("#lieux_cal").hide();
                    $("#link_inter_btn").attr("href", "./i"+inter);
                    $("#link_inter").show();
                }
                else
                {

                    $("#btn-delete").show();
                    $(\'#btn-delete\').attr("onclick", "if(confirm(\'Êtes-vous sûr de vouloir supprimer ce rendez-vous ?\')) document.location.href = \'?rdv_suppr="+id_rdv+"\'");
                    $(\'#lieux_cal\').show();
                    $(\'#link_inter\').hide();
                }
                $(\'#id_rdv\').val(calEvent.id);
                $(\'#client\').html(calEvent.title);

                var description = calEvent.description;
                var tab = description.split("#");
                $(\'#date_heure\').val(tab[0]);
                $(\'#date_heure_fin\').val(tab[1]);
                if(tab[5] == 1)
                    $(\'#lieu_1\').attr(\'checked\', \'checked\');
                else
                    $(\'#lieu_2\').attr(\'checked\', \'checked\');
                $(\'#edit_rdv_modal\').modal(\'show\');
            },
            events: [';
        foreach($this->tab_events AS $event) {
            $dollar .= '{
                id: \''.$event['id'].'\',
                title: \''.rtrim($event['title']).'\',
                start: new Date('.$event['start_year'].', '.intval($event['start_month']).', '.intval($event['start_day']).', '.intval($event['start_hour']).', '.intval($event['start_minute']).'),
                end: new Date('.$event['end_year'].', '.intval($event['end_month']).', '.intval($event['end_day']).', '.intval($event['end_hour']).', '.intval($event['end_minute']).'),
                description: \''.$event['description'].'\',
                backgroundColor: \''.$event['color'].'\',
                allDay: false,
            },';
        }
            $dollar .= '
            ]
        });
        
         ';

        return $dollar;
    }

    private function script()
    {

        $this->script ='
        <script>
    /*
     * pages_calendar.js
     *
     * Demo JavaScript used on dashboard and calendar-page.
     */

    "use strict";

        function SetAjax(event)
        {
            //if(end== true)
               // var d = event.end.toString();
            //else

            var tab_mois = {Jan:"01", Feb:"02", Mar:"03", Apr:"04", May:"05", Jun:"06", Jul:"07", Aug:"08", Sep:"09", Oct:"10", Nov:"10", Dec:"12"};

            var d = event.start.toString();
            var tab_date = d.split(" ");
            var num_mois = tab_mois[tab_date[1]];
            var num_jour = tab_date[2];
            var annee = tab_date[3];
            var tab_heure = tab_date[4].split(":");
            var heure = tab_heure[0];
            var min = tab_heure[1];

            var f = event.end.toString();
            var tab_datef = f.split(" ");
            var num_moisf = tab_mois[tab_date[1]];
            var num_jourf = tab_date[2];
            var anneef = tab_date[3];
            var tab_heuref = tab_datef[4].split(":");
            var heuref = tab_heuref[0];
            var minf = tab_heuref[1];

            var descr = event.description;
            var tab_descr = descr.split("#");


            //alert(event.description);

            event.description = num_jour+\'/\'+num_mois+\'/\'+annee+\' \'+heure+\':\'+min+\'#\'+num_jourf+\'/\'+num_moisf+\'/\'+anneef+\' \'+heuref+\':\'+minf+\'#\'+tab_descr[2]+\'#\'+tab_descr[3]+\'#\'+tab_descr[4]+\'#\'+tab_descr[5];



            //alert(\'new_time=\' + event.start.toString()+\'&new_end=\'+ event.end.toString()+\'&id_rdv=\'+event.id);
            $.ajax({
                url : \'./ajax/modifier_rdv.php\',
                type : \'GET\',
                data : \'new_time=\' + event.start.toString()+\'&new_end=\'+ event.end.toString()+\'&id_rdv=\'+event.id,
                dataType : \'html\'
            });
        }

        $(document).ready(function(){
        
            ';
            if(!$this->not_load){
                $this->script .= $this->getReady();
            }

            $this->script .= '
        });
   
        </script>';
    }

    public function __toString()
    {
        $this->widget->setContent('<div id="'.$this->id.'"></div>', $this->box);
        $this->script();

        $edit_rdv_modal = new Modal('Modifier un rendez-vous', 'edit_rdv_modal');
        $edit_rdv_modal->setSubmitButton('form_edit', 'Modifier', 'btn-edit');
        $edit_rdv_modal->setCancelButton('Annuler', 'btn-cancel');
        $edit_rdv_modal->setDeleteButton('Supprimer', '#encours', 'btn-delete');

        $edit_rdv_form = new FormLayout('Saisie');
        $edit_rdv_form->setFormControls('form_edit');

        $edit_rdv_form->addLine('Client', '<div id="client"></div>');

        $id_rdv = new Hidden('id_rdv', 'id_rdv');
        $ecart = new Hidden('ecart', 'ecart');
        $ecart->setValue(3600);

        $date_heure = new Text('date_heure', 'date_heure');
        $date_heure->dataMask('99/99/9999 99:99');
        $date_heure->dateTimePicker('set_end()');
        $date_heure->onKeyUp("set_end();");
        $edit_rdv_form->addLine('Date heure début', $id_rdv.$ecart.$date_heure);

        $date_heure_fin = new Text('date_heure_fin', 'date_heure_fin');
        $date_heure_fin->dataMask('99/99/9999 99:99');
        $date_heure_fin->dateTimePicker('set_ecart();');
        $date_heure_fin->onKeyUp("set_ecart()");
        $edit_rdv_form->addLine('Date heure fin', $date_heure_fin);

        $lieu_1 = new Radio('lieu', '1', 'lieu_1');
        $lieu_2 = new Radio('lieu', '2', 'lieu_2');

        $edit_rdv_form->addLine('Lieu', $lieu_1.' chez le client<br />'.$lieu_2.' à l\'atelier', false, 'lieux_cal');

        $link_inter = new Button('Voir l\'intervention', "#", '', 'link_inter_btn');
        $link_inter->setClasse('btn-primary');
        //$link_inter = new Label('primary', 'Voir l\'intervention', '#');
        $edit_rdv_form->addLine('', $link_inter, false, 'link_inter');

        $edit_rdv_modal->setContent($edit_rdv_form, false);

        return $edit_rdv_modal->getModalHtml().$this->script.$this->widget->getHTML();
    }
}