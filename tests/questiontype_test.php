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
 * Unit tests for the spreadsheet question type class.
 *
 * @package   qtype_lsspreadsheet
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/lsspreadsheet/questiontype.php');


/**
 * Unit tests for the spreadsheet question type class.
 *
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_lsspreadsheet_questiontype_test extends question_testcase {
    protected $qtype;

    protected function setUp() {
        $this->qtype = new qtype_lsspreadsheet();
    }

    protected function tearDown() {
        $this->qtype = null;
    }

    protected function assert_same_xml($expectedxml, $xml) {
        $this->assertEquals(str_replace("\r\n", "\n", $expectedxml),
                str_replace("\r\n", "\n", $xml));
    }

    public function test_name() {
        $this->assertEquals($this->qtype->name(), 'lsspreadsheet');
    }

    public function test_can_analyse_responses() {
        $this->assertFalse($this->qtype->can_analyse_responses());
    }

    public function test_get_random_guess_score() {
        $questiondata = test_question_maker::get_question_data('lsspreadsheet');;
        $this->assertEquals(0, $this->qtype->get_random_guess_score($questiondata));
    }

    public function test_get_possible_responses() {
        $questiondata = test_question_maker::get_question_data('lsspreadsheet');
        $this->assertEquals(array(), $this->qtype->get_possible_responses($questiondata));
    }

    public function test_xml_import() {
        $xml = $this->get_sample_xml();
        $xmldata = xmlize($xml);

        $importer = new qformat_xml();
        $q = $importer->try_importing_using_qtypes(
                $xmldata['question'], null, null, 'lsspreadsheet');

        $expectedq = test_question_maker::get_question_form_data('lsspreadsheet');
        $expectedq->questiontextformat = $expectedq->questiontext['format'];
        $expectedq->questiontext = $expectedq->questiontext['text'];
        $expectedq->generalfeedbackformat = $expectedq->generalfeedback['format'];
        $expectedq->generalfeedback = $expectedq->generalfeedback['text'];

        $this->assertEquals($expectedq->hint, $q->hint);
        $this->assert(new question_check_specified_fields_expectation($expectedq), $q);
    }

    public function test_xml_export() {
        $questiondata = test_question_maker::get_question_data('lsspreadsheet');

        $exporter = new qformat_xml();
        $xml = $exporter->writequestion($questiondata);

        $expectedxml = $this->get_sample_xml();

        $this->assert_same_xml($expectedxml, $xml);
    }

    /**
     * Get the Moodle XML format representation of the 'basic' sample question.
     * @return string XML fragment.
     */
    protected function get_sample_xml() {
        return '<!-- question: 0  -->
  <question type="lsspreadsheet">
    <name>
      <text>Spreadsheet question</text>
    </name>
    <questiontext format="html">
      <text>Fill in the boxes.</text>
    </questiontext>
    <generalfeedback format="html">
      <text>I hope you learned something.</text>
    </generalfeedback>
    <defaultgrade>1</defaultgrade>
    <penalty>0.3333333</penalty>
    <hidden>0</hidden>
    <correctfeedback format="html">
      <text>Well done!</text>
    </correctfeedback>
    <partiallycorrectfeedback format="html">
      <text>Parts, but only parts, of your response are correct.</text>
    </partiallycorrectfeedback>
    <incorrectfeedback format="html">
      <text>That is not right at all.</text>
    </incorrectfeedback>
    <lsspreaddata>
<![CDATA[[
    {
        "cell": {
            "table0_cell_c0_r10": {
                "celltype": "Label_1",
                "chart": "",
                "col": 0,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 10,
                "textvalue": "Volume&nbsp;of&nbsp;intracellular&nbsp;fluid&nbsp;compartment&nbsp;(l)"
            },
            "table0_cell_c0_r12": {
                "celltype": "None_undefined",
                "chart": "",
                "col": 0,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 12,
                "textvalue": ""
            },
            "table0_cell_c0_r13": {
                "celltype": "None_undefined",
                "chart": "",
                "col": 0,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 13,
                "textvalue": ""
            },
            "table0_cell_c0_r14": {
                "celltype": "None_undefined",
                "chart": "",
                "col": 0,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 14,
                "textvalue": ""
            },
            "table0_cell_c0_r3": {
                "celltype": "SectionHeading_1",
                "chart": "",
                "col": 0,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 3,
                "textvalue": "&lt;b&gt;TABLE&nbsp;1&nbsp;-&nbsp;VITAL&nbsp;STATISTICS&lt;/b&gt;"
            },
            "table0_cell_c0_r4": {
                "celltype": "SectionHeading_1",
                "chart": "",
                "col": 0,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 4,
                "textvalue": "&lt;br&gt;"
            },
            "table0_cell_c0_r5": {
                "celltype": "Label_1",
                "chart": "",
                "col": 0,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 5,
                "textvalue": "Gender&nbsp;(male/female)"
            },
            "table0_cell_c0_r6": {
                "celltype": "Label_1",
                "chart": "",
                "col": 0,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 6,
                "textvalue": "Body&nbsp;mass&nbsp;(kg)"
            },
            "table0_cell_c0_r7": {
                "celltype": "Label_1",
                "chart": "",
                "col": 0,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 7,
                "textvalue": "Height&nbsp;(m)"
            },
            "table0_cell_c0_r8": {
                "celltype": "Label_1",
                "chart": "",
                "col": 0,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 8,
                "textvalue": "BMI&nbsp;(kg/m&lt;sup&gt;2&lt;/sup&gt;)"
            },
            "table0_cell_c0_r9": {
                "celltype": "Label_1",
                "chart": "",
                "col": 0,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 9,
                "textvalue": "Volume&nbsp;of&nbsp;extracellular&nbsp;fluid&nbsp;compartment&nbsp;(l)"
            },
            "table0_cell_c1_r10": {
                "celltype": "CalcAnswer_1",
                "chart": "",
                "col": 1,
                "feedback": "",
                "formula": "=0.4*B7",
                "rangetype": "SigfigRange_2",
                "row": 10,
                "textvalue": "=0.4*B7"
            },
            "table0_cell_c1_r12": {
                "celltype": "None_undefined",
                "chart": "",
                "col": 1,
                "feedback": "",
                "formula": "",
                "rangetype": "AbsoluteRange_0",
                "row": 12,
                "textvalue": ""
            },
            "table0_cell_c1_r13": {
                "celltype": "None_undefined",
                "chart": "",
                "col": 1,
                "feedback": "",
                "formula": "",
                "rangetype": "AbsoluteRange_0",
                "row": 13,
                "textvalue": ""
            },
            "table0_cell_c1_r14": {
                "celltype": "None_undefined",
                "chart": "",
                "col": 1,
                "feedback": "",
                "formula": "",
                "rangetype": "AbsoluteRange_0",
                "row": 14,
                "textvalue": ""
            },
            "table0_cell_c1_r5": {
                "celltype": "StudentInput_1",
                "chart": "",
                "col": 1,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 5,
                "textvalue": ""
            },
            "table0_cell_c1_r6": {
                "celltype": "StudentInput_1",
                "chart": "",
                "col": 1,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 6,
                "textvalue": ""
            },
            "table0_cell_c1_r7": {
                "celltype": "StudentInput_1",
                "chart": "",
                "col": 1,
                "feedback": "",
                "formula": "",
                "rangetype": "",
                "row": 7,
                "textvalue": ""
            },
            "table0_cell_c1_r8": {
                "celltype": "CalcAnswer_1",
                "chart": "",
                "col": 1,
                "feedback": "",
                "formula": "=B7/(B8*B8)",
                "rangetype": "SigfigRange_3",
                "row": 8,
                "textvalue": "=B7/(B8*B8)"
            },
            "table0_cell_c1_r9": {
                "celltype": "CalcAnswer_1",
                "chart": "",
                "col": 1,
                "feedback": "",
                "formula": "=0.2*B7",
                "rangetype": "SigfigRange_2",
                "row": 9,
                "textvalue": "=0.2*B7"
            }
        },
        "chartdata": null,
        "metadata": {
            "columns": 2,
            "rows": 15,
            "title": ""
        }
    }
]]]>
    </lsspreaddata>
    <hint format="html">
      <text>Try again.</text>
    </hint>
    <hint format="html">
      <text>Hint 2.</text>
    </hint>
  </question>
';
    }
}
