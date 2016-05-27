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


// Standard inclusions  
$loadChart = true;
if (isset($_GET['data'])) $data = json_decode($_GET['data']);
$data = json_decode('{"xseries":[1,2,3,null,null,null,null,null,null,null,null],"yseries":[1,2,3,null,null,null,null,null,null,null,null],"title":"","xaxistitle":"xaxistitle","yaxistitle":"Absorbance at 500 nm","urlsalt":0}');

require_once(dirname(__FILE__) . '/lib/Chart.php');

?>
