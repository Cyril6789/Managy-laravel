<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 22/02/2016
 * Time: 11:05
 */
class FormLayout
{
    private $widget;
    private $form;
    private $table = array();
    private $i;
    private $button='';
    private $id;
    private $label_width=3;
    private $content_width=9;
    private $box;
    private $add_content='';

    public function __construct($title, $width = 12, $box=true)
    {
        $this->widget = new WidgetBox($title, $width);
        $this->box = $box;
    }

    public function setFormControls($id, $action='', $method='post')
    {
        $this->id = $id;
        $this->form = new Form($id, $action, $method);
    }

    public function file()
    {
        $this->form->file();
    }

    public function addContent($content)
    {
        $this->add_content = $content;
    }

    public function setWidth($label, $content='')
    {
        $this->label_width = $label;
        if(!$content)
            $this->content_width = 12 - $label;
        else
            $this->content_width = $content;
    }

    public function addLine($label, $contenu, $required=false, $id='', $style='', $class='')
    {
        $this->table[$this->i]['label'] = $label;
        $this->table[$this->i]['contenu'] = $contenu;
        $this->table[$this->i]['required'] = $required;
        $this->table[$this->i]['div'] = $id;
        $this->table[$this->i]['style'] = $style;
        $this->table[$this->i]['class'] = $class;
        $this->i++;
    }

    public function setValueButton($value='Submit', $name='')
    {
        $this->button = '
    <div class="form-actions">
        <input type="submit" value="'.$value.'" class="btn btn-primary pull-right" name="'.$name.'">
    </div>';
    }

    private function script()
    {
        return '
<script>
        "use strict";

        $(document).ready(function(){

            //===== Validation =====//

            $.extend( $.validator.defaults, {
		invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var message = errors == 1
                        ? \'You missed 1 field. It has been highlighted.\'
                        : \'You missed \' + errors + \' fields. They have been highlighted.\';
                    noty({
					text: message,
					type: \'error\',
					timeout: 2000
				});
			}
            },
		errorPlacement: function(error, element) {
                if (element.attr(\'type\') === "file" && element.data(\'style\') === "fileinput"){
                    error.appendTo(element.closest("div.fileinput-holder").parent(\'div\'));
                } else {
                    error.insertAfter(element)
			 }
            }
	});

	$("#'.$this->id.'").validate();
</script>
	';
    }

    public function __toString()
    {
        $liste = '';
        foreach($this->table AS $line)
        {

            $liste .= '
            <div class="form-group '.$line['class'].'" id="'.$line['div'].'" style="'.$line['style'].'">
                <label class="col-md-'.$this->label_width.'  control-label">'.$line['label'];

            if($line['required'])
            $liste .= '<span class="required">*</span>';

            $liste .= '</label>
                <div class="col-md-'.$this->content_width.'">
                    '.$line['contenu'].'
                </div>
            </div>
            ';
        }

        if(empty($this->form))
            $html = $liste.$this->button;
        else
        {
            $this->form->setContent($liste.$this->add_content.$this->button);
            $html = $this->form->getHTML();
        }

        $this->widget->setContent($html, $this->box);

        return $this->widget->getHTML();



    }


}