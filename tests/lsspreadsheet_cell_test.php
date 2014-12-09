<?php


defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/question/type/lsspreadsheet/lib/LsspreadsheetCell.php');
require_once($CFG->dirroot . '/question/type/lsspreadsheet/phpexcel/PHPExcel.php');
use Learnsci\LsspreadsheetCell;

class LsspreadsheetCellTest extends basic_testcase {

	private $cell;

	protected function setUp() {
		$this->cell = new LsspreadsheetCell();
	}

	public function testDefaultValues(){
		$cell = $this->cell;
		$this->assertEquals($cell->celltype, '');
		$this->assertEquals($cell->response, '');
		$this->assertEquals($cell->feedbackClass, '');
		$this->assertEquals($cell->feedbackImage, '');
		$this->assertNull($cell->iscorrect);
		$this->assertEquals($cell->row, '');
		$this->assertEquals($cell->col, '');
		$this->assertEquals($cell->textvalue, '');
		$this->assertEquals($cell->formula, '');
		$this->assertEquals($cell->feedback, '');
		$this->assertEquals($cell->labelalign, '');
		$this->assertEquals($cell->marks, 0);
		$this->assertEquals($cell->chart, '');
		$this->assertEquals($cell->rangetype, '');
		$this->assertEquals($cell->rangeval, 0);

		$this->assertEquals($cell->getCellvalue(), '');
	}

	public function testCalcAnswerCell(){
    $cellName = 'cellName';
    $numberOfColumns = 99;
    $isReadOnly = false;
    $result = $this->cell->getTdForCell($cellName, $numberOfColumns, $isReadOnly);
	}

	public function testInitFromJsonObjectLabelCell(){
		$cell = new LsspreadsheetCell();
		$jsonObject = array(
			"celltype" => "Label_1",
			"chart" => "",
      "col" => 1,
      "feedback" => "some feedback",
      "formula" => "",
      "rangetype" => "",
      "row" => 0,
      "textvalue" => "Some&nbsp;long&nbsp;feedback");
  	$cell->initCellFromJsonObject($jsonObject);
  	$this->assertEquals($cell->celltype, 'Label');
  	$this->assertEquals($cell->col, 1);
  	$this->assertEquals($cell->row, 0);
  	$this->assertEquals($cell->getExcelRef(), 'B1');
  	$this->assertEquals($cell->getCellvalue(), 'Some long feedback');
  	$this->assertEquals($cell->labelalign, 'left');
	}

	public function testInitFromJsonObjectNoneCell(){
		$cell = new LsspreadsheetCell();
		$jsonObject = array(
			"celltype" => "None_undefined",
      "chart" => "",
      "col" => 0,
      "feedback" => "",
      "formula" => "",
      "rangetype" => "",
      "row" => 0,
      "textvalue" => ""
      );
  	$cell->initCellFromJsonObject($jsonObject);
  	$this->assertEquals($cell->celltype, 'None');
  	$this->assertEquals($cell->col, 0);
  	$this->assertEquals($cell->row, 0);
  	$this->assertEquals($cell->getExcelRef(), 'A1');
  	$this->assertEquals($cell->getCellvalue(), '');
	}

	public function testInitFromJsonObjectSectionHeadingCell(){
		$cell = new LsspreadsheetCell();
		$jsonObject = array(
			"celltype" => "SectionHeading_1",
      "chart" => "",
      "col" => 0,
      "feedback" => "",
      "formula" => "",
      "rangetype" => "",
      "row" => 3,
      "textvalue" => "&lt;b&gt;TABLE&nbsp;1&nbsp;-&nbsp;VITAL&nbsp;STATISTICS&lt;/b&gt;"
      );
  	$cell->initCellFromJsonObject($jsonObject);
  	$this->assertEquals($cell->celltype, 'SectionHeading');
  	$this->assertEquals($cell->col, 0);
  	$this->assertEquals($cell->row, 3);
  	$this->assertEquals($cell->getExcelRef(), 'A4');
  	$this->assertEquals($cell->getCellvalue(), '<b>TABLE 1 - VITAL STATISTICS</b>');
	}

	public function testInitFromJsonObjectCalcAnswerCell(){
		$cell = new LsspreadsheetCell();
		$jsonObject = array(
			"celltype" => "CalcAnswer_7",
      "chart" => "",
      "col" => 0,
      "feedback" => "",
      "formula" => "=0.4*B7",
      "rangetype" => "SigfigRange_2",
      "row" => 0,
      "textvalue" => "=0.4*B7"
      );
  	$cell->initCellFromJsonObject($jsonObject);
  	$this->assertEquals($cell->celltype, 'CalcAnswer');
		$this->assertEquals($cell->marks, 7);
  	$this->assertEquals($cell->col, 0);
  	$this->assertEquals($cell->row, 0);
  	$this->assertEquals($cell->getExcelRef(), 'A1');
  	$this->assertEquals($cell->getCellvalue(), '=0.4*B7');
  	$this->assertEquals($cell->rangetype, 'SigfigRange');
  	$this->assertEquals($cell->rangeval, 2);
  	$this->assertEquals($cell->formula, '=0.4*B7');
	}

}
