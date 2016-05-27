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

namespace Learnsci;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Chart
 *
 * @author steve
 */
require_once(dirname(__FILE__) . '/../../../../config.php'); 
global $CFG; 

require_once(dirname(__FILE__) . '/../' . 'pChart/pData.php');
require_once(dirname(__FILE__) . '/../' . 'pChart/pChart.php');
// require_once ('src/Lsspreadsheet.php');
require_once (dirname(__FILE__) . '/../' . 'lib/ChartStats.php');

//display image
if ($loadChart == true){
$lschart = new Chart();

$plot = $lschart->xyplot($data);

header('Content-type: image/png');
imagepng($plot->Picture);
}
//endto beremoved
class Chart {

	public function __construct() {
	}

	public function create_chart_from_lsspreadsheet($spreadsheet) {
		$chartcellids = $this->get_chartcellids_from_lsspreadsheet($spreadsheet);
	}

	public function get_chart_javascript($qid, $wwwroot, $json_chart_instructions, $lschartdata) {
		$javascript = "";
		$javascript .= "<script type=\"text/javascript\"> if(typeof(lsspreadsheetdata)===\"undefined\"){var lsspreadsheetdata= new Array();};\n lsspreadsheetdata[" . $qid . "]={};lsspreadsheetdata[" . $qid . "].qid=" . $qid . "; lsspreadsheetdata[" . $qid . "].server=\"" . $wwwroot . "\";lsspreadsheetdata[" . $qid . "].chartmeta=" . json_encode($lschartdata) . ";\n lsspreadsheetdata[" . $qid . "].chartinstructions = " . $json_chart_instructions . "; </script>";
		$javascript .= "<script type=\"text/javascript\" src=\"" . $wwwroot . "/question/type/lsspreadsheet/js/jquery-1.4.2.min.js\"></script>";
		$javascript .= "<script type=\"text/javascript\" src=\"" . $wwwroot . "/question/type/lsspreadsheet/js/json2.js\"></script>";
		$javascript .= "<script type=\"text/javascript\" src=\"" . $wwwroot . "/question/type/lsspreadsheet/js/lsspreadsheet_chart.js\"></script>";
		$javascript .= "<script type=\"text/javascript\" >$(document).ready(function() {set_up_question(" . $qid . ");});</script>";
		return $javascript;
	}

	public function get_chart_html($qid, $wwwroot) {
		$html = "";
		$html .= '<div class="lsspreadsheetchart_div">';
		$html .= '<div class="lsspreadsheet_img">';
		$html .= '<img id="lsspreadsheetchart_resp' . $qid . '" src="" alt=""/>';
		$html .= '</div>';
		$html .= '<div id="lsspreadsheet_chart_stats">';
		$html .= '<table id="lsspreadsheet_chart_stats_table">';
		//$html .= '<thead><tr><th>Slope</th><th>Intercept</th><th>R<sup>2</sup></th></tr></thead>';
		$html .= '<thead><tr><th></th><th>Statistics</th></tr></thead>';
		$html .= '<tbody>';
		$html .= '<tr><th>Slope</th><td id="stats_slope_td_resp' . $qid . '"></td></tr>';
		$html .= '<tr><th>Intercept</th><td id="stats_intercept_td_resp' . $qid . '"></td></tr>';
		$html .= '<tr><th>R<sup>2</sup></th><td id="stats_rsquared_td_resp' . $qid . '"></td></tr>';
		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '</div></div>';

		return $html;
	}

	public function get_json_chart_instructions_from_lsspreadsheet($spreadsheet) {
		$chartcellids = $this->get_chartcellids_from_lsspreadsheet($spreadsheet);
		return json_encode($chartcellids);
	}

	public function get_chartcellids_from_lsspreadsheet($spreadsheet) {

		$chartcellids = array();

		foreach ($spreadsheet as $cellref => $cell) {

			if (isset($cell->chart) &&
				(
					($cell->celltype == "StudentInput") ||
					($cell->celltype == "CalcAnswer")
				)
			) {
				$charttype = $cell->chart;
				if ($charttype != "") {
					if (!isset($chartcellids[$charttype])) {
						$chartcellids[$charttype] = array();
					}

					$chartcellids[$charttype][] = $cellref;
				}
			}
		}
		return $chartcellids;
	}

	private function draw_axis_title($Chart) {

	}
	public function xyplot($data) {

		$lsstats = new ChartStats();
		$DataSet = new pData;
		$Chart = new pChart(300, 300);

		if (!is_array($data->xseries)) {
			return $Chart;
		}

		for ($i = 0; $i < count($data->xseries); $i++) {
			$DataSet->AddPoint(floatval($data->xseries[$i]), "Serie2");
			$DataSet->AddPoint(floatval($data->yseries[$i]), "Serie1");
		}

		$DataSet->SetSerieName("Trigonometric function", "Serie1");
		$DataSet->AddSerie("Serie1");
		$DataSet->AddSerie("Serie2");

		$DataSet->SetXAxisName($data->xaxistitle);
		$DataSet->SetYAxisName($data->yaxistitle);

// Initialise the graph
		// Prepare the graph area
		$Chart->setFontProperties(dirname(__FILE__) . '/../' . 'Fonts/tahoma.ttf', 10);

		$Chart->setGraphArea(55, 30, 270, 230);
		$DataDescription =$DataSet->GetDataDescription();
		$XSerieName = $DataDescription["Axis"]["X"];
		$YSerieName = $DataDescription["Axis"]["Y"];
		$Chart->drawXYScale($DataSet->GetData(), $DataSet->GetDataDescription(), "Serie1", "Serie2", 0, 0, 0, 0);
		$Chart->drawGraphArea(255, 255, 255, FALSE);

		$Chart->drawGrid(4, FALSE);

//Following function is a Learning Science add on!
		$Chart->drawXYPointsGraph($data->xseries, $data->yseries, $DataDescription, $YSerieName, $XSerieName, $PaletteID = 0, $BigRadius = 5, $SmallRadius = 2, $R2 = -1, $G2 = -1, $B2 = -1, $Shadow = TRUE);

//Following function is a Learning Science add on!

		$data->stats = $lsstats->get_lsspreadsheet_stats($data->xseries, $data->yseries);
		error_log("xyplot");
		error_log(print_r($data, true));
		$Chart->drawLineOfBestFit($data->stats->linebestfit->x1, $data->stats->linebestfit->y1, $data->stats->linebestfit->x2, $data->stats->linebestfit->y2);
		//$Test->setFontProperties("Fonts/subscript.ttf", 10);

		$Chart->clearShadow();
		$Chart->drawTitle(100, 15, $data->title, 0, 0, 0);

		return $Chart;
	}

}

?>
