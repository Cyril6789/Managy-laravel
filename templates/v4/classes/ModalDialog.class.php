<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 10/02/2016
 * Time: 15:03
 */
class ModalDialog
{

    private $id;
    private $buttonValue = 'Bouton';
    private $modalTitle = 'Titre de la Modal';
    private $content;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function buttonValue($value)
    {
        $this->buttonValue = $value;
        $this->modalTitle = $value;
    }

    public function setModalTitle($title)
    {
        $this->modalTitle = $title;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function __toString()
    {
        $bouton = new Button($this->buttonValue, '#'.$this->id);
        $bouton->data_toggle('modal');
        $bouton->setFullWidth();

        $html = '
<!-- Modal dialog -->
<div class="modal fade" id="'.$this->id.'">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">'.$this->modalTitle.'</h4>
            </div>
            <div class="modal-body">
                '.$this->content.'
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Save changes</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->';

        return $bouton->getHTML().' '.$html;

    }

}