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
 * lsciss question definition class.
 *
 * @package    qtype
 * @subpackage lsciss
 * @copyright  2016 Learning Science Ltd https://learnsci.co.uk

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/lsciss/lib/Spreadsheet.php');
require_once($CFG->dirroot . '/question/type/lsciss/lib/Cell.php');
require_once($CFG->dirroot . '/question/type/lsciss/lib/CellGrader.php');
require_once($CFG->dirroot . '/question/type/lsciss/lib/Chart.php');
require_once($CFG->dirroot . '/question/type/lsciss/lib/ChartStats.php');
require_once($CFG->dirroot . '/question/type/lsciss/phpexcel/PHPExcel.php');
use Learnsci\Spreadsheet;


/**
 * Represents a lsciss question.
 *
 * @copyright  2016 Learning Science Ltd https://learnsci.co.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_lsciss_question extends question_graded_automatically {

    /** @var string JSON of the spreadsheet. */
    public $lsspreaddata;

    public function get_expected_data() {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setJsonStringFromDb($this->lsspreaddata);
        $fields = $spreadsheet->get_field_names();

        $expected = array();
        foreach ($fields as $name) {
            $expected[$name] = PARAM_RAW_TRIMMED;
        }

        return $expected;
    }
    /**
     *  test that all cells have been filled in for all responses
     */
    public function is_complete_response(array $response) {
        $complete = true;
        $k=0;
        $testComplete = array();
        foreach ($this->get_expected_data() as $name => $notused) {
        $testComplete[$k] = 'true';
            if (!array_key_exists($name, $response) ||
                    (!$response[$name] && $response[$name] !== '0')) {$testComplete[$k] = 'false';
        //  return false;
            }
        $k++;
        }
        if (!in_array('true', $testComplete)) return false;
        return true;
    }

    public function get_validation_error(array $response) {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return get_string('pleaseananswerallparts', 'qtype_lsciss');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        // TODO.
        return question_utils::arrays_have_same_keys_and_values(
                $prevresponse, $newresponse);
    }

    public function get_correct_response() {
        return null;
    }

    public function summarise_response(array $response) {
        $parts = array();
        foreach ($this->get_expected_data() as $name => $notused) {
            if (array_key_exists($name, $response) &&
                    ($response[$name] || $response[$name] === '0')) {
                $parts[] = str_replace('table0_cell_', '', $name). ': ' . $response[$name];
            }
        }
        return implode(', ', $parts);
    }

    public function check_file_access($qa, $options, $component, $filearea,
            $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }

    public function grade_response(array $response) {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setJsonStringFromDb($this->lsspreaddata);
        $fraction = $spreadsheet->get_fractional_grade($response);
        
        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }
}
