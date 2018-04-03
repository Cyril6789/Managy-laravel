<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 08/03/2016
 * Time: 09:30
 */
class Notify
{
    private $text;
    private $type;
    private $timeout;

    public function __construct($text, $type='success', $timeout=0)
    {
        $tab = Array('success', 'info', 'warning', 'error');


        if(in_array(strtolower($type), $tab))
            $this->type = strtolower($type);
        else
            $this->type = 'info';

        $this->text = addslashes($text);
        $this->timeout = $timeout * 2;
    }

    public function __toString()
    {

        $html = "
        <script>
        $(document).ready(function(){
            toastr.options = {
            \"closeButton\": false,
            \"debug\": false,
            \"positionClass\": \"toast-top-center\",
            \"onclick\": null,
            \"showDuration\": \"300\",
            \"hideDuration\": \"1000\",
            \"timeOut\": \"".$this->timeout."\",
            \"extendedTimeOut\": \"1000\",
            \"showEasing\": \"swing\",
            \"hideEasing\": \"linear\",
            \"showMethod\": \"fadeIn\",
            \"hideMethod\": \"fadeOut\"
        };
            Command: toastr[\"".$this->type."\"](\"".$this->text."\");
            
            
            
            
            
        });
        </script>
        ";

        /*
        $html = "
<script>
    $(document).ready(function(){
            noty({
                text: '".addslashes($this->text)."',
                type: '".$this->type."',
                layout: 'top',
                timeout: ".$this->timeout.",
            });
    });
    
    
    
    jQuery(document).ready(function() {
    UIToastr.init()
});
    
</script>
        ";*/

        return $html;
    }
}