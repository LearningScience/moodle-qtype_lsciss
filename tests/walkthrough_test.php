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
 * This file contains tests that walks a spreadsheet question through various
 * behaviours.
 *
 * @package   qtype_lsciss
 * @copyright 2012 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/lsciss/tests/helper.php');


/**
 * Unit tests for the spreadsheet question type.
 *
 * @copyright 2012 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_lsciss_walkthrough_test extends qbehaviour_walkthrough_test_base {
    public function test_deferredfeedback_behaviour() {
        $question = test_question_maker::make_question('lsciss');
        $this->start_attempt_at_question($question, 'deferredfeedback', 3);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->render();
        $this->check_output_contains_text_input('table0_cell_c1_r5');
        $this->check_output_contains_text_input('table0_cell_c1_r6');
        $this->check_output_contains_text_input('table0_cell_c1_r7');
        $this->check_output_contains_text_input('table0_cell_c1_r8');
        $this->check_output_contains_text_input('table0_cell_c1_r9');
        $this->check_output_contains_text_input('table0_cell_c1_r10');
        $this->check_current_output(
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_no_hint_visible_expectation());

        // Save a partially right answer.
        $this->process_submission(array(
            'table0_cell_c1_r5' => '1.0',
            'table0_cell_c1_r6' => '1.0',
            'table0_cell_c1_r7' => '1.0',
            'table0_cell_c1_r8' => '1.0',
            'table0_cell_c1_r9' => '1.0',
            'table0_cell_c1_r10' => '1.0',
            ));

        // Verify.
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(null);
        $this->render();
        $this->check_output_contains_text_input('table0_cell_c1_r5', '1.0');
        $this->check_output_contains_text_input('table0_cell_c1_r6', '1.0');
        $this->check_output_contains_text_input('table0_cell_c1_r7', '1.0');
        $this->check_output_contains_text_input('table0_cell_c1_r8', '1.0');
        $this->check_output_contains_text_input('table0_cell_c1_r9', '1.0');
        $this->check_output_contains_text_input('table0_cell_c1_r10', '1.0');
        $this->check_current_output(
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_no_hint_visible_expectation());

        // Submit the right answer.
        $this->finish();


        // Verify.
        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(1);
        $this->render();
        $this->check_output_contains_text_input('table0_cell_c1_r5', '1.0', false);
        $this->check_output_contains_text_input('table0_cell_c1_r6', '1.0', false);
        $this->check_output_contains_text_input('table0_cell_c1_r7', '1.0', false);
        $this->check_output_contains_text_input('table0_cell_c1_r8', '1.0', false);
        $this->check_output_contains_text_input('table0_cell_c1_r9', '1.0', false);
        $this->check_output_contains_text_input('table0_cell_c1_r10', '1.0', false);
        $this->check_current_output(
                //$this->get_contains_standard_partiallycorrect_combined_feedback_expectation(),
                $this->get_no_hint_visible_expectation());
    }
}
