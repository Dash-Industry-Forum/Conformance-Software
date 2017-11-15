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

function mpdvalidator($result_array, $locate, $foldername)
{
    global $string_info;
    $function_result = array();
    $schematronIssuesReport;
    $url_array = $result_array;
    chdir($url_array[1]); // Change default execution directory to the location of the mpd validator
    //run mpd validator
    //$mpdvalidator = syscall("ant run -Dinput=\"" . $url_array[0] . "\" -Dresolved=" . $url_array[3] . "/resolved.xml" . " -Dschema=schemas/DASH-MPD.xsd"); 
    $mpdvalidator = syscall('java -cp "saxon9.jar:saxon9-dom.jar:xercesImpl.jar:bin" Validator ' . $url_array[0] . " " . $url_array[3] . "/resolved.xml schemas/DASH-MPD.xsd");
    $mpdvalidator = str_replace('[java]', "", $mpdvalidator); //save the mpd validator output to variable
    $valid_word = 'Start XLink resolving';
    $report_start = strpos($mpdvalidator, $valid_word); // Checking the begining of the Xlink validation
    $mpdvalidator = substr($mpdvalidator, $report_start); // 
    $mpdreport = fopen($locate . '/mpdreport.txt', 'a+b');
    fwrite($mpdreport, $mpdvalidator); //get mpd validator result to text file

    $temp_string = str_replace(array('$Template$'), array("mpdreport"), $string_info); // copy mpd report to html file 
    $mpd_rep_loc = 'temp/' . $foldername . '/mpdreport.html'; // location of mpd report

    file_put_contents($locate . '/mpdreport.html', $temp_string); // create HTML to contain mpd report
    $exit = false;

    if (strpos($mpdvalidator, "XLink resolving successful") !== false)// check if Xlink resolving is successful
    {
        $totarr[] = 'true'; //incase of mpd validation success send true to client
    }
    else
    {

        $totarr[] = $mpd_rep_loc; // if failed send client the location of mpdvalidator report
        $exit = true; // if failed terminate conformance check 
    }
    if (strpos($mpdvalidator, "MPD validation successful") !== false)//check if MPD resolving is successful 
    {
        $totarr[] = 'true'; //incase of mpd validation success send true to client
    }
    else
    {

        $totarr[] = $mpd_rep_loc; /// if failed send client the location of mpdvalidator report
        $exit = true; // if failed terminate conformance check
    }
    if (strpos($mpdvalidator, "Schematron validation successful") !== false) // check if Schematron validation is successful
    {
        $totarr[] = 'true'; // if succesful send true to client
    }
    else
    {
        $schematronIssuesReport = analyzeSchematronIssues($mpdvalidator);
        $totarr[] = $mpd_rep_loc; /// if failed send client the location of mpdvalidator report
        $exit = true; // if failed terminate conformance check
    }
    if ($url_array[2] === 1)  // only mpd validation requested       
    {
        $exit = true;
    }

    $function_result[0] = $exit;
    $function_result[1] = $totarr;
    $function_result[2] = $schematronIssuesReport;
    return $function_result;
}

?>
