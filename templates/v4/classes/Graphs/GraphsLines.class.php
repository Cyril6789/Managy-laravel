<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 18/02/2016
 * Time: 18:26
 */
class GraphsLines
{
    private $id;
    private $tab_data = Array();
    private $i=0;
    private $time_min;
    private $time_max;
    private $legend;
    private $first_color = '';
    private $tab_legend = Array();
    private $height;

    public function __construct($id, $height = '400px')
    {
        $this->id = $id;
        $this->height = $height;
    }

    public function addColor($color, $legend)
    {
        $this->legend = $this->parse_legende($legend);
        $this->tab_legend[$this->legend]['legend'] = $legend;
        $this->tab_legend[$this->legend]['color'] = $color;
        $this->i = 0;
        if(empty($this->first_color))
            $this->first_color = $color;
    }

    public function addData($value, $time)
    {
        $this->tab_data[$time][$this->legend] = $value;
        $this->i++;
    }

    private function parse_legende($str, $charset='utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        $str = str_replace(' ', '', $str);
        return $str;
    }

    private function script()
    {
        $html = '<script>

    jQuery(document).ready(function() {
         AmCharts.makeChart("'.$this->id.'", {
                type:"serial", theme:"light", pathToImages:App.getGlobalPluginsPath()+"amcharts/amcharts/images/", autoMargins:!1, marginLeft:30, marginRight:8, marginTop:10, marginBottom:26, fontFamily:"Open Sans", color:"#888", dataProvider:[
';
                    foreach ($this->tab_data AS $key  => $value)
                    {
                        //print_r($value);
                        $html .= '
                        {
                            year: "'.$key.'", ';
                            foreach ($value AS $k => $v ) {
                                //print_r($value);
                                $html .= $k . ':' . $v . ', ';
                            }
                            $html .= '
                        },
                        ';
                    }

                    $html .= '
                ],
                valueAxes:[
                    {
                    axisAlpha: 0, position: "left"
                    }
                ],
                startDuration:1, graphs:[
                ';
                    foreach ($this->tab_legend AS $key => $value)
                    {
                        $html .= '  {
                        balloonText: "<span style=\'font-size:13px;\'>[[title]] en [[category]] : <b>[[value]]</b> [[additional]]</span>", bullet: "round", dashLengthField: "dashLengthLine", lineThickness: 3, bulletSize: 7, bulletBorderAlpha: 1, bulletColor: "#FFFFFF", lineColor:"'.$value['color'].'", useLineColorForBulletBorder: !0, bulletBorderThickness: 3, fillAlphas: 0, lineAlpha: 1, title: "'.$value['legend'].'", valueField: "'.$key.'"
                    }, ';
                    }
                    $html .= '
                ],
                categoryField:"year", categoryAxis:
                {
                    gridPosition: "start", axisAlpha: 0, tickLength: 0
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


        $html = '<div id="'.$this->id.'" class="chart" style="height: '.$this->height.';"></div>';

        return $html.$this->script();
    }
}