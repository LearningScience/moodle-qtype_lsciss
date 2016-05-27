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
 * lsciss question renderer class.
 *
 * @package    qtype
 * @subpackage lsciss
 * @copyright  2016 Learning Science Ltd https://learnsci.co.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
use Learnsci\Spreadsheet;
use Learnsci\CellGrader;
use Learnsci\LsspreadsheetUtils;
use Learnsci\Chart;
use Learnsci\ChartStats;

/**
 * Generates the output for lsciss questions.
 *
 * @copyright  2016 Learning Science Ltd https://learnsci.co.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_lsciss_renderer extends qtype_renderer {
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        global $CFG; 
        $question = $qa->get_question();

        //moodle going throughtext and filtering
        $questiontext = $question->format_questiontext($qa);
        
        $spreadSheet = new Spreadsheet();
        $spreadSheet->setJsonStringFromDb($question->lsspreaddata);
        $graded = $spreadSheet->grade_spreadsheet_question($qa->get_last_qt_data());

        $chart = new Chart();
        $chartJS = $chart->get_chart_javascript($question->id, $CFG->wwwroot, '', '');

        //$showChart ='&nbsp;<img alt="" src="'.$CFG->wwwroot.'/question/type/lsciss/ajax_chart.php" /> ';
        $feedbackStyles = [
            'correctFeedbackClass' => $this->feedback_class(1),
            'correctFeedbackImage' => $this->feedback_image(1),
            'wrongFeedbackClass' => $this->feedback_class(0),
            'wrongFeedbackImage' => $this->feedback_image(0)
        ];


        $html = $spreadSheet->getTakeTableFromLsspreaddata($qa->get_field_prefix(), $options, $qa, $graded, $feedbackStyles);
        //$html .= $showChart;
        $html .= '<div style="clear:both;"></div>';

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
