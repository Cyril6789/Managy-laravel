<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 19/04/2017
 * Time: 16:28
 */
class DropdownMenu
{
    private $icon;
    private $id;
    private $link;
    private $badge;
    private $title;
    private $texte_view_all='';
    private $link_view_all='';
    private $scroller_height;
    private $tab_lines;
    private $compteur=0;
    private $original_title;
    private $target_blank;
    private $large='';
    private $hidden='';

    public function __construct($icon, $valeur_badge='', $color_badge='danger', $link='javascript:;', $id='', $original_title='', $target_blank=false)
    {
        $this->icon = $icon;
        $this->id = $id;
        $this->link = $link;
        if($valeur_badge)
            $this->badge = new Badge($valeur_badge, $id, $color_badge);
        else
            $this->badge = '&nbsp;';
        $this->setScrollerHeight(250);
        $this->tab_lines = Array();
        if($target_blank)
            $this->target_blank= 'target="_blank"';
    }

    /**
     * @param string $hidden
     */
    public function setHidden($hidden='xs')
    {
        $this->hidden = 'hidden-'.$hidden;
    }

    public function setLarge()
    {
        $this->large = ' large';
    }

    public function setTitle($title, $link_view_all='', $text_view_all='Tout voir')
    {
        $this->title = $title;
        $this->texte_view_all = $text_view_all;
        $this->link_view_all = $link_view_all;
    }

    public function setScrollerHeight($scroller_height)
    {
        $this->scroller_height = $scroller_height;
    }

    public function addLine($text, $icon, $time, $link='javascript:;', $color_label='success')
    {
        $this->tab_lines[$this->compteur]['text'] = $text;
        $this->tab_lines[$this->compteur]['label'] = new Label($color_label, new Font($icon));
        $this->tab_lines[$this->compteur]['time'] = $time;
        $this->tab_lines[$this->compteur]['link'] = $link;
        $this->compteur++;
    }

    public function __toString()
    {
        if($this->link == 'javascript:;')
            $data_toggle = 'dropdown';
        else
            $data_toggle = '';
        $html = '
<li class="dropdown dropdown-extended '.$this->hidden.' dropdown-notification" id="'.$this->id.'">
    <a href="'.$this->link.'" '.$this->target_blank.' data-placement="bottom" data-original-title="test" class="dropdown-toggle" data-toggle="'.$data_toggle.'" data-hover="dropdown" data-close-others="true">
        '.new Font($this->icon).$this->badge.'
    </a>';

            $html .= '
    <ul class="dropdown-menu'.$this->large.'">';

            if ($this->title) {
                $html .= '
        <li class="external">
            <h3>
                ' . $this->title . '
            </h3>';
                if ($this->texte_view_all AND $this->link_view_all)
                    $html .= '
            <a href="' . $this->link_view_all . '">' . $this->texte_view_all . '</a>';
                $html .= ' 
        </li>';
            }

            if(!count($this->tab_lines))
                $this->scroller_height = 0;

                $html .= '
        <li>
            <ul class="dropdown-menu-list scroller" style="height: ' . $this->scroller_height . 'px;" data-handle-color="#637283">';

                foreach ($this->tab_lines AS $line) {
                    $html .= '
                <li>
                    <a href="' . $line['link'] . '">
                        <span class="time">' . $line['time'] . '</span>
                        <span class="details">
                            ' . $line['label'] . ' ' . $line['text'] . '
                        </span>
                    </a>
                </li>';
                }

            $html .= '
            </ul>
        </li>
    </ul>';

        $html .= '
</li>';

        return $html;
    }

}