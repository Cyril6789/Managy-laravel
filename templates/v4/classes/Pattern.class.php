<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 06/12/2016
 * Time: 20:58
 */
class Pattern
{
    private $id;
    private $id_hidden_field;
    private $value;
    private $disabled = false;

    public function __construct($id, $id_hidden_field, $value='')
    {
        $this->id = $id;
        $this->id_hidden_field = $id_hidden_field;
        $this->value = $value;
    }

    public function disable()
    {
        $this->disabled = true;
    }

    public function __toString()
    {
        $html = '
        <div id="'.$this->id.'" class="pattern-holder direction">
                </div>

<script>

    (function(){


        var lock =new PatternLock(\'#'.$this->id.'\', {
            onDraw:function(pattern){
                $(\'#'.$this->id_hidden_field.'\').val(pattern);
            },
        });
        lock.setPattern(\''.$this->value.'\');
        ';
        if($this->disabled)
            $html .= '
        lock.disable();
        ';

        $html .= '
    }());
    ;
</script>';

        if($this->disabled)
            $hidden = '';
        else
            $hidden = new Hidden($this->id_hidden_field);


        return $html.$hidden;
    }
}