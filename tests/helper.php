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
        $lsss->questiontext = '.';
        $lsss->generalfeedback = 'I hope you learned something.';
        $lsss->qtype = question_bank::get_qtype('lsspreadsheet');


        // test_question_maker::set_standard_combined_feedback_fields($lsss);

        $lsss->lsspreaddata = '[
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
]';

        return $lsss;
    }
}
