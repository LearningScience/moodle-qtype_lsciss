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
 * Contains the helper class for the spread-sheet question type tests.
 *
 * @package   qtype_lsspreadsheet
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Question maker for unit tests for the spread-sheet question definition class.
 *
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_lsspreadsheet_test_helper extends question_test_helper {
    public function get_test_questions() {
        return array('basic');
    }

    /**
     * @return qtype_lsspreadsheet_question
     */
    public function make_lsspreadsheet_question_basic() {
        question_bank::load_question_definition_classes('lsspreadsheet');
        $lsss = new qtype_lsspreadsheet_question();

        test_question_maker::initialise_a_question($lsss);

        $lsss->name = 'Spread-sheet question';
        $lsss->questiontext = 'Fill in the boxes.';
        $lsss->generalfeedback = 'I hope you learned something.';
        $lsss->qtype = question_bank::get_qtype('lsspreadsheet');

        test_question_maker::set_standard_combined_feedback_fields($lsss);

        $lsss->lsspreaddata = file_get_contents(__DIR__ . '/fixtures/sample_sheet_data.json');

        return $lsss;
    }

    public function get_lsspreadsheet_question_data_basic() {
        global $USER;

        $q = new stdClass();
        test_question_maker::initialise_question_data($q);
        $q->name = 'Spread-sheet question';
        $q->qtype = 'lsspreadsheet';
        $q->parent = 0;
        $q->questiontext = 'Fill in the boxes.';
        $q->questiontextformat = FORMAT_HTML;
        $q->generalfeedback = 'I hope you learned something.';
        $q->generalfeedbackformat = FORMAT_HTML;
        $q->defaultmark = 1;
        $q->penalty = 0.3333333;
        $q->length = 1;
        $q->hidden = 0;
        $q->createdby = $USER->id;
        $q->modifiedby = $USER->id;

        $q->options = new stdClass();
        $q->options->lsspreaddata = file_get_contents(__DIR__ . '/fixtures/sample_sheet_data.json');
        test_question_maker::set_standard_combined_feedback_fields($q->options);

        return $q;
    }
}
