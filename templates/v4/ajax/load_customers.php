<?php session_start();
require_once('../templates/v4/classes/includes.php');



$widget_result = new WidgetBox('Liste des clients trouvés', 6);


$table_result = new HtmlTable('', 'table table-hover bordered-table');
$table_result->addTSection('thead');
$table_result->addRow();
$table_result->addCell('Choisir', '', 'thead');
$table_result->addCell('Nom', '', 'thead');
$table_result->addCell('Adresse', '', 'thead');
$table_result->addTSection('tbody');
while ($row = $db->Row())
{
    $table_result->addRow();
    ($row->pro_part == 1)? $color = 'warning': $color = 'success';

    if($_GET['no_change_page']) {
        $num = new Label($color, '<span class="fa fa-arrow-right"></span>', '#');
        $num->setOnclick($_GET['reload_function'].'('.$row->id.')');
    }
    else
        $num = new Label($color, '<span class="fa fa-arrow-right"></span>', $_GET['redirect_link'].$row->id);



    $table_result->addCell($num);
    $table_result->addCell($row->nom. ' '.$row->prenom.'<br />Fixe : '.$row->fixe.'<br />Portable : '.$row->portable);
    $table_result->addCell($row->adresse.' '.$row->cp.' '.$row->ville);

}
$widget_result->setContent($table_result);

echo $widget_result;

?>
