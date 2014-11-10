<?php


defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/question/type/lsspreadsheet/lib/Lsspreadsheet.php');
require_once($CFG->dirroot . '/question/type/lsspreadsheet/lib/LsspreadsheetCell.php');
require_once($CFG->dirroot . '/question/type/lsspreadsheet/lib/LsspreadsheetUtils.php');
require_once($CFG->dirroot . '/question/type/lsspreadsheet/lib/LsspreadsheetCellGrader.php');
require_once($CFG->dirroot . '/question/type/lsspreadsheet/lib/LsspreadsheetChart.php');
require_once($CFG->dirroot . '/question/type/lsspreadsheet/lib/LsspreadsheetChartStats.php');
require_once($CFG->dirroot . '/question/type/lsspreadsheet/phpexcel/PHPExcel.php');
use Learnsci\Lsspreadsheet;
use Learnsci\LsspreadsheetUtils;

class LsspreadsheetTest extends basic_testcase {

	private $lsspreaddata;

	protected function setUp() {
		$this->spreadsheet = new Lsspreadsheet();
		$this->lsspreaddata = file_get_contents(__DIR__ . '/fixtures/sample_sheet_data.json');

	}

	public function testConvertRawdata() {
		$spreadsheetUtils = new LsspreadsheetUtils();
		$json = $spreadsheetUtils->decodeLsspreaddataJsonString($this->lsspreaddata);
		$spreadsheetUtils->convert_rawdata_from_zero_array_lsspreaddata($json);
	}

	public function testConvertLsspreaddataJsonToObject() {
		$spreadsheetUtils = new LsspreadsheetUtils();
		$json = $spreadsheetUtils->decodeLsspreaddataJsonString($this->lsspreaddata);
		//print_r($json);
		$lsspreaddata = $spreadsheetUtils->convert_rawdata_from_zero_array_lsspreaddata($json);
		//print_r($lsspreaddata['table0_cell_c0_r3']);
		$spreadSheet = $spreadsheetUtils->convert_lsspreaddata_json_to_object($lsspreaddata);
		//print_r($spreadSheet['table0_cell_c0_r3']);
	}

	public function testCreateExcelFromSpreadsheet() {
		$spreadsheetUtils = new LsspreadsheetUtils();
		$spreadsheet = $spreadsheetUtils->getObjectFromLsspreaddata($this->lsspreaddata);
		$excel = $spreadsheetUtils->create_excel_marking_sheet_from_spreadsheet($spreadsheet);
	}

	public function testGetTakeTableFromLsspreaddata() {
		$spreadsheetUtils = new LsspreadsheetUtils();
		$tableHtml = $spreadsheetUtils->getTakeTableFromLsspreaddata($this->lsspreaddata);
		$expectedTableHtml = file_get_contents(__DIR__ . '/fixtures/take-table.html');
		file_put_contents('/tmp/lsspreadsheet.html', $tableHtml);
		$this->assertEquals($tableHtml, $expectedTableHtml);
	}

	public function testGradeSpreadsheetQuestion()
	{
		$responses = Array (
			'table0_cell_c1_r5' => 'male',
			'table0_cell_c1_r6' => 1,
			'table0_cell_c1_r7' => 1,
			'table0_cell_c1_r8' => 1,
			'table0_cell_c1_r9' => 0.2,
			'table0_cell_c1_r10' => 0.4);
		$answers = $this->spreadsheet->grade_spreadsheet_question(
			$this->lsspreaddata,
			$responses,
			$gradingtype = "auto");

		//table0_cell_c1_r10 should be 0.4 times table0_cell_c1_r6
		$this->assertEquals($answers['table0_cell_c1_r10']->iscorrect, true);

	}

	public function testGetCellCorrectness(){

		$submitted_answer = 4;
		$calcAnswer = 4;
		$cell_rangetype = 'SigfigRange';
		$cell_rangeval = '2';
		$answer = $this->spreadsheet->get_cell_correctness($submitted_answer, $calcAnswer, $cell_rangetype, $cell_rangeval);
	}


	public function testMethodMarkCell(){
		$responses = Array (
			'table0_cell_c1_r5' => 89,
			'table0_cell_c1_r6' => 10,
			'table0_cell_c1_r7' => 3,
			'table0_cell_c1_r8' => 5,
			'table0_cell_c1_r9' => 6,
			'table0_cell_c1_r10' => 1);
		$spreadsheetUtils = new LsspreadsheetUtils();
		$spreadSheet = $spreadsheetUtils->getObjectFromLsspreaddata($this->lsspreaddata);

		$cell_rangetype = 'SigfigRange';
		$cell_rangeval = '2';
		$cell_excelref = 'B11';
		$cell_formula = '=0.4 * B7';
		$submitted_answer = 4;
		$moodleinput_excel = $this->spreadsheet->create_excel_populated_all_moodle_inputs($spreadSheet, $responses, false);
		$methodAnswer = $this->spreadsheet->method_mark_cell($moodleinput_excel, $cell_excelref, $cell_formula, $cell_rangetype, $cell_rangeval, $submitted_answer);
	}

	public function testMethodMarkCellAgreesWithCellCorrectness(){
		$responses = Array (
			'table0_cell_c1_r5' => 89,
			'table0_cell_c1_r6' => 10,
			'table0_cell_c1_r7' => 3,
			'table0_cell_c1_r8' => 5,
			'table0_cell_c1_r9' => 6,
			'table0_cell_c1_r10' => 1);
		$spreadsheetUtils = new LsspreadsheetUtils();
		$spreadSheet = $spreadsheetUtils->getObjectFromLsspreaddata($this->lsspreaddata);

		$cell_rangetype = 'SigfigRange';
		$cell_rangeval = '2';
		$cell_excelref = 'B11';
		$cell_formula = '=0.4 * B7';
		$submitted_answer = 4;
		$moodleinput_excel = $this->spreadsheet->create_excel_populated_all_moodle_inputs($spreadSheet, $responses, false);
		$methodAnswer = $this->spreadsheet->method_mark_cell($moodleinput_excel, $cell_excelref, $cell_formula, $cell_rangetype, $cell_rangeval, $submitted_answer);
	}

}
