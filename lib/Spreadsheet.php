<?php
namespace Learnsci;

class Spreadsheet {


	public function __construct() {
				/**
		 * Caching is disabled to stop old values for cells being held in memory,
		 * less efficient on large spreadsheets but should be ok here.
		 *
		 * Note that that PHPExcel_Calculation instance is a singleton.
		 */
		\PHPExcel_Calculation::getInstance()->setCalculationCacheEnabled(false);

		$this->lsspreadsheetCellGrader = new CellGrader();
	}

	/**
	 * @brief function to extract the chart input ids
	 */
	public function get_chart_inputids($lsspreaddata) {
		$rawdata = json_decode($lsspreaddata);
		$spreadsheet = $this->getObjectFromLsspreaddata($lsspreaddata);

		foreach ($spreadsheet as $cellref => $cell) {

			$chartinputid = array();

			if (isset($cell->chart)) {
				$chartinputid[] = $cellref;
			}
		}

		return json_encode($chartinputid);
	}


	/**
	 * Creates a PHPExcel spreadsheet in memory that has all the right forumalae in
	 * so that the student input data can be added in later
	 * @param <type> $spreadsheet
	 * @return <type>
	 */
	public function create_excel_populated_all_moodle_inputs($responses, $doClone = true) {
		$moodleinput_excel = new \PHPExcel();

		//PHPExcel_Calculation::getInstance()->clearCalculationCache();

		foreach ($this->spreadsheet as $cellref => $cell) {

			if (in_array($cellref, array_keys($responses))) {

				$cellvalue = $responses[$cellref];

				if (!is_numeric($cellvalue)) {
					$cellvalue = "null";
				}

				if ($cellvalue == "") {
					$cellvalue = "null";
				}
			} else {
				$cellvalue = strtoupper($cell->formula);
			}

			$moodleinput_excel->getActiveSheet()->setCellValue($cell->getExcelref(), $cellvalue);
		}
		//PHPExcel_Calculation::getInstance()->clearCalculationCache();

		if ($doClone) {
			$ret = clone ($moodleinput_excel);
		} else {
			$ret = $moodleinput_excel;
		}
		return $ret;
	}



	public function get_field_names(){
		$calculatedCellNames = [];
		foreach ($this->spreadsheet as $key => $cell) {
			if(($cell->celltype === 'CalcAnswer') || (($cell->celltype === 'StudentInput'))){
				$calculatedCellNames[] = $key;
			}
		}
		return $calculatedCellNames;
	}

	public function method_mark_cell($moodleinput_excel, $cell_excelref, $cell_formula, $cell_rangetype, $cell_rangeval, $submitted_answer) {

		$inputvalue = $moodleinput_excel->getActiveSheet()->getCell($cell_excelref)->getCalculatedValue();
		$moodleinput_excel->getActiveSheet()->setCellValue($cell_excelref, strtoupper($cell_formula));
		$calculated_answer = $moodleinput_excel->getActiveSheet()->getCell($cell_excelref)->getCalculatedValue();

		$answer = $this->get_cell_correctness($submitted_answer, $calculated_answer, $cell_rangetype, $cell_rangeval);
		$moodleinput_excel->getActiveSheet()->setCellValue($cell_excelref, $inputvalue);
		return $answer;
	}

	// Counts the number of digits after the '.'
	public function get_num_decimals($value, $include_trailing_zeros) {
		$decimals = null;
		//Cast value to a string if it is not already
		if(is_string($value) !== true){
			$value = (string)$value;
		}
		//Trim trailing zeros if we do not want to count them
		if($include_trailing_zeros === true){
			$decimals = strlen(substr(strrchr($value, "."), 1));
		}else{
			$decimals = strlen(substr(strrchr(trim($value), "."), 1));
		}
		return $decimals;
	}

