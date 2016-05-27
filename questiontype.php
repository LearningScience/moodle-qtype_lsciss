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
 * Question type class for the lsciss question type.
 *
 * @package   qtype_lsciss
 * @copyright 2016 Learning Science Ltd https://learnsci.co.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/lsciss/question.php');
require_once($CFG->dirroot . '/question/format/xml/format.php');

class qtype_lsciss extends question_type {

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
        $question->options = $DB->get_record('qtype_lsciss_options',
                array('questionid' => $question->id));
        return true;
    }

    public function save_question_options($question) {
        global $DB;
        $context = $question->context;

        $options = $DB->get_record('qtype_lsciss_options',
                array('questionid' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->lsspreaddata = '';
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->id = $DB->insert_record('qtype_lsciss_options', $options);
        }
    
        $options->lsspreaddata = $question->lsspreaddata;
        $options = $this->save_combined_feedback_helper($options, $question, $context);
        $DB->update_record('qtype_lsciss_options', $options);

        $this->save_hints($question);
    }

    public function delete_question($questionid, $contextid) {
        global $DB;
        return parent::delete_question($questionid, $contextid);
        $DB->delete_records('qtype_lsciss_options', array('questionid' => $questionid));
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->lsspreaddata = $questiondata->options->lsspreaddata;
        $this->initialise_combined_feedback($question, $questiondata);
    }

    public function get_random_guess_score($questiondata) {
        return 0;
    }

    public function can_analyse_responses() {
        return false;
    }

    public function get_possible_responses($questiondata) {
        return array();
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
        if (!isset($data['@']['type']) || $data['@']['type'] != 'lsciss') {
            return false;
        }

        $question = $format->import_headers($data);
        $question->qtype = 'lsciss';

        $question->lsspreaddata = $format->getpath($data,
                array('#', 'lsspreaddata', 0, '#'), '', false, 'lsspreaddata is required');

        $format->import_combined_feedback($question, $data, false);

        $format->import_hints($question, $data, false, false,
                $format->get_format($question->questiontextformat));

        return $question;
    }

    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $output = '';

        $output .= $format->write_combined_feedback($question->options,
                                                    $question->id,
                                                    $question->contextid);
        $output .= "    <lsspreaddata>\n" . $format->xml_escape($question->options->lsspreaddata) .
            "\n    </lsspreaddata>\n";

        return $output;
    }
}
