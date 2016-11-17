<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$counter_name = "counter.txt";
// Check if a text file exists. If not create one and initialize it to zero.
if (!file_exists($counter_name)) {
  $f = fopen($counter_name, "w");
  $timezone = date_default_timezone_get();
  $date = date('m/d/Y h:i:s a', time());
  fwrite($f, "The current server timezone is: " . $timezone. ", file created at: " . $date . "\n" . "0");  
  fclose($f);
}
// Read the current value of our counter file
$f = fopen($counter_name,"r");
$content = fread($f, filesize($counter_name)); //the whole file including the header info
$contents = explode("\n", $content);
$info = $contents[0];
$counterVal = $contents[1];
fclose($f);

// Has visitor been counted in this session?
// If not, increase counter value by one
if(!isset($_SESSION['hasVisited'])){
  $_SESSION['hasVisited']="yes";
  $counterVal++;
  $f = fopen($counter_name, "w");
  fwrite($f, $info. "\n". $counterVal);
  fclose($f); 
}
