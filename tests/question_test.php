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
 * Unit tests for the spreadsheet question definition class.
 *
 * @package   qtype_lsspreadsheet
 * @copyright 2012 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

/**
 * Unit tests for the spreadsheet question definition class.
 *
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_lsspreadsheet_question_test extends basic_testcase {

    public function test_get_expected_data() {
        $question = test_question_maker::make_question('lsspreadsheet');

        $this->assertEquals(array(
                    'table0_cell_c1_r10' => PARAM_RAW_TRIMMED,
                    'table0_cell_c1_r5' => PARAM_RAW_TRIMMED,
                    'table0_cell_c1_r6' => PARAM_RAW_TRIMMED,
                    'table0_cell_c1_r7' => PARAM_RAW_TRIMMED,
                    'table0_cell_c1_r8' => PARAM_RAW_TRIMMED,
                    'table0_cell_c1_r9' => PARAM_RAW_TRIMMED,
                ), $question->get_expected_data());
    }

    public function test_is_complete_response() {
        // TODO is it actually necessary for the students to complete every box?
        // If not change the assertions an the code.
        $question = test_question_maker::make_question('lsspreadsheet');

        $this->assertFalse($question->is_complete_response(array()));
        $this->assertFalse($question->is_complete_response(array(
                    'table0_cell_c1_r10' => '1.0',
                    'table0_cell_c1_r5' => '1.0',
                    'table0_cell_c1_r6' => '1.0',
                    'table0_cell_c1_r7' => '1.0',
                    'table0_cell_c1_r8' => '1.0',
                    'table0_cell_c1_r9' => '',
                )));
        $this->assertTrue($question->is_complete_response(array(
                    'table0_cell_c1_r10' => '1.0',
                    'table0_cell_c1_r5' => '1.0',
                    'table0_cell_c1_r6' => '1.0',
                    'table0_cell_c1_r7' => '1.0',
                    'table0_cell_c1_r8' => '1.0',
                    'table0_cell_c1_r9' => '1.0',
                )));
    }

    //see match question type for examples
    public function test_is_gradable_response() {
        // TODO here, it is probably even less necessary for the students to
        // complete every box, change the assertions an the code.
        $question = test_question_maker::make_question('lsspreadsheet');

        $this->assertFalse($question->is_gradable_response(array()));
        $this->assertFalse($question->is_gradable_response(array(
                    'table0_cell_c1_r10' => '1.0',
                    'table0_cell_c1_r5' => '1.0',
                    'table0_cell_c1_r6' => '1.0',
                    'table0_cell_c1_r7' => '1.0',
                    'table0_cell_c1_r8' => '1.0',
                    'table0_cell_c1_r9' => '',
                )));
        $this->assertTrue($question->is_gradable_response(array(
                    'table0_cell_c1_r10' => '1.0',
                    'table0_cell_c1_r5' => '1.0',
                    'table0_cell_c1_r6' => '1.0',
                    'table0_cell_c1_r7' => '1.0',
                    'table0_cell_c1_r8' => '1.0',
                    'table0_cell_c1_r9' => '1.0',
                )));
    }

    public function test_grading() {
        $question = test_question_maker::make_question('lsspreadsheet');

        list($fraction, $state) = $question->grade_response(array(
                    'table0_cell_c1_r5' => 'male',
                    'table0_cell_c1_r6' => '1.0',
                    'table0_cell_c1_r7' => '1.0',
                    'table0_cell_c1_r8' => '1.0',
                    'table0_cell_c1_r9' => '0.3',
                    'table0_cell_c1_r10' => '0.5'
                ));
        $this->assertEquals(0.3333333, $fraction, '', 0.00000005);
        $this->assertEquals(question_state::$gradedpartial, $state);

        list($fraction, $state) = $question->grade_response(array(
                    'table0_cell_c1_r5' => 'male',
                    'table0_cell_c1_r6' => '1.0',
                    'table0_cell_c1_r7' => '1.0',
                    'table0_cell_c1_r8' => '0.1',
                    'table0_cell_c1_r9' => '0.3',
                    'table0_cell_c1_r10' => '0.5'
                ));
        $this->assertEquals(0, $fraction, '', 0.00000005);
        $this->assertEquals(question_state::$gradedwrong, $state);

     
        list($fraction, $state) = $question->grade_response(array(
                    'table0_cell_c1_r5' => 'male',
                    'table0_cell_c1_r6' => '1.0',
                    'table0_cell_c1_r7' => '1.0',
                    'table0_cell_c1_r8' => '1.0',
                    'table0_cell_c1_r9' => '0.2',
                    'table0_cell_c1_r10' => '0.4'
                ));
        $this->assertEquals(1, $fraction, '', 0.00000005);
        $this->assertEquals(question_state::$gradedright, $state);


    }

    //text summary of question human readable
    public function test_get_question_summary() {
        $question = test_question_maker::make_question('lsspreadsheet');
        $this->assertEquals('Fill in the boxes.', $question->get_question_summary());
    }

    //when previewing question can see these (response history)
    public function test_summarise_response() {
        $question = test_question_maker::make_question('lsspreadsheet');

        $this->assertEquals('c1_r10: 1.0, c1_r5: 2.0, c1_r6: 3.0, c1_r7: 4.0, c1_r8: 5.0, c1_r9: 6.0',
                $question->summarise_response(array(
                        'table0_cell_c1_r10' => '1.0',
                        'table0_cell_c1_r5' => '2.0',
                        'table0_cell_c1_r6' => '3.0',
                        'table0_cell_c1_r7' => '4.0',
                        'table0_cell_c1_r8' => '5.0',
                        'table0_cell_c1_r9' => '6.0',
                    )));
    }

    public function test_get_correct_response() {
        $question = test_question_maker::make_question('lsspreadsheet');
        $this->assertNull($question->get_correct_response());
    }

    public function test_classify_response() {
        $question = test_question_maker::make_question('lsspreadsheet');
        $question->start_attempt(new question_attempt_step(), 1);

        $this->assertEquals(array(), $question->classify_response(array()));
    }
}
