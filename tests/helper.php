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
 * Contains the helper class for the spreadsheet question type tests.
 *
 * @package   qtype_lsciss
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Question maker for unit tests for the spreadsheet question definition class.
 *
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_lsciss_test_helper extends question_test_helper {
    public function get_test_questions() {
        return array('basic');
    }

    /**
     * @return qtype_lsciss_question
     */
    public function make_lsciss_question_basic() {
        question_bank::load_question_definition_classes('lsciss');
        $lsss = new qtype_lsciss_question();

        test_question_maker::initialise_a_question($lsss);

        $lsss->name = 'Spreadsheet question';
        $lsss->questiontext = 'Fill in the boxes.';
        $lsss->generalfeedback = 'I hope you learned something.';
        $lsss->qtype = question_bank::get_qtype('lsciss');

        test_question_maker::set_standard_combined_feedback_fields($lsss);
        unset($lsss->shownumcorrect);

        $lsss->lsspreaddata = file_get_contents(__DIR__ . '/fixtures/sample_sheet_data.json');

        $lsss->hints = array(
            new question_hint(1, 'Try again.', FORMAT_HTML),
            new question_hint(2, 'Hint 2.', FORMAT_HTML),
        );

        return $lsss;
    }

    public function get_lsciss_question_data_basic() {
        global $USER;

        $q = new stdClass();
        test_question_maker::initialise_question_data($q);
        $q->name = 'Spreadsheet question';
        $q->qtype = 'lsciss';
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
        unset($q->options->shownumcorrect);

        $q->hints = array(
            new question_hint(1, 'Try again.', FORMAT_HTML),
            new question_hint(2, 'Hint 2.', FORMAT_HTML),
        );

        return $q;
    }

    public function get_lsciss_question_form_data_basic() {
        global $USER;

        $fromform = new stdClass();
        $fromform->name = 'Spreadsheet question';
        $fromform->qtype = 'lsciss';
        $fromform->questiontext = array('text' => 'Fill in the boxes.', 'format' => FORMAT_HTML);
        $fromform->generalfeedback = array('text' => 'I hope you learned something.', 'format' => FORMAT_HTML);
        $fromform->defaultmark = 1;
        $fromform->penalty = 0.3333333;

        $fromform->lsspreaddata = file_get_contents(__DIR__ . '/fixtures/sample_sheet_data.json');
        test_question_maker::set_standard_combined_feedback_form_data($fromform);
        unset($fromform->shownumcorrect);

        $fromform->hint = array(
            array('text' => 'Try again.', 'format' => FORMAT_HTML),
            array('text' => 'Hint 2.', 'format' => FORMAT_HTML),
        );

        return $fromform;
    }
}
