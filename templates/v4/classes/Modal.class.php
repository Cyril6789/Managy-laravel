<?php


/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 06/03/2016
 * Time: 16:58
 */
class Modal
{
    protected $title;
    protected $modal_id;
    protected $cancel = "Annuler";
    protected $submit = "valider";
    protected $submit_id = '';
    protected $candel_id = '';
    protected $id_form='';
    protected $content='';
    protected $open_bouton;
    protected $open_link='';
    protected $display_button = true;
    protected $delete='';
    protected $delete_link='';
    protected $delete_id = '';
    protected $onclickButtonValue = '';
    protected $onclickButtonOnclick = '';
    protected $onclickButtonId = '';
    protected $disableClose = false;
    protected $css='';
    protected $headerbackgroundcolor;
    protected $dismiss = true;

    public function __construct($title, $modal_id)
    {
        $this->title = $title;
        $this->modal_id = $modal_id;
        $this->setHeaderBackgroundColor('#36C6D3');
        //$this->setWidth('70%');
    }

    public function setWidth($width)
    {
        $this->css = '    
        @media screen and (min-width: 768px) {
            .'.$this->modal_id.'-custom-class {
                width: '.$width. ';
            }
        }
    ';
    }

    public function getCss()
    {
        return $this->css;
    }

    public function openButton($value, $class="btn-default btn-block", $full_width=false, $title='')
    {
        $this->open_bouton = new Button($value, '#'.$this->modal_id, $title);
        $this->open_bouton->data_toggle('modal');
        $this->open_bouton->setClasse($class);
        if($full_width)
            $this->open_bouton->setFullWidth();
    }

    public function disableClose()
    {
        $this->disableClose = true;
    }

    public function openLink($value)
    {
        $this->open_link = '<a data-toggle="modal" href="#'.$this->modal_id.'">'.$value.'</a>';
    }

    public function getAhref()
    {
        return "#".$this->modal_id."\" data-toggle=\"modal";
    }



    public function noButton()
    {
        $this->display_button = false;
    }

    public function setSubmitButton($form_id='', $value='', $id='')
    {
        $this->id_form = $form_id;
        if(!empty($value))
            $this->submit = $value;
        $this->submit_id = $id;
    }

    public function setCancelButton($value, $id='')
    {
        $this->cancel = $value;
        $this->candel_id = $id;
    }

    public function setDeleteButton($value, $link='', $id='')
    {
        $this->delete = $value;
        $this->delete_link = $link;
        $this->delete_id = $id;
    }

    public function setOnclickButton($value, $onclick, $id='', $dismiss=true)
    {
        $this->onclickButtonValue = $value;
        $this->onclickButtonOnclick = $onclick;
        $this->onclickButtonId = $id;
        $this->submit = '';
        $this->dismiss = $dismiss;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getOpenHtml()
    {
        if(is_object($this->open_bouton))
            return $this->open_bouton->getHTML();
        else
            return $this->open_link;
    }

    public function openNow()
    {
        $html = "
<script>
    $(document).ready(function(){
        $('#".$this->modal_id."').modal('show');
    });

</script>";

        return $html;
    }

    public function setHeaderBackgroundColor($color)
    {
        $this->headerbackgroundcolor = $color;
    }

    public function getModalHtml()
    {

        $ligne = new Row($this->content);

        $html = '
<!-- Modal dialog -->
<div class="modal fade" id="'.$this->modal_id.'">
    <div class="modal-dialog '.$this->modal_id.'-custom-class">
        <div class="modal-content">
            <div class="modal-header" id="modal-header-'.$this->modal_id.'">';

        if(!$this->disableClose)
                $html .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-remove"></i></button>';
        $html .= '<h4 class="modal-title">'.$this->title.'</h4>
            </div>
            <div class="modal-body">
               '.$ligne.'
            </div>';
        if($this->display_button)
        {
            $html .= '
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="'.$this->candel_id.'" data-dismiss="modal">'.$this->cancel.'</button>';
            if(!empty($this->delete))
            {
                $html .= '
                <button type="button" class="btn btn-danger" data-dismiss="modal" id="'.$this->delete_id.'" onclick="window.location.href=\''.$this->delete_link.'\';">'.$this->delete.'</button>
                ';
            }

            if(!empty($this->submit))
            {
                $html .= '
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="' . $this->submit_id . '" onclick="$(\'#' . $this->id_form . '\').submit();">' . $this->submit . '</button>
            ';
            }

            if(!empty($this->onclickButtonValue))
            {
                if($this->dismiss)
                    $dismiss = 'data-dismiss="modal"';
                else
                    $dismiss = '';
                $html .= '
                <button type="button" class="btn btn-primary" '.$dismiss.' onclick="'.$this->onclickButtonOnclick.'" id="'.$this->onclickButtonId.'">' . $this->onclickButtonValue . '</button>
            ';
            }
        }
        $html .= '</div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->';

        return '
        <style>
        '.$this->css.' 
        
        .modal-title {
            font-weight: 600;
            font-size: 15px;
            color: #ffffff;
        }
        
        #modal-header-'.$this->modal_id.' {
            background-color: '.$this->headerbackgroundcolor.';
        }
        
        .modal-header .close {
            color: #ffffff;
        }
        </style>'
        .$html;
    }

    public function __toString()
    {

        return $this->getOpenHtml().$this->getModalHtml();
    }

}