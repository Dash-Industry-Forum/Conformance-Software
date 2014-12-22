<?php

function mpdvalidator($mpdvalidator_path, $url, $locate, $foldername)
{
    global $string_info;
    $function_result =[];
    $schematronIssuesReport;

    chdir($mpdvalidator_path);// Change default execution directory to the location of the mpd validator
    $mpdvalidator = syscall("ant run -Dinput=".$url." -Dtmpdir=".$locate); //run mpd validator
    $mpdvalidator = str_replace('[java]',"",$mpdvalidator); //save the mpd validator output to variable
    $valid_word = 'Start XLink resolving'; 
    $report_start = strpos($mpdvalidator,$valid_word); // Checking the begining of the Xlink validation
    $mpdvalidator=substr ($mpdvalidator,$report_start); // 
    $mpdreport = fopen($locate.'/mpdreport.txt','a+b');
    fwrite($mpdreport,$mpdvalidator);//get mpd validator result to text file

    $temp_string = str_replace (array('$Template$'),array("mpdreport"),$string_info); // copy mpd report to html file 
    $mpd_rep_loc = 'temp/'.$foldername.'/mpdreport.html'; // location of mpd report

    file_put_contents($locate.'//mpdreport.html',$temp_string); // create HTML to contain mpd report
    $exit=false;

    if(strpos($mpdvalidator,"XLink resolving successful")!==false)// check if Xlink resolving is successful
    {    
        $totarr[]='true';//incase of mpd validation success send true to client
    }else{
        $totarr[]=$mpd_rep_loc;// if failed send client the location of mpdvalidator report
        $exit = true;// if failed terminate conformance check 
    }
    if(strpos($mpdvalidator,"MPD validation successful")!==false)//check if Xlink resolving is successful 
    {    
        $totarr[]='true';//incase of mpd validation success send true to client
    }else{
        $totarr[]=$mpd_rep_loc;/// if failed send client the location of mpdvalidator report
        $exit = true;// if failed terminate conformance check
    }
    if(strpos($mpdvalidator,"Schematron validation successful")!==false) // check if Schematron validation is successful
    {    
        $totarr[]='true'; // if succesful send true to client
    }else{
        $schematronIssuesReport = analyzeSchematronIssues($mpdvalidator);
        $totarr[]=$mpd_rep_loc;/// if failed send client the location of mpdvalidator report
        $exit =true;// if failed terminate conformance check
    }
    
    $function_result[0]=$exit;
    $function_result[1]=$totarr;
    $function_result[2]=$schematronIssuesReport;
    return $function_result;							   
}


?>
