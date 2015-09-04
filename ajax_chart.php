<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// Standard inclusions  
$loadChart = true;
if (isset($_GET['data'])) $data = json_decode($_GET['data']);
$data = json_decode('{"xseries":[1,2,3,null,null,null,null,null,null,null,null],"yseries":[1,2,3,null,null,null,null,null,null,null,null],"title":"","xaxistitle":"xaxistitle","yaxistitle":"Absorbance at 500 nm","urlsalt":0}');

require_once(dirname(__FILE__) . '/lib/Chart.php');

?>
