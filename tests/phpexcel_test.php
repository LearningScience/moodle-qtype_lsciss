<?php

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/question/type/lsspreadsheet/phpexcel/PHPExcel.php');

class PhpExcelTest extends PHPUnit_Framework_TestCase {

	public function testCreateInstance() {
    $excel = new \PHPExcel();
    \PHPExcel_Calculation::getInstance()->clearCalculationCache();
    $excel->getActiveSheet()->setCellValue('A1', '3');
    $excel->getActiveSheet()->setCellValue('B1', '2');
    $excel->getActiveSheet()->setCellValue('C1', '=A1*B1');
    $calc = $excel->getActiveSheet()->getCell('C1')->getCalculatedValue();
    $this->assertEquals($calc, 6);
    // $objWriter = \PHPExcel_IOFactory::createWriter($excel, "Excel2007");
    // $objWriter->save("/tmp/phpExcelTest.xlsx");
    \PHPExcel_Calculation::getInstance()->clearCalculationCache();
	}

}