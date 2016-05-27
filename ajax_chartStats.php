<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */

require_once ('LsspreadsheetChartStats.php');
require_once ('Lsspreadsheet.php');


$data = json_decode($_GET['data']);

function get_formatted_stats_html($data) {
    $stats = LsspreadsheetChartStats::get_lsspreadsheet_stats($data->xseries, $data->yseries);
    return $stats;
}

if (isset($data->xseries)) {
    $stats = get_formatted_stats_html($data);
    echo json_encode($stats);

}else {
    echo "";
}

?>