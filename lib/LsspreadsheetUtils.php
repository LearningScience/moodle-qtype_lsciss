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

			$spreadSheet[$cellref] = new \stdClass();

			$spreadSheet[$cellref]->col = $cell['col'];
			$spreadSheet[$cellref]->row = $cell['row'];

			$spreadSheet[$cellref]->r = $spreadSheet[$cellref]->row + 1;
			$spreadSheet[$cellref]->c = $spreadSheet[$cellref]->col;

			$spreadSheet[$cellref]->excelref = \PHPExcel_Cell::stringFromColumnIndex($spreadSheet[$cellref]->c) . $spreadSheet[$cellref]->r;
			$spreadSheet[$cellref]->textvalue = $cell['textvalue'];
			$spreadSheet[$cellref]->formula = $cell['formula'];
			$spreadSheet[$cellref]->feedback = $cell['feedback'];
			$spreadSheet[$cellref]->labelalign = "";
			$spreadSheet[$cellref]->marks = 0;
			$spreadSheet[$cellref]->additional = array();

			if (isset($cell['chart'])) {
				$spreadSheet[$cellref]->chart = $cell['chart'];
			}
			$celltype = $cell['celltype'];
			$range = $cell['rangetype'];

			if ($celltype !== "") {
				$celltype = explode("_", $celltype);
				$spreadSheet[$cellref]->celltype = $celltype[0];

				if ($spreadSheet[$cellref]->celltype == "Label") {
					if (isset($celltype[1])) {
						$spreadSheet[$cellref]->labelalign = $celltype[1];
					} else if (!isset($celltype[1]) or $celltype[1] == "1") {
						$spreadSheet[$cellref]->labelalign = "left";
					}
				} else if ($spreadSheet[$cellref]->celltype == "CalcAnswer") {
					$spreadSheet[$cellref]->marks = $celltype[1];
					if ($range === "") {
						//Setting the defualt range is to make sure that any questions set with
						//the earliest versions of the javascript interface are OK
						$range = "AbsoluteRange_0";
					}
				} else {
					$spreadSheet[$cellref]->additional = $celltype[1];
				}
			} else {
				$spreadSheet[$cellref]->celltype = null;
				$spreadSheet[$cellref]->marks = null;
			}
			if ($range !== "") {
				//range data stored in one string using "_" as sep
				$range = explode("_", $range);
				$spreadSheet[$cellref]->rangetype = $range[0];
				$spreadSheet[$cellref]->rangeval = $range[1];
			} else {
				$spreadSheet[$cellref]->rangetype = null;
				$spreadSheet[$cellref]->rangeval = null;
			}
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
				$markingExcel->getActiveSheet()->setCellValue($cell->excelref, strtoupper($cell->formula));
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
	 * @param  boolean $markingquestion         [description]
	 * @param  string  $json_chart_instructions [description]
	 * @param  string  $lschartdata             [description]
	 * @return [type]                           [description]
	 */
	public function getTakeTableFromLsspreaddata($lsspreaddata, $nameprefix = '', $markingquestion = false, $json_chart_instructions = "", $lschartdata = "") {
		global $QTYPES, $CFG;

		$lschart = new LsspreadsheetChart();
		//this is the method that draws the question that the student actually sees.

		//$lsspreaddata = $question->lsspreaddata;
		$metadata = $this->get_metadataObject($lsspreaddata);

		$spreadSheet = $this->getObjectFromLsspreaddata($lsspreaddata);

		$excel = $this->convert_spreadsheet_to_excel($spreadSheet);

		$feedback = "";

		//LS START - This to change when graphing implemented, should be pulled from db:
		$gradingtype = "auto";
		//LS END

		$htmltable = "";

		if ($lschartdata !== "") {
			$htmltable .= $lschart->get_chart_javascript($question->id, $CFG->wwwroot, $json_chart_instructions, $lschartdata);
		}
		$htmltable .= "<div class=\"lsspreadsheet_table\"><table>";
		for ($row = 0; $row < $metadata->rows; $row++) {
			for ($col = 0; $col < $metadata->columns; $col++) {

				$rowind = "r" . $row;
				$colind = "c" . $col;
				$cellref = 'table0_cell_' . $colind . '_' . $rowind;
				$celldata = [];

				if (isset($spreadSheet[$cellref])) {
					$cell = $spreadSheet[$cellref];

					$celldata[$cellref] = new \stdClass();
					$celldata[$cellref]->celltype = $cell->celltype;
					$celldata[$cellref]->marks = $cell->marks;
					$celldata[$cellref]->cellvalue = html_entity_decode(str_ireplace('&nbsp;', " ", $cell->textvalue));
					$celldata[$cellref]->cellname = $nameprefix . $cellref;
					$celldata[$cellref]->style = "";
					$celldata[$cellref]->rangetype = $cell->rangetype;
					$celldata[$cellref]->rangevalue = $cell->rangeval;
					$celldata[$cellref]->feedback = $cell->feedback;
					$celldata[$cellref]->feedback = str_replace("'", "\\'", $celldata[$cellref]->feedback);
					if ($markingquestion && array_key_exists($cellref, $celldata)) {

						$strfeedbackwrapped = "Feedback";//get_string('correctanswerandfeedback', 'qtype_lsspreadsheet');
						$popupfeedback = "<p>Correct value for your data is " . $celldata[$cellref]->correct_value . "</p><p>" . str_replace("u000a", "<br/>", $celldata[$cellref]->feedback) . "</p>";
						$celldata[$cellref]->popup = " onmouseover=\"return overlib('$popupfeedback', STICKY, MOUSEOFF, CAPTION, '$strfeedbackwrapped', FGCOLOR, '#FFFFFF');\" " .
						" onmouseout=\"return nd();\" ";

						if (isset($celldata[$cellref]->iscorrect) and $celldata[$cellref]->iscorrect) {

							$celldata[$cellref]->style = 'class = "' . question_get_feedback_class(1) . '"';
							$celldata[$cellref]->markedimg = "<img src=\"$CFG->pixpath/i/tick_green_big.gif\" alt=\"$feedback\" />";
						} else {

							$celldata[$cellref]->style = 'class = "' . question_get_feedback_class(0) . '"';
							$celldata[$cellref]->markedimg = "<img src=\"$CFG->pixpath/i/cross_red_big.gif\" alt=\"$feedback\" />";
						}
					} else {
						$celldata[$cellref]->style = "";
						$celldata[$cellref]->markedimg = "";
						$celldata[$cellref]->correct_value = "";
						$celldata[$cellref]->popup = "";
					}

					$celldata[$cellref]->response = '';

				} else {
					$celldata[$cellref] = new \stdClass();
					$celldata[$cellref]->celltype = "";
					$celldata[$cellref]->cellvalue = "";
					$celldata[$cellref]->cellname = "";
					$celldata[$cellref]->style = "";
					$celldata[$cellref]->markedimg = "";
					$celldata[$cellref]->correct_value = "";
					$celldata[$cellref]->response = '';
				}
				$celldata[$cellref]->colspan = 1;
				$celldata[$cellref]->tdclass = "";
				switch ($celldata[$cellref]->celltype) {
					case "FixedAnswer":
						$celldata[$cellref]->cellcontent = '<input type="text" ' . $celldata[$cellref]->popup . " " . $celldata[$cellref]->style . ' value="' . $celldata[$cellref]->response . '" name="' . $celldata[$cellref]->cellname . '"></input>' . $celldata[$cellref]->markedimg;
						break;
					case "CalcAnswer":
						$celldata[$cellref]->tdclass = "lsCalcAnswerTd";
						$celldata[$cellref]->cellcontent = '<input type="text" class="lsCalcAnswerInput" ' . $celldata[$cellref]->popup . " " . $celldata[$cellref]->style . ' value="' . $celldata[$cellref]->response . '" id="' . $celldata[$cellref]->cellname . '" name="' . $celldata[$cellref]->cellname . '"></input>' . $celldata[$cellref]->markedimg;
						break;
					case "NumberAnswer":
						$celldata[$cellref]->cellcontent = '<input type="text"  ' . $celldata[$cellref]->popup . " " . $celldata[$cellref]->style . ' value="' . $celldata[$cellref]->response . '"name="' . $celldata[$cellref]->cellname . '"></input>' . $celldata[$cellref]->markedimg;
						break;
					case "StudentInput":
						$celldata[$cellref]->tdclass = "lsStudentInputTd";
						$celldata[$cellref]->cellcontent = '<input type="text" class="lsInputStudentCell" value="' . $celldata[$cellref]->response . '" id="' . $celldata[$cellref]->cellname . '" name="' . $celldata[$cellref]->cellname . '"></input>';
						break;
					case "Label":
						//error_log(print_r($cellvalue,true));
						$celldata[$cellref]->tdclass = "lsLabelTd_" . $cell->labelalign;
						$celldata[$cellref]->cellcontent = $celldata[$cellref]->cellvalue;
						break;
					case "SectionHeading":
						//error_log(print_r($cellvalue,true));
						$celldata[$cellref]->cellcontent = $celldata[$cellref]->cellvalue;
						$celldata[$cellref]->colspan = $metadata->columns;
						$celldata[$cellref]->tdclass = "lsTableSectionHeading";
						break;
					default:
						$celldata[$cellref]->cellcontent = "";
				}
				$htmltable .= "<td colspan=" . $celldata[$cellref]->colspan . " class=" . $celldata[$cellref]->tdclass . ">" . $celldata[$cellref]->cellcontent . "</td>";
			}
			$htmltable .= "</tr>";
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

			$objPHPExcel->getActiveSheet()->setCellValue($cell->excelref, $val);
		}

		return (clone $objPHPExcel);
	}

}
