<?php
namespace Learnsci;

class Lsspreadsheet {

	static $debug = false;
	private $lsspreadsheetUtils;

	public function __construct() {
				/**
		 * Caching is disabled to stop old values for cells being held in memory,
		 * less efficient on large spreadsheets but should be ok here.
		 *
		 * Note that that PHPExcel_Calculation instance is a singleton.
		 */
		\PHPExcel_Calculation::getInstance()->setCalculationCacheEnabled(false);


		self::$debug = false;
		$this->lsspreadsheetUtils = new LsspreadsheetUtils();
		$this->lsspreadsheetCellGrader = new LsspreadsheetCellGrader();
	}

	public function get_cloze_positions($options) {
		$clozeposition = array();
		foreach ($options as $key => $value) {
			$qtext = $value->questiontext;
			$qtext = explode("#", $qtext);
			$qtext = explode("||", $qtext[1]);
			$clozeposition[$qtext[0]] = $key;
		}
		return $clozeposition;
	}

	/**
	 * @brief function to extract the chart input ids
	 */
	public function get_chart_inputids($lsspreaddata) {
		$rawdata = json_decode($lsspreaddata);
		$spreadsheet = $this->lsspreadsheetUtils->getObjectFromLsspreaddata($lsspreaddata);

		foreach ($spreadsheet as $cellref => $cell) {

			$chartinputid = array();

			if (isset($cell->chart)) {
				$chartinputid[] = $cellref;
			}
		}

		return json_encode($chartinputid);
	}

	/**
	 * Function to generate a fake cloze question for the chart
	 * @return string
	 */
	public function generate_cloze_chart_question($lsspreaddata) {
		/**
		 * Ok this is a weird fuction that fakes a cloze question.  Its similar
		 * in concept to the other ones but uses a short answer question type rather
		 * than a numeric type.
		 */
		$cloze = '';

		$chartdata = json_decode($lsspreaddata);

		if (isset($chartdata->series[0]->mark)) {
			$mark = $chartdata->series[0]->mark;
			$mark = 1;
			$cloze = "{" . $mark . ":SHORTANSWER:%100%graph#}";
		}

		return $cloze;
	}

	/**
	 * Creates a PHPExcel spreadsheet in memory that has all the right forumalae in
	 * so that the student input data can be added in later
	 * @param <type> $spreadsheet
	 * @return <type>
	 */
	public function create_excel_populated_all_moodle_inputs($spreadsheet, $responses, $doClone = true) {
		$moodleinput_excel = new \PHPExcel();

		//PHPExcel_Calculation::getInstance()->clearCalculationCache();

		foreach ($spreadsheet as $cellref => $cell) {

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

			$moodleinput_excel->getActiveSheet()->setCellValue($cell->excelref, $cellvalue);
		}
		//PHPExcel_Calculation::getInstance()->clearCalculationCache();

		if ($doClone) {
			$ret = clone ($moodleinput_excel);
		} else {
			$ret = $moodleinput_excel;
		}
		return $ret;
	}

	public function get_chartdataObject($lsspreaddata) {

		$rawdata = json_decode($lsspreaddata);
		$rawdata = $rawdata['0'];

		if (isset($rawdata->chartdata)) {
			$chartdata = $rawdata->chartdata;
			return $chartdata;
		} else {
			return "";
		}
	}

	public function get_field_names($lsspreaddata){
		$json = $this->lsspreadsheetUtils->decodeLsspreaddataJsonString($lsspreaddata);
		$data = $this->lsspreadsheetUtils->convert_rawdata_from_zero_array_lsspreaddata($json);
		$calculatedCellNames = [];
		foreach ($data as $key => $value) {
			if(($value['celltype'] === 'CalcAnswer_1') || (($value['celltype'] === 'StudentInput_1'))){
				$calculatedCellNames[] = $key;
			}
		}
		return $calculatedCellNames;
	}

