<?php


require_once('Lsspreadsheet.php');
require_once('LsspreadsheetChart.php');

$lschart = new LsspreadsheetChart();

$json = $_POST['data'];
//$json = json_encode($json);

$spreadsheet = get_spreadsheetObject($json);

$lschart->create_chart_from_lsspreadsheet($spreadsheet);
echo(json_encode($data));
?>
