<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/02/2016
 * Time: 14:19
 */
class Alert
{

    protected $removable;
    protected $type;
    protected $content;
    protected $size=12;
    protected $style;
    protected $id;

    protected function getHTML()
    {
        if($this->removable)
            $remove = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>';
        else
            $remove = '';

        $html = '
        <div class="alert alert-'.$this->type.'" '.$this->style.' id="'.$this->id.'">
            '.$remove.'
            '.$this->content.'
        </div>
        ';


        $col = new Col($this->size);
        $col->setContent($html);


        return $col;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setType($type)
    {
        $this->type = $type;
    }



    public function setStyle($style)
    {
        $this->style = $style;
    }

}