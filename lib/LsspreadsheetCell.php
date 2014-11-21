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
    $this->colspan = 1;
    $this->tdclass = "";
    $this->correctanswer = '';
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
    $tdClass = '';

    $inLineTdStyle = '';
    switch ($this->celltype) {
      case "CalcAnswer":
        $tdclass = "lsCalcAnswerTd";
        if($isReadOnly === true){
          //$inLineTdStyle .= ' style="border: 1px solid #000"';
          $inLineTdStyle .= ' style="text-align:center;background-color: #D3E6FF; border: 2px solid #6C7AB5;"';
        }
        $cellcontent = $this->getCellHtml('lsCalcAnswerInput', $cellname, $isReadOnly);
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

    return "\n  <td colspan=" . $colspan . " class=\"" . $tdclass . "\"". $inLineTdStyle . ">" . $cellcontent . "</td>";
    //return "\n  <td colspan=" . $colspan . " class=" . $tdclass . ">" . $cellcontent . "</td>";
  }

  private function getInputTagCell($cssClass, $cellname){
    $styles = trim($cssClass . $this->style);
    return '<input type="text" class="' . $styles  . '" ' . ' value="' . $this->response . '" id="' . $cellname . '" name="' . $cellname . '"></input>' . $this->markedimg;
  }

  private function getInputTagCellReadOnly($cssClass, $cellname){
    $styles = trim($cssClass . $this->style);
    return '<input type="text" readonly="readonly" class="' . $styles  . '" ' .' value="' . $this->response . '" id="' . $cellname . '" name="' . $cellname . '"></input>' . $this->markedimg;
  }  

  private function getInputTagCellReadOnlyMarked($cssClass, $cellname){
    $styles = trim($cssClass . $this->style . ' ' . $this->feedbackClass );
    return '<input type="text" readonly="readonly" class="' . $styles  . '" ' . ' value="' . $this->response . '" id="' . $cellname . '" name="' . $cellname . '"></input>'. $this->feedbackImage . '<br/>' . '<span>' . $this->correctanswer . '</span>';
  }

  private function getCellHtml($cssClass, $cellname, $isReadOnly){
    if($isReadOnly === true){
      return $this->getInputTagCellReadOnlyMarked($cssClass, $cellname);
    } else {
      return $this->getInputTagCell($cssClass, $cellname);
    }
  }

}