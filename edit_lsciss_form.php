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
 * Defines the editing form for the lsciss question type.
 *
 * @package   qtype_lsciss
 * @copyright 2016 Learning Science Ltd https://learnsci.co.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * lsciss question editing form definition.
 *
 * @copyright  2016 Learning Science Ltd https://learnsci.co.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_lsciss_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        global $PAGE, $CFG;
        //$PAGE->requires->jquery();

        $PAGE->requires->js("/question/type/lsciss/js/jquery-1.4.2.min.js");
        $PAGE->requires->js("/question/type/lsciss/js/jquery.tooltip.js");
        $PAGE->requires->js("/question/type/lsciss/js/jquery.dimensions.min.js");

        $PAGE->requires->js("/question/type/lsciss/js/jquery.sheet.js");
        $PAGE->requires->js("/question/type/lsciss/js/jquery.json-2.2.min.js");
        $PAGE->requires->js("/question/type/lsciss/js/plugins/mbMenu.min.js");
        $PAGE->requires->js("/question/type/lsciss/js/plugins/jquery.scrollTo-min.js");
        $PAGE->requires->js("/question/type/lsciss/js/jquery-ui-1.8.custom.min.js");
        $PAGE->requires->js("/question/type/lsciss/js/lsspreadsheet.js");

        $PAGE->requires->css("/question/type/lsciss/styles/jquery.sheet.css");
        $PAGE->requires->css("/question/type/lsciss/styles/styles.css");

        $htmlOfEditor = file_get_contents($CFG->dirroot . "/question/type/lsciss/html_snippets/spreadsheet_editor.html");
        $questionWWWroot = $CFG->wwwroot . "/question/type/lsciss";
        $htmlOfEditor = str_replace('#questionWWWroot#', $questionWWWroot, $htmlOfEditor);
        $mform->addElement('html', $htmlOfEditor);
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
        return 'lsciss';
    }
}
