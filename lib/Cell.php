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
 * **Description here**
 *
 * @package   qtype_lsciss
 * @copyright 2016 Learning Science Ltd https://learnsci.co.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Learnsci;

class Cell {
  private $cellvalue;
  private $excelref;

  private $style;
  private $colspan;
  private $tdclass;
  private $feedbackstring;

  //make function
  public $celltype;
  public $response;
  public $correctanswer;
  public $feedbackClass;
  public $feedbackImage;
  public $iscorrect;
  public $row;
  public $col;
  public $textvalue;
  public $formula;
  public $feedback;
  public $labelalign;
  public $marks;
  public $chart;
  public $rangetype;
  public $rangeval;


  public function __set($name, $value) {
        throw new \Exception('Cannot add new property \$$name to instance of ' . __CLASS__);
  }

  public function __construct(){
    $this->celltype = '';
    $this->cellvalue = '';
    $this->response = '';
    $this->correctanswer = '';
    $this->feedbackClass = '';
    $this->feedbackImage = '';
    $this->iscorrect = null;
    $this->row = '';
    $this->col = '';
    $this->textvalue = '';
    $this->formula = '';
    $this->feedback = '';
    $this->labelalign = '';
    $this->marks = 0;
    $this->chart = '';
    $this->rangetype = '';
    $this->rangeval = 0;
  }


  public function getCellValue(){
    $text = is_null($this->textvalue) ? ''  : $this->textvalue;
    return html_entity_decode(str_ireplace('&nbsp;', ' ', $text));
  }

  public function getExcelRef(){
    $r = $this->row + 1;
    $c = $this->col;
    return \PHPExcel_Cell::stringFromColumnIndex($c) . $r;
  }

  public function initCellFromJsonObject($jsonCell){
      $this->col = $jsonCell['col'];
      $this->row = $jsonCell['row'];

      $this->textvalue = $jsonCell['textvalue'];
      $this->formula = $jsonCell['formula'];
      $this->feedback = $jsonCell['feedback'];
      $this->labelalign = '';
      $this->marks = 0;

      if (isset($jsonCell['chart'])) {
        $this->chart = $jsonCell['chart'];
      }
      $celltype = $jsonCell['celltype'];
      $range = $jsonCell['rangetype'];

      if ($celltype !== '') {
        $celltype = explode('_', $celltype);
        $this->celltype = $celltype[0];
        if ($this->celltype == 'Label') {
          if ((isset($celltype[1]) === false) || ($celltype[1] === '1')) {
            $this->labelalign = 'left';
          } else {
            $this->labelalign = $celltype[1];
          }
        } else if ($this->celltype == 'CalcAnswer') {
          $this->marks = $celltype[1];
          if ($range === '') {
            //Setting the defualt range is to make sure that any questions set with
            //the earliest versions of the javascript interface are OK
            $range = 'AbsoluteRange_0';
          }
        } 
      } 
      if ($range !== '') {
        //range data stored in one string using '_' as sep
        $range = explode('_', $range);
        $this->rangetype = $range[0];
        $this->rangeval = $range[1];
      }
  }

  public function getTdForCell($cellname, $numberOfColumns, $isReadOnly){
    $colspan = 1;
    $tdClass = '';

    $inLineTdStyle = '';
    switch ($this->celltype) {
      case 'CalcAnswer':
        $tdclass = 'lsCalcAnswerTd';
        if($isReadOnly === true){
          $inLineTdStyle .= ' style="text-align:center;background-color: #D3E6FF; border: 2px solid #6C7AB5;"';
        }
        $cellcontent = $this->getCellHtml('lsCalcAnswerInput', $cellname, $isReadOnly);
        break;
      case 'StudentInput':
        $tdclass = 'lsStudentInputTd';
        $cellcontent = $this->getCellHtml('lsInputStudentCell', $cellname, $isReadOnly);
        break;
      case 'Label':
        $inLineTdStyle .= ' style="text-align:' . $this->labelalign . ';"';
        $tdclass = 'lsLabelTd_' . $this->labelalign;
        $cellcontent = $this->getCellValue();
        break;
      case 'SectionHeading':
        $cellcontent = $this->getCellValue();
        $colspan = $numberOfColumns;
        $tdclass = 'lsTableSectionHeading';
        break;
      default:
        $tdclass = '';
        $cellcontent = '';
        
      }

    return '<td colspan=' . $colspan . ' class="' . $tdclass . '"'. $inLineTdStyle . '>' . $cellcontent . '</td>';
  }

  private function getInputTagCell($cssClass, $cellname){
    $styles = trim($cssClass);
    return '<input type="text" class="' . $styles  . '" ' . ' value="' . $this->response . '" id="' . $cellname . '" name="' . $cellname . '" style="width: 80px;"></input>';
  }

  private function getInputTagCellReadOnly($cssClass, $cellname){
    $styles = trim($cssClass);
    return '<input type="text" readonly="readonly" class="' . $styles  . '" ' .' value="' . $this->response . '" id="' . $cellname . '" name="' . $cellname . '" style="width: 80px;"></input>';
  }  

  private function getInputTagCellReadOnlyMarked($cssClass, $cellname){
    $styles = trim($cssClass . ' ' . $this->feedbackClass );
    return '<input type="text" readonly="readonly" class="' . $styles  . '" ' . ' value="' . $this->response . '" id="' . $cellname . '" name="' . $cellname . '" style="width: 80px;"></input>'. $this->feedbackImage . '<br/>' . '<span>' . $this->correctanswer . '</span>' . '<br>' . '<span>' . $this->feedback . '</span>';
  }

  private function getCellHtml($cssClass, $cellname, $isReadOnly){
    if($isReadOnly === true){
      return $this->getInputTagCellReadOnlyMarked($cssClass, $cellname);
    } else {
      return $this->getInputTagCell($cssClass, $cellname);
    }
  }

}