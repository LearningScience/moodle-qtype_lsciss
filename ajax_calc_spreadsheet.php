<?php

require_once('Lsspreadsheet.php');
$json = $_POST['data'];

$spreadSheet = get_spreadsheetObject($json);
$objPHPExcel = convert_spreadsheet_to_excel($spreadSheet);
$jsonCalc = get_calculated_sheet_json($spreadSheet,$objPHPExcel);

echo(json_encode($jsonCalc));

?>