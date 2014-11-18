<?php
namespace Learnsci;

class LsspreadsheetCell 
{
  private $cellvalue;
  private $excelref;

  public function __construct(){
    $this->celltype = "";
    $this->cellvalue = "";
    $this->cellname = "";
    $this->style = "";
    $this->markedimg = "";
    $this->correct_value = "";
    $this->response = '';
    $this->popup = "";
    $this->colspan = 1;
    $this->tdclass = "";
    $this->correctAnswer = '';
    $this->feedbackstring = '';
    $this->feedbackClass = '';
    $this->feedbackImage = '';
    $this->iscorrect = null;
    $this->submitted_anser = '';
    $this->row = '';
    $this->col = '';
  }


  public function getCellValue()
  {
    return html_entity_decode(str_ireplace('&nbsp;', " ", $this->textvalue));
  }

  public function getExcelRef()
  {
    $r = $this->row + 1;
    $c = $this->col;
    return \PHPExcel_Cell::stringFromColumnIndex($c) . $r;
  }

  public function getTdForCell($cellname, $numberOfColumns, $isReadOnly){
    $colspan = 1;
    switch ($this->celltype) {
      case "FixedAnswer":
        $tdclass = '';
        $cellcontent = $this->getCellHtml('', $cellname, $isReadOnly);
        break;
      case "CalcAnswer":
        $tdclass = "lsCalcAnswerTd";
        $cellcontent = $this->getCellHtml('lsCalcAnswerInput', $cellname, $isReadOnly);
        break;
      case "NumberAnswer":
        $cellcontent = $this->getCellHtml('', $cell);
        break;
      case "StudentInput":
        $tdclass = "lsStudentInputTd";
        $cellcontent = $this->getCellHtml('lsInputStudentCell', $cellname, $isReadOnly);
        break;
      case "Label":
        $tdclass = "lsLabelTd_" . $this->labelalign;
        $cellcontent = $this->getCellValue();
        break;
      case "SectionHeading":
        $cellcontent = $this->getCellValue();
        $colspan = $numberOfColumns;
        $tdclass = "lsTableSectionHeading";
        break;
      default:
        $tdclass = '';
        $cellcontent = "";
        
      }
    return "\n  <td colspan=" . $colspan . " class=" . $tdclass . ">" . $cellcontent . "</td>";
  }

  private function getInputTagCell($cssClass, $cellname){
    $styles = trim($cssClass . $this->style);
    return '<input type="text" class="' . $styles  . '" ' . $this->popup . ' value="' . $this->response . '" id="' . $cellname . '" name="' . $cellname . '"></input>' . $this->markedimg;
  }

  private function getInputTagCellReadOnly($cssClass, $cellname){
    $styles = trim($cssClass . $this->style);
    return '<input type="text" readonly="readonly" class="' . $styles  . '" ' . $this->popup . ' value="' . $this->response . '" id="' . $cellname . '" name="' . $cellname . '"></input>' . $this->markedimg;
  }  

  private function getInputTagCellReadOnlyMarked($cssClass, $cellname){
    $styles = trim($cssClass . $this->style . ' ' . $this->feedbackClass );
    return '<input type="text" readonly="readonly" class="' . $styles  . '" ' . $this->popup . ' value="' . $this->response . '" id="' . $cellname . '" name="' . $cellname . '"></input>'. $this->feedbackImage . ' ' . $this->correctanswer;
  }

  private function getCellHtml($cssClass, $cellname, $isReadOnly){
    if($isReadOnly === true){
      return $this->getInputTagCellReadOnlyMarked($cssClass, $cellname);
    } else {
      return $this->getInputTagCell($cssClass, $cellname);
    }
  }

}