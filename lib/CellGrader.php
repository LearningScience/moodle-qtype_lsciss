<?php
namespace Learnsci;

class CellGrader {

  public function __construct(){}

  public function getSigFigCellCorrectness($submitted_answer, $correct_answer, $rangeval){
    $answer = new \stdClass();
    $answer->correctanswer = "";
    $rounded_submitted_answer = $this->toPrecision($submitted_answer, $rangeval);
    $rounded_correct_answer = $this->toPrecision($correct_answer, $rangeval);
    $answer->correctanswer = " " . $rounded_correct_answer . " to " . $rangeval . " sig. fig";

    $answer->feedbackstring = " " . $rounded_correct_answer . " to " . $rangeval . " sig. fig";
    //$differenceFromCorrectAnswer = $rounded_submitted_answer - $rounded_correct_answer;

    //Add a 2% (percent) leeway for students carrying exact numbers through
    //an equation.
    $percentage_leeway = 2.0;

    if ($rounded_correct_answer === 0) {
      //We want to avoid divide by zero errors!
      $leewayValue = 0.0;
    } else {
      $leewayValue = (($rounded_correct_answer / 100.0) * $percentage_leeway);
    }

    //flip the ranges if value is negative
    if($rounded_correct_answer >= 0){
      $upper_correct_range = $rounded_correct_answer + $leewayValue;
      $lower_correct_range = $rounded_correct_answer - $leewayValue;
    }else{
      $upper_correct_range = $rounded_correct_answer - $leewayValue;
      $lower_correct_range = $rounded_correct_answer + $leewayValue;
    }

    if ($rounded_correct_answer < 0) {
      if (($rounded_submitted_answer >= $upper_correct_range)
        && ($rounded_submitted_answer <= $lower_correct_range)) {
        //Answer is correct within a 2% leeway
        $answer->iscorrect = true;
      } else {
        $answer->iscorrect = false;
      }
    } else {
      if (($rounded_submitted_answer <= $upper_correct_range)
        && ($rounded_submitted_answer >= $lower_correct_range)) {
        //Answer is correct within a 2% leeway
        $answer->iscorrect = true;
      } else {
        $answer->iscorrect = false;
      }
    }

    return $answer;
  }

  /**
   * Rounding to significant digits ( just like JS toPrecision() )
   *
   * @number <float> value to round
   * @sf <int> Number of significant figures
   */
  public function toPrecision($number, $sf) {
    // How many decimal places do we round and format to?
    // @note May be negative.
    $dp = floor($sf - log10(abs($number)));

    // Round as a regular number.
    $numberFinal = round($number, $dp);

    //If the original number it's halp up rounded, don't need the last 0
    $arrDecimais = explode('.', $numberFinal);
    if (!isset($arrDecimais[1])) {
      $arrDecimais[1] = "";
    }
    if (strlen($number) > strlen($numberFinal) && $dp > strlen($arrDecimais[1])) {
      $valorFinal = sprintf("%." . ($dp - 1) . "f", $number);
    } else {
      //Leave the formatting to format_number(), but always format 0 to 0dp.
      $valorFinal = str_replace(',', '', number_format($numberFinal, 0 == $numberFinal ? 0 : $dp));
    }

    // Verify if needs to be represented in scientific notation
    $arrDecimaisOriginal = explode('.', $number);
    if (sizeof($arrDecimaisOriginal) >= 2) {
      return (strlen($arrDecimaisOriginal[0]) > $sf) ?
      sprintf("%." . ($sf - 1) . "f", $valorFinal) :
      $valorFinal;
    } else {
      return sprintf("%." . ($sf - 1) . "f", $valorFinal);
    }
  }

  public function getAbsoluteCellCorrectness($submitted_answer, $correct_answer, $rangeval, $correct_answer_string, $num_decimals){
    $answer = new \stdClass();
    $answer->correctanswer = "";
    //$submitted_answer = $submitted_answer;
    if ($rangeval != 0) {
      $answer->correctanswer = "  between " . sprintf("%.2f", ($correct_answer - $rangeval)) . " and " . sprintf("%.2f", (($correct_answer + $rangeval)));
    } else {
      $answer->correctanswer = " " . $correct_answer_string . " exactly ";
    }

    $correct_answer = round($correct_answer, $num_decimals);

    //cast $correct_answer to string so both are same type for comparison
    if (($submitted_answer === ((String)$correct_answer)) ||
      ($submitted_answer >= ($correct_answer - $rangeval)) && ($submitted_answer <= ($correct_answer + $rangeval))) {
      $answer->iscorrect = true;
    } else {
      $answer->iscorrect = false;
    }
    return $answer;
  }

  public function getDecimalCorrectness($submitted_answer, $correct_answer, $rangeval){
    $answer = new \stdClass();
    $answer->correctanswer = "";
    $rounded_submitted_answer = round($submitted_answer, $rangeval);
    $rounded_correct_answer = round($correct_answer, 0 + $rangeval);

    $answer->correctanswer = " " . number_format($rounded_correct_answer, $rangeval, ".", "") . " to " . $rangeval . " dec. places";

    //Add a 2% (percent) leeway for students carrying exact numbers through
    //an equation.
    $percentage_leeway = 2.0;

    if ($rounded_correct_answer === 0) {
      //We want to avoid divide by zero errors!
      $leewayValue = 0.0;
    } else {
      $leewayValue = (($rounded_correct_answer / 100.0) * $percentage_leeway);
    }

    //flip the ranges if value is negative
    if($rounded_correct_answer >= 0){
      $upper_correct_range = $rounded_correct_answer + $leewayValue;
      $lower_correct_range = $rounded_correct_answer - $leewayValue;
    }else{
      $upper_correct_range = $rounded_correct_answer - $leewayValue;
      $lower_correct_range = $rounded_correct_answer + $leewayValue;
    }

    if (($rounded_submitted_answer <= $upper_correct_range)
      && ($rounded_submitted_answer >= $lower_correct_range)) {
      //Answer is correct within a 2% leeway
      $answer->iscorrect = true;
    } else {
      $answer->iscorrect = false;
    }

    return $answer;
  }

  public function getPercentCellCorrectness($submitted_answer, $correct_answer, $rangeval){
    $answer = new \stdClass();
    $answer->correctanswer = "";
    $tolerance = ($correct_answer / 100.0) * $rangeval;
    $lower_range = $correct_answer - $tolerance;
    $upper_range = $correct_answer + $tolerance;

    $answer->correctanswer = "  " . $rangeval . "% error allowed";

    if (($submitted_answer >= $lower_range) and ($submitted_answer <= $upper_range)) {
      $answer->iscorrect = true;
    } else {
      $answer->iscorrect = false;
    }
    return $answer;
  }
}
