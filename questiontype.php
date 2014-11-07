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
 * Question type class for the lsspreadsheet question type.
 *
 * @package   qtype_lsspreadsheet
 * @copyright THEYEAR YOURNAME (YOURCONTACTINFO)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/lsspreadsheet/question.php');


/**
 * The lsspreadsheet question type.
 *
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_lsspreadsheet extends question_type {

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_combined_feedback($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_combined_feedback($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    public function get_question_options($question) {
        global $DB;
        parent::get_question_options($question);
        $question->options = $DB->get_record('qtype_lsspreadsheet_options',
                array('questionid' => $question->id));
        return true;
    }

    public function save_question_options($question) {
        global $DB;
        $context = $question->context;

        $options = $DB->get_record('qtype_lsspreadsheet_options',
                array('questionid' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->lsspreaddata = '';
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->id = $DB->insert_record('qtype_lsspreadsheet_options', $options);
        }
    
        $options->lsspreaddata = $question->lsspreaddata;
        $options = $this->save_combined_feedback_helper($options, $question, $context);
        $DB->update_record('qtype_lsspreadsheet_options', $options);

        $this->save_hints($question);
    }

    public function delete_question($questionid, $contextid) {
        global $DB;
        return parent::delete_question($questionid, $contextid);
        $DB->delete_records('qtype_lsspreadsheet_options', array('questionid' => $questionid));
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        // TODO.
        parent::initialise_question_instance($question, $questiondata);
    }

    public function get_random_guess_score($questiondata) {
        return 0;
    }

    public function get_possible_responses($questiondata) {
        // TODO.
        return array();
    }
}
