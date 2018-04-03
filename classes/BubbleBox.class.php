<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 29/09/2015
 * Time: 14:25
 */

class BubbleBox {

    private $html;

    public function __construct($text)
    {

        $text = str_replace("'", "&apos;", $text);
        $text = str_replace("\"", "&quot;", $text);
        if(is_file('./templates/mango/classes/BubbleBox.php'))
        {
            ob_start();
            include('./templates/mango/classes/BubbleBox.php');
            $this->html .= ob_get_contents();
            ob_end_clean();
        }

    }

    public function __toString()
    {
        return $this->html;
    }
} 