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
 * Defines the editing form for the lsspreadsheet question type.
 *
 * @package    qtype
 * @subpackage lsspreadsheet
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * lsspreadsheet question editing form definition.
 *
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_lsspreadsheet_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        $mform->addElement('textarea', 'lsspreaddata', 'Spreadsheet JSON');

        $this->add_combined_feedback_fields();
        $this->add_interactive_settings();
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_combined_feedback($question);
        $question = $this->data_preprocessing_hints($question);
        if (isset($question->options->lsspreaddata)) {
            $question->lsspreaddata = $question->options->lsspreaddata;
        }
        return $question;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (json_decode($data['lsspreaddata']) === null) {
            $errors['lsspreaddata'] = 'Invalid JSON.';
        }

        return $errors;
    }

    public function qtype() {
        return 'lsspreadsheet';
    }
}
