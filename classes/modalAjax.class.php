<?php session_start();
//echo TEMPLATE_NAME;
if(TEMPLATE_NAME AND TEMPLATE_NAME != "v4")
    require_once('./templates/'.TEMPLATE_NAME.'/classes/Modal.class.php');

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/08/2017
 * Time: 09:57
 */
class modalAjax
{

    private $modul;
    private $action;
    private $modal;
    private $settings;
    private $debug;
    private $id_modal;

    public function __construct($modul, $action, $id='')
    {
        $this->modul = $modul;
        $this->action = $action;
        if($id)
            $this->id_modal = $modul.'_'.$action.'_'.$id;
        else
            $this->id_modal = $modul.'_'.$action;



        $this->modal = new Modal('En cours...', $this->id_modal);

        $this->modal->setContent('<div style="text-align: center"><img src="./templates/v4/img/cloud_load.gif" width="100%;" /></div>');
        //$this->modal->setWidth('70%');
        $this->oppenButton('Bouton d\'ouverture');
        $this->modal->setSubmitButton('', '', $this->id_modal.'_sbt_btn');
    }

    public function oppenButton($value, $class="btn-default btn-block", $full_width=false, $title='')
    {
        $this->openButton($value, $class, $full_width, $title);
    }

    public function openButton($value, $class="btn-default btn-block", $full_width=false, $title='')
    {
        $this->modal->openButton($value, $class, $full_width, $title);
    }

    public function settings( $settings)
    {
        foreach ($settings AS $key => $value) {
            if (is_array($value) OR is_object($value))
                $this->settings .= '&' . $key . '=' . urlencode(serialize($value));
            else
                $this->settings .= '&' . $key . '=' . urlencode($value);
        }

       // $this->settings = urlencode($this->settings);
    }

    /**
     * @param mixed $debug
     */
    public function setDebug()
    {
        $this->debug = true;
    }

    private function script()
    {
        $js = '
        <script>
        
        $("#'.$this->id_modal.'").on("hidden.bs.modal", function () {
            var mymodal = $( "#'.$this->id_modal.'" );
            mymodal.find(\'.modal-body\').hide().html(\'<div style="text-align: center"><img src="./templates/v4/img/cloud_load.gif" width="100%;" /></div>\').show();                 
             mymodal.find(\'.modal-title\').html("En cours...");
             
              mymodal.find(\'#modal-header-'.$this->id_modal.'\').css(\'background-color\', \'#36C6D3=\');
              
             if($(window).width() > \'1024\')
                $(\'.'.$this->id_modal.'-custom-class\').width(\'40%\');
             //else
                //$(\'.'.$this->id_modal.'-custom-class\').width(\'98%\');
                
              function match_media_callback2(media) {
                          if(media.matches) {
                            $(\'.'.$this->id_modal.'-custom-class\').animate({width: \'40%\'}, \'slow\');
                          }
                          else
                              $(\'.'.$this->id_modal.'-custom-class\').animate({width: \'98%\'}, \'slow\');
                        }    
                        
                        window.matchMedia("screen and (min-width: 1024px)").addListener(match_media_callback2);  
                
             mymodal.find(\'.modal-footer\').show(\'slow\');
        });
            $( "#'.$this->id_modal.'" ).on(\'shown.bs.modal\', function(){
            //alert(\'ok\');
                
                           
               '.$this->id_modal.'_modal_load();
               // mymodal.find(\'.modal-body\').html();
                
                      
            });
                            
            function '.$this->id_modal.'_modal_load()
            {
                var mymodal = $( "#'.$this->id_modal.'" );
                
                
            
                 mymodal.find(\'.modal-body\').hide().html(\'<div style="text-align: center"><img src="./templates/v4/img/cloud_load.gif" width="100%;" /></div>\').show();                 
                 mymodal.find(\'.modal-title\').html("En cours...");
                 
                 
                 $.ajax({
                    url: \'./ajax/modals.php\',
                    type: \'POST\',
                    data: \'modul='.$this->modul.'&action='.$this->action.'&id_modal='.$this->id_modal.$this->settings.'\',
                    dataType: \'json\',
                    complete: function (resultat, statut) {
                        
                        
                        
                        ';
                        if($this->debug)

                            $js .= 'mymodal.find(\'.modal-body\').hide().html(\'<div class="row"><div class="col-md-12">\'+resultat.responseText+\'</div></div>\').fadeIn(\'slow\');
                                    //alert(resultat.responseText);';
                        $js .= '
                        var jsonc = jQuery.parseJSON(resultat.responseText);
                        
                        mymodal.find(\'.modal-body\').hide().html(\'<div class="row"><div class="col-md-12">\'+jsonc.content+\'</div></div>\').fadeIn(\'slow\');
                        mymodal.find(\'.modal-title\').hide().html(jsonc.title).fadeIn(\'slow\');
                        //alert(jsonc.form_id);
                        $(\'#'.$this->id_modal.'_sbt_btn\').attr("onclick", "$(\'#"+jsonc.form_id+"\').submit();");
                        
                        //alert(jsonc.width);
                        if(jsonc.width != \'\' && $(window).width() > "1024")
                            $(\'.'.$this->id_modal.'-custom-class\').animate({width: jsonc.width}, \'slow\');
                        //else
                            //$(\'.'.$this->id_modal.'-custom-class\').animate({width: "98%"}, \'slow\');
                            
                        if(jsonc.color)
                            mymodal.find(\'#modal-header-'.$this->id_modal.'\').animate({\'background-color\': jsonc.color}, \'slow\');
                        
                        if(jsonc.hidebuttons)
                           mymodal.find(\'.modal-footer\').hide(\'slow\');
                        else 
                            mymodal.find(\'.modal-footer\').show(\'slow\');
                          
                        function match_media_callback(media) {
                          if(media.matches) {
                            $(\'.'.$this->id_modal.'-custom-class\').animate({width: jsonc.width}, \'slow\');
                          }
                         
                        }    
                        
                        window.matchMedia("screen and (min-width: 1024px)").addListener(match_media_callback);
                        $(\'.date-picker\').datepicker();
                        ComponentsDateTimePickers.init(); 
                        autocomplete_ville.init();
                        $(\'.make-switch\').bootstrapSwitch();
                        /*$(\'.select2\').select2();*/
    
                    }
                });
            }
        </script>';
        return $js;
    }

    public function getModalOpen()
    {
        return $this->modal->getOpenHtml();
    }

    public function getModalHtml()
    {
        return $this->modal->getModalHtml().$this->script();
    }

    public function openNow()
    {
        return $this->modal->openNow();
    }

    public function getHref()
    {
        return $this->modal->getAhref();
    }

    public function __toString()
    {
        return (string) $this->modal.$this->script();
    }
}