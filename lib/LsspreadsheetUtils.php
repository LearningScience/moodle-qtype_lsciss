<?php
namespace Learnsci;

class LsspreadsheetUtils {

	public function __construct() {

	}

	public function decodeLsspreaddataJsonString($jsonString) {
		if (strpos($jsonString, '\"')) {
			$jsonString = stripslashes($jsonString);
		}
		$json = json_decode($jsonString);
		return $json;
	}

	public function getObjectFromLsspreaddata($rawdata) {
		$json = $this->decodeLsspreaddataJsonString($rawdata);
		$lsspreaddata = $this->convert_rawdata_from_zero_array_lsspreaddata($json);
		$spreadsheet = $this->convert_lsspreaddata_json_to_object($lsspreaddata);

		return $spreadsheet;
	}

	public function convert_rawdata_from_zero_array_lsspreaddata($rawdata) {
		$lsspreaddata = '';
		$rawdata = $rawdata['0'];
		$tempdata = get_object_vars($rawdata->cell);
		foreach ($tempdata as $key => $ob) {
			$lsspreaddata[$key] = get_object_vars($ob);
		}

		return $lsspreaddata;
	}

	/**
	 * @brief function to return a spreadsheet object
	 *
	 * Function uses the object passed from the JSON and changes it to a
	 * more useful object for the PHP processing.  Note the r,c and row,col pairs
	 * these are handy for addressing either the javascript cells or the PHPExcel
	 * cells which differ in the row number.  Javascript interface has a row 0,
	 * Excel starts at 1.
	 *
	 * @param <type> $jsonString
	 * @return <type>
	 */
	public function convert_lsspreaddata_json_to_object($ssdata) {

		$spreadSheet = array();

		foreach ($ssdata as $cellref => $cell) {

			$lsspreadsheetCell = new LsspreadsheetCell();

			$lsspreadsheetCell->col = $cell['col'];
			$lsspreadsheetCell->row = $cell['row'];

			$lsspreadsheetCell->textvalue = $cell['textvalue'];
			$lsspreadsheetCell->formula = $cell['formula'];
			$lsspreadsheetCell->feedback = str_replace("'", "\\'", $cell['feedback']);
			$lsspreadsheetCell->labelalign = "";
			$lsspreadsheetCell->marks = 0;
			$lsspreadsheetCell->additional = array();

			if (isset($cell['chart'])) {
				$lsspreadsheetCell->chart = $cell['chart'];
			}
			$celltype = $cell['celltype'];
			$range = $cell['rangetype'];

			if ($celltype !== "") {
				$celltype = explode("_", $celltype);
				$lsspreadsheetCell->celltype = $celltype[0];

				if ($lsspreadsheetCell->celltype == "Label") {
					if (isset($celltype[1])) {
						$lsspreadsheetCell->labelalign = $celltype[1];
					} else if (!isset($celltype[1]) or $celltype[1] == "1") {
						$lsspreadsheetCell->labelalign = "left";
					}
				} else if ($lsspreadsheetCell->celltype == "CalcAnswer") {
					$lsspreadsheetCell->marks = $celltype[1];
					if ($range === "") {
						//Setting the defualt range is to make sure that any questions set with
						//the earliest versions of the javascript interface are OK
						$range = "AbsoluteRange_0";
					}
				} else {
					$lsspreadsheetCell->additional = $celltype[1];
				}
			} else {
				$lsspreadsheetCell->celltype = null;
				$lsspreadsheetCell->marks = null;
			}
			if ($range !== "") {
				//range data stored in one string using "_" as sep
				$range = explode("_", $range);
				$lsspreadsheetCell->rangetype = $range[0];
				$lsspreadsheetCell->rangeval = $range[1];
			} else {
				$lsspreadsheetCell->rangetype = null;
				$lsspreadsheetCell->rangeval = null;
			}

			$spreadSheet[$cellref] = $lsspreadsheetCell;
		}

		return ($spreadSheet);
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
						// var_dump($cellref);
						// var_dump($cell);
						// var_dump($graded[$cellref]);
					}
				} else {
					$cell = new LsspreadsheetCell();
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
		$rawdata = json_decode($lsspreaddata);
		$rawdata = $rawdata['0'];
		$metadata = $rawdata->metadata;
		return $metadata;
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
