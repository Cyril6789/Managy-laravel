<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/07/2016
 * Time: 15:50
 */

class GraphsPies {

    private $id;
    private $tab_data = Array();
    private $i=0;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function addData($label, $value)
    {
        $this->tab_data[$this->i]['label'] = $label;
        $this->tab_data[$this->i]['value'] = $value;
        $this->i++;
    }


    private function script()
    {
        $html = '
<script>
    jQuery(document).ready(function() {
        AmCharts.makeChart("'.$this->id.'", {
                type:"pie", theme:"light", fontFamily:"Open Sans", color:"#888", dataProvider:[';

        foreach($this->tab_data AS $data)
        $html .= '{
                        country: "'.$data['label'].' ", value: '.$data['value'].'
                    }
                    ,';

        $html .= '

	],
            valueField:"value", titleField:"country", outlineAlpha:.4, depth3D:15, balloonText:"[[title]]<br><span style=\'font-size:14px\'><b>[[value]]</b> ([[percents]]%)</span>", angle:30, exportConfig:
                {
                    menuItems: [
                        {
                            icon: "/lib/3/images/export.png", format: "png"
                        }
                    ]
                }
            }
        );
        }

    );
</script>';
        return $html;
    }


    public function __toString()
    {

        $html = '<div id="'.$this->id.'" class="chart" style="height: 400px;"></div>';

        return $html.$this->script();
    }

} 