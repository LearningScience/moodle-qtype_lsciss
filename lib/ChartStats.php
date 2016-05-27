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

/**
 * Description of ChartStats
 *
 * @author steve
 */
class ChartStats {

	/**
	 * linear regression function
	 * @param $x array x-coords
	 * @param $y array y-coords
	 * @returns object() $linearregression->slope, $linearregression->intercept
	 */
	public static function get_lsspreadsheet_stats($x, $y) {

		$stats->x = $x;
		$stats->y = $y;

		if (!is_array($stats->x)) {
			return false;
		}

		if (count(array_filter($stats->x, 'strlen')) == 0) {
			return false;
		}

		$stats->linearregression = self::linear_regression($x, $y);
		$stats->rsquared = self::r_squared($x, $y);
		$stats->linebestfit = self::line_bestfit($stats);

		return $stats;
	}

	private static function line_bestfit($stats) {

		try
		{
			$linebestfit->x1 = min(array_filter($stats->x, 'strlen'));
			$linebestfit->x2 = max($stats->x);
			$linebestfit->y1 = $stats->linearregression->slope * $linebestfit->x1 + $stats->linearregression->intercept;
			$linebestfit->y2 = $stats->linearregression->slope * $linebestfit->x2 + $stats->linearregression->intercept;

			return $linebestfit;
		} catch (Exception $e) {
			$linebestfit->x1 = 0;
			$linebestfit->x2 = 0;
			$linebestfit->y1 = 0;
			$linebestfit->y2 = 0;
			return $linebestfit;
		}
	}

	private static function linear_regression($x, $y) {

		$stats = self::get_stats_sums($x, $y);

		// calculate slope

		$linear_regression_numerator = (($stats->n * $stats->xy_sum)-($stats->x_sum * $stats->y_sum));
		$linear_regression_denominator = (($stats->n * $stats->xx_sum)-($stats->x_sum * $stats->x_sum));

		if ($linear_regression_denominator === 0) {
			$linearregression->slope = null;
		} else {
			$linearregression->slope = $linear_regression_numerator / $linear_regression_denominator;
		}
		// calculate intercept

		if ($stats->n == 0) {
			$linearregression->intercept = 0;
		} else {
			$linearregression->intercept = ($stats->y_sum-($linearregression->slope * $stats->x_sum)) / $stats->n;
		}
		// return result
		return $linearregression;
	}

	private static function get_stats_sums($x, $y) {

		// calculate number points
		$stats->n = count(array_filter($x, 'strlen'));

		// ensure both arrays of points are the same size
		if ($stats->n != count(array_filter($y, 'strlen'))) {

			return false;
		}

		// calculate sums
		$stats->x_sum = array_sum($x);
		$stats->y_sum = array_sum($y);

		$stats->xx_sum = 0;
		$stats->xy_sum = 0;

		for ($i = 0; $i < $stats->n; $i++) {
			$stats->xy_sum += ($x[$i] * $y[$i]);
			$stats->xx_sum += ($x[$i] * $x[$i]);
			$stats->yy_sum += ($y[$i] * $y[$i]);
		}

		return $stats;
	}

	private static function r_squared($x, $y) {

		// calculate number points
		$stats = self::get_stats_sums($x, $y);

		$rsquared_numerator = ($stats->n * $stats->xy_sum)-($stats->x_sum * $stats->y_sum);

		$a = sqrt(($stats->n * $stats->xx_sum)-($stats->x_sum * $stats->x_sum));
		$b = sqrt(($stats->n * $stats->yy_sum)-($stats->y_sum * $stats->y_sum));

		$rsquared_denominator = $a * $b;

		try
		{

			if (($rsquared_numerator == 0) || ($rsquared_denominator == 0)) {
				return false;
			}

			$r = $rsquared_numerator / $rsquared_denominator;

			$rsquared->value = pow($r, 2);

// return result
			return $rsquared;
		} catch (Exception $e) {
			return false;
		}
	}

}

?>