	public function get_cell_correctness($submitted_answer, $correct_answer, $rangetype, $rangeval, $correct_answer_string = "") {
		$num_decimals = $this->get_num_decimals($submitted_answer, false);
		if ($correct_answer_string === "") {
			if ($correct_answer > 0) {
				$correct_answer_string = round($correct_answer, $num_decimals);
			}else{
				$correct_answer_string = $correct_answer;
			}
		}

		$rangeval = $rangeval + 0.0;
		$submitted_answer = trim($submitted_answer);	

		switch ($rangetype) {
			case "SigfigRange":
				$answer = $this->lsspreadsheetCellGrader->getSigFigCellCorrectness($submitted_answer, $correct_answer, $rangeval, $correct_answer_string);
				break;

			case "DecimalRange":
				$answer = $this->lsspreadsheetCellGrader->getDecimalCorrectness($submitted_answer, $correct_answer, $rangeval, $correct_answer_string);
				break;

			case "PercentRange":
				$answer = $this->lsspreadsheetCellGrader->getPercentCellCorrectness($submitted_answer, $correct_answer, $rangeval, $correct_answer_string);
				break;
			case "AbsoluteRange":
				$answer = $this->lsspreadsheetCellGrader->getAbsoluteCellCorrectness($submitted_answer, $correct_answer, $rangeval, $correct_answer_string, $num_decimals);
				break;
		}

		if ($submitted_answer === "") {
			$answer->iscorrect = false;
		}
		if (!is_numeric($submitted_answer)) {
			$answer->iscorrect = false;
		}

		return $answer;
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
 * Grade the spreadsheet question
 *
 * @param <type> $options
 * @param <type> $responses
 * @param <type> $gradingtype
 * @return int
 */
	public function grade_spreadsheet_question($responses, $gradingtype = "auto") {
		$answersArray = [];
		$spreadSheet = $this->spreadsheet;

		$excel = null;
		$excel = $this->create_excel_marking_sheet_from_spreadsheet($spreadSheet, false);

		$moodleinput_excel = $this->create_excel_populated_all_moodle_inputs($responses, false);
		\PHPExcel_Calculation::getInstance()->clearCalculationCache();
		//populate the excel sheet with the StudentInput Data
		foreach ($responses as $cellref => $value) {
			$fields = explode("_", $cellref);

			if ($fields[0] == "table0") {

				//dont overwrite the calculation cells!
				if ($spreadSheet[$cellref]->celltype == "StudentInput") {
					$excel->getActiveSheet()->setCellValue($spreadSheet[$cellref]->getExcelref(), $value);
				}
			}
		}

		foreach ($spreadSheet as $cellref => $cell) {

			if (array_key_exists($cellref, $responses)) {
				$submitted_answer = $responses[$cellref];
			} else {
				$submitted_answer = "";
			}

			\PHPExcel_Calculation::getInstance()->clearCalculationCache();
			$calcAnswer = $excel->getActiveSheet()->getCell($cell->getExcelref())->getCalculatedValue();

			$cells[$cellref]['correct_value'] = $calcAnswer;
			$answer_checked = new \stdClass();
			$answer_checked->iscorrect = null;
			$answer_checked->correctanswer = '';
			$answer_checked->feedbackstring = '';

			switch ($cell->celltype) {
				case "CalcAnswer":
					$answer_checked = $this->get_cell_correctness($submitted_answer, $calcAnswer, $cell->rangetype, $cell->rangeval);
					$answer_checked = $this->method_mark_cell(
						$moodleinput_excel,
						$cell->getExcelref(),
						$cell->formula,
						$cell->rangetype,
						$cell->rangeval,
						$submitted_answer);
					break;
				}
				$answer_checked->celltype = $cell->celltype;
				$answer_checked->submitted_answer = $submitted_answer;
				$answersArray[$cellref] = $answer_checked;
			}



		$excel->disconnectWorksheets();
		$moodleinput_excel->disconnectWorksheets();

		unset($excel);
		unset($moodleinput_excel);
		return $answersArray;
	}

	public function get_fractional_grade($responses){
		$gradedQuestion = [];
		$ans = $this->grade_spreadsheet_question($responses);

		$userTotal = 0;
    $maxMark = 0;

    foreach ($ans as $key => $value) {

      if($value->celltype === 'CalcAnswer'){
          $maxMark += $this->spreadsheet[$key]->marks;
      }

      if($value->iscorrect === true){
          $userTotal += $this->spreadsheet[$key]->marks;
      }
    }
        
		return $this->getGradeFractionFromUserTotalAndMaxMark($userTotal, $maxMark);
	}

	/**
	 * This function isnt used by moodle - its a helper function for the migration to the new question type
	 * @return [type] [description]
	 */
	public function get_internal_max_mark(){
		$maxMark = 0;
		foreach ($this->spreadsheet as $key => $value) {
      if($value->celltype === 'CalcAnswer'){
          $maxMark += $value->marks;
      }
    }
    return $maxMark;
	}

	private function getGradeFractionFromUserTotalAndMaxMark($userTotal, $maxMark){
		if($maxMark === 0){
    	$fraction = 1;
    } else {
	    $fraction = $userTotal / $maxMark;
    }
    return $fraction;
	}

	/**
 *
 * @param <type> $spreadSheet
 * @param <type> $objPHPExcel
 * @return spreadsheet
 *   This is a spreadsheet data
 */
	public function get_calculated_sheet_json($spreadSheet, &$objPHPExcel) {
		\PHPExcel_Calculation::getInstance()->clearCalculationCache();

		foreach ($spreadSheet as $cellref => $cell) {
			$spreadSheet[$cellref]->textvalue = $objPHPExcel->getActiveSheet()->getCell($cell->getExcelref())->getCalculatedValue();
		}

		return $spreadSheet;
	}

	public function setJsonStringFromDb($lsspreaddata_string_from_db){
		$json = json_decode($lsspreaddata_string_from_db, true);
		//the javascript editor wraps the data in an array which represents worksheets
		//we only use 1 worksheet so only need the 0 index of the array
		$this->lsspreaddata = $json[0];
		$this->initSpreadsheetFromLsspreadata();
		$this->initMetaDataObject();
		$this->initChartDataObject();
	}

	private function initSpreadsheetFromLsspreadata() {
		$jsonCells = $this->lsspreaddata['cell'];

		$spreadsheet = array();

		foreach ($jsonCells as $cellref => $cell) {

			$lsspreadsheetCell = new Cell();
			$lsspreadsheetCell->initCellFromJsonObject($cell);
			$spreadsheet[$cellref] = $lsspreadsheetCell;
		}

		$this->spreadsheet = $spreadsheet;
	}

	private function initMetaDataObject(){
		$this->numberOfColumns = $this->lsspreaddata['metadata']['columns'];
		$this->numberOfRows = $this->lsspreaddata['metadata']['rows'];
		$this->title = $this->lsspreaddata['metadata']['title'];
	}

	private function initChartDataObject(){
		if(array_key_exists('chartdata', $this->lsspreaddata)){
			$this->chartData = $this->lsspreaddata['chartdata'];
		} else {
			$this->chartData = '';
		}
	}

	public function getChartDataObject() {
		return $this->chartData;
	}

	public function getObjectFromLsspreaddata() {
		return $this->spreadsheet;
	}

	/**
	 * Returns HTML for table
	 * @param  [type]  $excel              PHP Excel obj
	 * @param  [type]  $nameprefix              [description]
	 * @param  string  $json_chart_instructions [description]
	 * @param  string  $lschartdata             [description]
	 * @param  Object  $options             [description]
	 * @return [type]                           [description]
	 */
	public function getTakeTableFromLsspreaddata($nameprefix = '', $options, $qa, $graded, $feedbackStyles, $json_chart_instructions = "", $lschartdata = "") {

		$lschart = new Chart();

		$spreadSheet = $this->spreadsheet;

		$htmltable = "";

		if ($lschartdata !== "") {
			//$htmltable .= $lschart->get_chart_javascript($question->id, $CFG->wwwroot, $json_chart_instructions, $lschartdata);
		}
		$htmltable .= "<div class=\"lsspreadsheet_table\"><table>";
		for ($row = 0; $row < $this->numberOfRows; $row++) {
				$htmltable .= '<tr>';
			for ($col = 0; $col < $this->numberOfColumns; $col++) {
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
					$cell = new Cell();
					$cell->response = $qa->get_last_qt_var($cellref);
				}
				$htmltable .= $cell->getTdForCell($cellname, $this->numberOfColumns, $options->readonly);
			}
			$htmltable .= "\n</tr>\n";
		}
		$htmltable .= "</table></div>";

		if ($lschartdata !== "") {
			//$htmltable .= $lschart->get_chart_html($question->id, $CFG->wwwroot);
		}

		return $htmltable;
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



