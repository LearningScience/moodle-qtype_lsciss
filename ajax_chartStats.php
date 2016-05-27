<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * **Description here**
 *
 * @package   qtype_lsciss
 * @copyright 2016 Learning Science Ltd https://learnsci.co.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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