	public function method_mark_cell($moodleinput_excel, $cell_excelref, $cell_formula, $cell_rangetype, $cell_rangeval, $submitted_answer) {

		//error_log("submitted_answer" . " " . print_r($cell->excelref, true) . " = " . print_r($submitted_answer, true));

		$inputvalue = $moodleinput_excel->getActiveSheet()->getCell($cell_excelref)->getCalculatedValue();
		$moodleinput_excel->getActiveSheet()->setCellValue($cell_excelref, strtoupper($cell_formula));
		$calculated_answer = $moodleinput_excel->getActiveSheet()->getCell($cell_excelref)->getCalculatedValue();

		//error_log("calc answer" . " " . print_r($cell_excelref, true) . " = " . print_r($calculated_answer, true));

		$answer = $this->get_cell_correctness($submitted_answer, $calculated_answer, $cell_rangetype, $cell_rangeval);
		$moodleinput_excel->getActiveSheet()->setCellValue($cell_excelref, $inputvalue);
		return $answer;
	}

	public function get_cell_correctness($submitted_answer, $correct_answer, $rangetype, $rangeval, $correct_answer_string = "") {

		if ($correct_answer_string === "") {
			if ($correct_answer > 0) {
				$correct_answer_string = sprintf("%.2f", $correct_answer);
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
				$answer = $this->lsspreadsheetCellGrader->getAbsoluteCellCorrectness($submitted_answer, $correct_answer, $rangeval, $correct_answer_string);
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
 * Grade the spreadsheet question
 *
 * @param <type> $lsspreaddata
 * @param <type> $options
 * @param <type> $responses
 * @param <type> $gradingtype
 * @return int
 */
	public function grade_spreadsheet_question($lsspreaddata, $responses, $gradingtype = "auto") {

		$spreadSheet = $this->lsspreadsheetUtils->getObjectFromLsspreaddata($lsspreaddata);

		$excel = null;
		$excel = $this->lsspreadsheetUtils->create_excel_marking_sheet_from_spreadsheet($spreadSheet, false);

		$moodleinput_excel = $this->create_excel_populated_all_moodle_inputs($spreadSheet, $responses, false);
		\PHPExcel_Calculation::getInstance()->clearCalculationCache();
		//populate the excel sheet with the StudentInput Data
		foreach ($responses as $cellref => $value) {
			$fields = explode("_", $cellref);

			if ($fields[0] == "table0") {

				//dont overwrite the calculation cells!
				if ($spreadSheet[$cellref]->celltype == "StudentInput") {
					$excel->getActiveSheet()->setCellValue($spreadSheet[$cellref]->excelref, $value);
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
			$calcAnswer = $excel->getActiveSheet()->getCell($cell->excelref)->getCalculatedValue();

			$cells[$cellref]['correct_value'] = $calcAnswer;

			if ($submitted_answer !== "" and $calcAnswer !== "#DIV/0!") {
				switch ($cell->celltype) {
					case "CalcAnswer":
						$answer_checked = $this->get_cell_correctness($submitted_answer, $calcAnswer, $cell->rangetype, $cell->rangeval);
						$answer_checked = $this->method_mark_cell(
							$moodleinput_excel,
							$cell->excelref,
							$cell->formula,
							$cell->rangetype,
							$cell->rangeval,
							$submitted_answer);

						if ($answer_checked->iscorrect == true) {
							//the range in the correct answer should be 0
							// the answer in the numerical question should be 1
							$responses[$cellref] = 1;
						} else {
							$responses[$cellref] = 0;
						}
						break;
				}
			}
		}


		$excel->disconnectWorksheets();
		$moodleinput_excel->disconnectWorksheets();

		unset($excel);
		unset($moodleinput_excel);
		return $responses;
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
			$spreadSheet[$cellref]->textvalue = $objPHPExcel->getActiveSheet()->getCell($cell->excelref)->getCalculatedValue();
		}

		return $spreadSheet;
	}


}



