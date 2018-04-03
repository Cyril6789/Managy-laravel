<?php session_start();

$widget = new WidgetBox('Tableau des interventions', 12);

$inters = new HtmlTable('', 'table table-bordered table-hover table-responsive datatable');
$inters->addTSection('thead');
$inters->addRow();
$inters->addCell('#', '', 'thead');
$inters->addCell('Client', '', 'thead');
$inters->addCell('Ouvert le', '', 'thead');
$inters->addCell('Couleur', '', 'thead');
$inters->addCell('Cloturé le', '', 'thead');
$inters->addTSection('tbody');

foreach ($tab_inters AS $value_inter)
{
    $inters->addRow('', array('onclick'=>'document.location=\'./intervention-'.$value_inter['id'].'\'', 'style'=>'cursor: pointer;'));

    if($value_inter['cloture'])
        $color = 'success';
    else
    {
        if($value_inter['pec'])
            $color = 'warning';
        else
            $color = 'danger';
    }

    $zero = '';
    $lg = strlen($value_inter['id']);
    for($i=$lg; $i<4; $i++)
        $zero .= '0';


    $num = new Label($color, $zero.$value_inter['id'], './i'.$value_inter['id']);

    $inters->addCell($num);

    $inters->addCell($value_inter['titre'].' '.$value_inter['name'].' '.$value_inter['lname'].'<br /><strong>'.$value_inter['projet'].' - '.$value_inter['matiere'].'</strong>');
    $inters->addCell(parse_date($value_inter['ouverture']));


    $inters->addCell($value_inter['couleur'].' (RAL : '.$value_inter['ral'].')<br />'.$value_inter['aspect']);

    if($value_inter['cloture'] )
        $inters->addCell(parse_date($value_inter['cloture']));
    else
        $inters->addCell('Non clôturé');
}

$widget->setContent($inters);


$row = new Row($widget);
echo $row;

?>