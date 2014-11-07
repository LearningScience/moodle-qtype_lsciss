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
 * lsspreadsheet question renderer class.
 *
 * @package    qtype
 * @subpackage lsspreadsheet
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
use Learnsci\Lsspreadsheet;
use Learnsci\LsspreadsheetCellGrader;
use Learnsci\LsspreadsheetUtils;
use Learnsci\LsspreadsheetChart;
use Learnsci\LsspreadsheetChartStats;

/**
 * Generates the output for lsspreadsheet questions.
 *
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_lsspreadsheet_renderer extends qtype_renderer {
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        $question = $qa->get_question();

        //moodle going throughtext and filtering
        $questiontext = $question->format_questiontext($qa);
        
        $spreadsheetUtils = new LsspreadsheetUtils();
        $html = $spreadsheetUtils->getTakeTableFromLsspreaddata($question->lsspreaddata, $qa->get_field_prefix() );

        // $placeholder = false;
        // if (preg_match('/_____+/', $questiontext, $matches)) {
        //     $placeholder = $matches[0];
        // }
        // $input = '**subq controls go in here**';

        // if ($placeholder) {
        //     $questiontext = substr_replace($questiontext, $input,
        //             strpos($questiontext, $placeholder), strlen($placeholder));
        // }

        $result = html_writer::tag('div', $questiontext . $html, array('class' => 'qtext'));

        /* if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                    $question->get_validation_error(array('answer' => $currentanswer)),
                    array('class' => 'validationerror'));
        }*/
        return $result;
    }

    public function specific_feedback(question_attempt $qa) {
        // TODO.
        return '';
    }

    public function correct_response(question_attempt $qa) {
        // TODO.
        return '';
    }
}
