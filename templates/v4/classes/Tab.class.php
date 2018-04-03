<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/02/2016
 * Time: 16:14
 */
class Tab
{
    private $i=1;
    private $id_tab;
    private $table = Array();
    private $position;
    private $full_width='';
    private $row='';
    private $width_nav=3;

    public function __construct($position='top', $id_tab='')
    {
        switch($position)
        {
            case 'right':
                $this->position = 'tabs-right';
                break;
            case 'left':
                $this->position = 'tabs-left';
                break;
            case 'below':
                $this->position = 'tabs-below';
                break;
            default :
                $this->position = '';
        }

        if(empty($id_tab))
            $this->id_tab = rand(1, 1000);
        else
            $this->id_tab = $id_tab;
    }

    /**
     * @param int $width_nav
     */
    public function setWidthNav($width_nav)
    {
        if($width_nav > 0 AND $width_nav < 13)
            $this->width_nav = (int) $width_nav;
    }

    public function fullWidth()
    {
        $this->full_width = 'tabbable-full-width';
    }

    public function setRowContent()
    {
        $this->row = 'row';
    }

    public function addPane($title, $content, $id_pane='')
    {
        $this->table[$this->i]['title'] = $title;
        $this->table[$this->i]['content'] = $content;
        if(empty($id_pane))
            $this->table[$this->i]['i'] = $this->i;
        else
            $this->table[$this->i]['i'] = $id_pane;
        $this->i++;
    }

    public function __toString()
    {
        $nav = '';
        $panes = '';
        $i=1;
        foreach($this->table AS $pane)
        {
            if($i == 1)
                $active = 'active';
            else
                $active = '';
            $nav .= '<li class="'.$active.'"><a href="#tab_'.$this->id_tab.'_'.$pane['i'].'" id="tab_'.$this->id_tab.'_'.$pane['i'].'_tab" data-toggle="tab">'.$pane['title'].'</a></li>';
            $panes .= '
                <div class="tab-pane '.$active.'" id="tab_'.$this->id_tab.'_'.$pane['i'].'">
                    '.$pane['content'].'
                </div>';
            $i++;
        }

        if($this->position == 'tabs-below')
            $nav = '
        <ul class="nav nav-tabs">
            '.$nav.'
        </ul>
            ';
        else
            $nav = '
                <ul class="nav nav-tabs ' . $this->position . '">
                    ' . $nav . '
                </ul>';



        $panes = '
            <div class="tab-content '.$this->row.'">
                '.$panes.'
            </div>';

        if($this->position == 'tabs-below')
            $html = $panes.'
            '.$nav;


        if($this->position == '')
            $html = $nav.'
            '.$panes;


        $width_pane = 12 - $this->width_nav;

        if($this->position == 'tabs-left')
            $html = '
                <div class="row">
                    <div class="col-md-'.$this->width_nav.' col-sm-'.$this->width_nav.' ">
                        '.$nav.'
                    </div>
                    <div class="col-md-'.$width_pane.' col-sm-'.$width_pane.' ">
                        '.$panes.'
                    </div>
                </div>';

        if($this->position == 'tabs-right')
            $html = '
                <div class="row">
                    <div class="col-md-'.$width_pane.' col-sm-'.$width_pane.' ">
                        '.$panes.'
                    </div>
                    <div class="col-md-'.$this->width_nav.' col-sm-'.$this->width_nav.' ">
                        '.$nav.'
                    </div>
                </div>';




        $html = '
        <div class="tabbable tabbable-custom '.$this->position.' '.$this->full_width.'">
            '.$html.'
        </div>';

        return $html;

    }




}