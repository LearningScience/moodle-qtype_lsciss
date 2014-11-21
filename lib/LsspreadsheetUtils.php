<?php
namespace Learnsci;

class LsspreadsheetUtils {

	public function __construct() {

	}

	public function getObjectFromLsspreaddata($lsspreaddata_string_from_db) {
		$json = json_decode($lsspreaddata_string_from_db, true);
		//the javascript editor wraps the data in an array which represents worksheets
		//we only use 1 worksheet so only need the 0 index of the array
		$json = $json[0]['cell'];

		$spreadsheet = array();

		foreach ($json as $cellref => $cell) {

			$lsspreadsheetCell = new LsspreadsheetCell();
			$lsspreadsheetCell->initCellFromJsonObject($cell);
			$spreadsheet[$cellref] = $lsspreadsheetCell;
		}

		return $spreadsheet;
	}

	/**
	 * Creates a PHPExcel spreadsheet in memory that has all the right forumalae in
	 * so that the student input data can be added in later
	 * @param <type> $spreadsheet
	 * @return <type>
	 */
	public function create_excel_marking_sheet_from_spreadsheet($spreadsheet, $doClone = true) {

		$markingExcel = new \PHPExcel();

		\PHPExcel_Calculation::getInstance()->clearCalculationCache();

		foreach ($spreadsheet as $cellref => $cell) {
			//This adds all the formulas to the spreadsheet but not numbers!
			if ($cell->formula != "") {
				$markingExcel->getActiveSheet()->setCellValue($cell->getExcelref(), strtoupper($cell->formula));
			}
		}

		\PHPExcel_Calculation::getInstance()->clearCalculationCache();
		if ($doClone) {
			$ret = clone ($markingExcel);
		} else {
			$ret = $markingExcel;
		}
		return $ret;
	}

	/**
	 * Was previously get_spreadsheet_table
	 * @param  [type]  $excel              PHP Excel obj
	 * @param  [type]  $nameprefix              [description]
	 * @param  string  $json_chart_instructions [description]
	 * @param  string  $lschartdata             [description]
	 * @param  Object  $options             [description]
	 * @return [type]                           [description]
	 */
	public function getTakeTableFromLsspreaddata($lsspreaddata, $nameprefix = '', $options, $qa, $graded, $feedbackStyles, $json_chart_instructions = "", $lschartdata = "") {

		$lschart = new LsspreadsheetChart();
		//this is the method that draws the question that the student actually sees.

		$metadata = $this->get_metadataObject($lsspreaddata);

		$spreadSheet = $this->getObjectFromLsspreaddata($lsspreaddata);

		$htmltable = "";

		if ($lschartdata !== "") {
			//$htmltable .= $lschart->get_chart_javascript($question->id, $CFG->wwwroot, $json_chart_instructions, $lschartdata);
		}
		$htmltable .= "<div class=\"lsspreadsheet_table\"><table>";
		for ($row = 0; $row < $metadata->rows; $row++) {
				$htmltable .= '<tr>';
			for ($col = 0; $col < $metadata->columns; $col++) {
				$rowind = "r" . $row;
				$colind = "c" . $col;
				$cellref = 'table0_cell_' . $colind . '_' . $rowind;

				$cellname = '';

				if (isset($spreadSheet[$cellref])) {
					$cell = $spreadSheet[$cellref];
					$cellname = $nameprefix . $cellref;
					$cell->response = $qa->get_last_qt_var($cellref);
					if(array_key_exists($cellref, $graded)){
						$cell->iscorrect = $graded[$cellref]->iscorrect;
						$cell->correctanswer = $graded[$cellref]->correctanswer;
						if($cell->iscorrect === true){
							$cell->feedbackClass = $feedbackStyles['correctFeedbackClass'];
							$cell->feedbackImage = $feedbackStyles['correctFeedbackImage'];
						} else if($cell->iscorrect === false) {
							$cell->feedbackClass = $feedbackStyles['wrongFeedbackClass'];
							$cell->feedbackImage = $feedbackStyles['wrongFeedbackImage'];
						}
					}
				} else {
					$cell = new LsspreadsheetCell();
					$cell->response = $qa->get_last_qt_var($cellref);
				}
				$htmltable .= $cell->getTdForCell($cellname, $metadata->columns, $options->readonly);
			}
			$htmltable .= "\n</tr>\n";
		}
		$htmltable .= "</table></div>";

		if ($lschartdata !== "") {
			//$htmltable .= $lschart->get_chart_html($question->id, $CFG->wwwroot);
		}

		return $htmltable;
	}


	/**
 * @brief function to extract the metadata
 */
	public function get_metadataObject($lsspreaddata) {
		$json = json_decode($lsspreaddata)[0];
		return $json->metadata;
	}

	/**
 * @brief function to extract the metadata
 */
	public function getChartDataObject($lsspreaddata) {
		$json = json_decode($lsspreaddata)[0];
		return $json->chartdata;
	}

	public function convert_spreadsheet_to_excel($spreadSheet) {

		$objPHPExcel = new \PHPExcel();

		foreach ($spreadSheet as $cellref => $cell) {

			if ($cell->formula != "") {
				$val = strtoupper($cell->formula);
			} else {
				$val = $cell->textvalue;
			}

			$objPHPExcel->getActiveSheet()->setCellValue($cell->getExcelref(), $val);
		}

		return (clone $objPHPExcel);
	}

}
