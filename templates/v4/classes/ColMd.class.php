<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 10/02/2016
 * Time: 08:28
 */

class ColMd {

    protected  $width;
    protected $html;

    public function __construct($width = 12)
    {
        $this->width = $width;
    }

    public function setContent($content)
    {

        $this->html = '
<div class="col-md-'.$this->width.'">
        '.$content.'
</div>';

    }

    public function __tostring()
    {
        if(empty($this->html))
            return '
<div class="col-md-'.$this->width.'">
    &nbsp;
</div>';
        else
            return $this->html;
    }

} 