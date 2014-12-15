<?php

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/question/type/lsciss/lib/CellGrader.php');

use Learnsci\CellGrader;

class CellGraderTest extends PHPUnit_Framework_TestCase {

  protected function setUp() {
    $this->cellGrader = new CellGrader();
  }
  public function testGetSigFigCellCorrectness(){
    $submitted_answer = 2.11;
    $correct_answer = 1.1;
    $rangeval = 1;
    $correct_answer_string = 'correct answer string';
    $answer = $this->cellGrader->getSigFigCellCorrectness($submitted_answer, $correct_answer, $rangeval, $correct_answer_string);
    $this->assertEquals($answer->iscorrect, false);
    $this->assertEquals($answer->correctanswer, " 1 to 1 sig. fig");

    $submitted_answer = 1.7;
    $correct_answer = 1;
    $rangeval = 1;
    $correct_answer_string = 'correct answer string';
    $answer = $this->cellGrader->getSigFigCellCorrectness($submitted_answer, $correct_answer, $rangeval, $correct_answer_string);
    $this->assertEquals($answer->iscorrect, false);
    $this->assertEquals($answer->correctanswer, " 1 to 1 sig. fig");

    $submitted_answer = 1.7;
    $correct_answer = 1.6;
    $rangeval = 1;
    $correct_answer_string = 'correct answer string';
    $answer = $this->cellGrader->getSigFigCellCorrectness($submitted_answer, $correct_answer, $rangeval, $correct_answer_string);
    $this->assertEquals($answer->iscorrect, true);
    $this->assertEquals($answer->correctanswer, " 2 to 1 sig. fig");
  }


  public function testGetAbsoluteCellCorrectness(){
    $submitted_answer = 2;
    $correct_answer = 2;
    $rangeval = 0;
    $correct_answer_string = 'correct answer string';
    $answer = $this->cellGrader->getAbsoluteCellCorrectness($submitted_answer, $correct_answer, $rangeval, $correct_answer_string);
    $this->assertEquals($answer->iscorrect, true);
    $this->assertEquals(" correct answer string exactly ", $answer->correctanswer);

    $submitted_answer = 1;
    $correct_answer = 2;
    $rangeval = 0;
    $correct_answer_string = 'correct answer string';
    $answer = $this->cellGrader->getAbsoluteCellCorrectness($submitted_answer, $correct_answer, $rangeval, $correct_answer_string);
    $this->assertEquals($answer->iscorrect, false);
    $this->assertEquals(" correct answer string exactly ", $answer->correctanswer);

    $submitted_answer = 1;
    $correct_answer = 2;
    $rangeval = 1;
    $correct_answer_string = 'correct answer string';
    $answer = $this->cellGrader->getAbsoluteCellCorrectness($submitted_answer, $correct_answer, $rangeval, $correct_answer_string);
    $this->assertEquals($answer->iscorrect, true);
    $this->assertEquals("  between 1.00 and 3.00", $answer->correctanswer);

    $submitted_answer = 1;
    $correct_answer = 3;
    $rangeval = 1;
    $correct_answer_string = 'correct answer string';
    $answer = $this->cellGrader->getAbsoluteCellCorrectness($submitted_answer, $correct_answer, $rangeval, $correct_answer_string);
    $this->assertEquals($answer->iscorrect, false);
    $this->assertEquals("  between 2.00 and 4.00", $answer->correctanswer);
  }

}
