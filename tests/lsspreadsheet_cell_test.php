<?php


defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/question/type/lsspreadsheet/lib/LsspreadsheetCell.php');

use Learnsci\LsspreadsheetCell;

class LsspreadsheetCellTest extends basic_testcase {

	private $cell;

	protected function setUp() {
		$this->cell = new LsspreadsheetCell();
	}

	public function testDefaultValues(){
		$cell = $this->cell;
		$this->assertEquals($cell->celltype, '');
		$this->assertEquals($cell->cellname, '');
		$this->assertEquals($cell->style, '');
		$this->assertEquals($cell->markedimg, '');

		$this->assertEquals($cell->getCellvalue(), '');
	}

	public function testCalcAnswerCell(){
		$cell = new LsspreadsheetCell();
		$this->celltype = "CalcAnswer";
    $this->cellvalue = "";
    $this->cellname = "";
    $this->style = "";
    $this->markedimg = "";
    $this->correct_value = "";
    $this->response = '';
    $this->colspan = 1;
    $this->tdclass = "";
    $this->correctanswer = '';
    $this->feedbackstring = '';
    $this->feedbackClass = '';
    $this->feedbackImage = '';
    $this->iscorrect = null;
    $this->submitted_anser = '';
    $this->row = '';
    $this->col = '';

    $cellName = 'cellName';
    $numberOfColumns = 99;
    $isReadOnly = false;
    $result = $cell->getTdForCell($cellName, $numberOfColumns, $isReadOnly);
    print_r($result);
	}

}
