<?php

/* This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

ini_set('memory_limit', '-1'); //remove memory limit
error_reporting(E_ERROR | E_PARSE);
include 'visitorcounter.php';
include 'featurlist.php';
//include 'globalvariables.php';
include 'crossvalidation.php';
include 'mpdvalidation.php';
include 'mpdprocessing.php';
include 'mpdparsing.php';
include 'datadownload.php';
include 'assemble.php';
include 'schematronIssuesAnalyzer.php';
include 'compare.php';

set_time_limit(0); // php run without time limit
ini_set("log_errors", 1);
ini_set("error_log", "myphp-error.log");

$session_id = json_decode($_POST['sessionid']); // parse recieved data
session_name($session_id);
session_start(); // initiate session for connected client
error_log("session_start:" . session_name());
start_visitor_counter();

$adaptsetdepth = array(); // array for Baseurl 
$depth = array(); //array contains all relative URLs exist in all mpd levels 
$locate;  // location of session folder on server
$foldername; // floder name for the session
$Adapt_urlbase = 0; // Baseurl in adaptationset
$id = array();  //mpd id
$codecs = array();
$width = array();
$height = array();
$period_baseurl = array(); // all baseURLs included in given period
$scanType = array();
$frameRate = array();
$sar = array();
$bandwidth = array();
$Adaptationset = array(); //array of all attributes in single adapatationset
$Adapt_arr = array(); //array of all adaptationsets within 1 period
$Period_arr = array(); // array of all periods 
$init_flag; // flag decide if this is the first connection attempt
$repnolist = array(); // list of number of representation
$period_url = array(); // array contains location of all segments within period
$perioddepth = array(); //array with all relative baseurls up to period level
$type = "";
$minBufferTime = "";
$profiles = "";
$mediaPresentationDuration = "";
$count1 = 0; // Count number of adaptationsets processed
$count2 = 0; //count number of presentations proceessed

if (isset($_POST['urlcode']))
{// if client initiate first connection
    $url_array = json_decode($_POST['urlcode']); // parse recieved data
    $url = $url_array[0]; // get mpd url from HTTP request
    $_SESSION['url'] = $url; // save mpd url to session variable
    unset($_SESSION['period_url']); // reset session variable 'period_url' in order to remove any old segment url from previous sessions
    unset($_SESSION['init_flag']); // reset for flag indicating first connection attempt
}
if (isset($_SESSION['locate'])) //get location from session variable if it is not secont  attempt to access server by same session
    $locate = $_SESSION['locate'];

if (isset($_SESSION['count1']))//get Adaptationset counter in access
    $count1 = $_SESSION['count1'];

if (isset($_SESSION['foldername']))//get folder name from session
    $foldername = $_SESSION['foldername'];

if (isset($_SESSION['count2']))//get presentation counter
    $count2 = $_SESSION['count2'];

if (isset($_SESSION['url']))//get mpd url from session variable
    $url = $_SESSION['url'];

if (isset($_SESSION['period_url']))//get period url from session variable
    $period_url = $_SESSION['period_url'];

if (isset($_SESSION['init_flag']))//check access flag status
    $init_flag = $_SESSION['init_flag'];

if (isset($_SESSION['Period_arr'])) //get array of periods in case of already processed 
    $Period_arr = $_SESSION['Period_arr'];

if (isset($_SESSION['type']))
    $type = $_SESSION['type'];

if (isset($_SESSION['minBufferTime']))
    $minBufferTime = $_SESSION['minBufferTime'];


$string_info = '<!doctype html> 
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Log detail</title>
  <style>
  p {
    color: blue;
    margin: 8px;
  }
  </style>
  <script src="https://code.jquery.com/jquery-1.9.1.js"></script>
</head>
<body>
 
<p>Processing...</p>
 
<script>
window.onload = tester;

function tester(){
var url = document.URL.split("/");
var newPathname = url[0];
var loc = window.location.pathname.split("/");
for ( i = 1; i < url.length-3; i++ ) {
  newPathname += "/";
  newPathname += url[i];
}
var location = newPathname+"/give.php";
$.post (location,
{val:loc[loc.length-2]+"/$Template$"},
function(result){
resultant=JSON.parse(result);
var end = "";
for(var i =0;i<resultant.length;i++)
{

resultant[i]=resultant[i]+"<br />";
end = end+" "+resultant[i];
$( "p" ).html( end);
}
});

}
</script>
 
</body>
</html>';

function print_r2($val)
{ //Print output line by line (for testing)
    echo '<pre>';
    print_r($val);
    echo '</pre>';
}

process_mpd(); // start processing mpd and get segments url
?>
