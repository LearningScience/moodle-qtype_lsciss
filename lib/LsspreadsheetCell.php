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

  public function getTdForCell($cellname, $numberOfColumns){
    $colspan = 1;
    switch ($this->celltype) {
      case "FixedAnswer":
        $tdclass = '';
        $cellcontent = $this->getInputTagCell('', $cellname);
        break;
      case "CalcAnswer":
        $tdclass = "lsCalcAnswerTd";
        $cellcontent = $this->getInputTagCell('lsCalcAnswerInput', $cellname);
        break;
      case "NumberAnswer":
        $cellcontent = $this->getInputTagCell('', $cell);
        break;
      case "StudentInput":
        $tdclass = "lsStudentInputTd";
        $cellcontent = $this->getInputTagCell('lsInputStudentCell', $cellname);
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

}