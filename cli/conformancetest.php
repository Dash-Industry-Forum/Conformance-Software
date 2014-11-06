<?php
ini_set('memory_limit','-1');//remove memory limit
error_reporting(E_ERROR | E_PARSE);

$inc = '../webfe/';

include $inc.'featurlist.php'; 
//include $inc.'globalvariables.php';
include $inc.'crossvalidation.php';
include $inc.'mpdvalidation.php';
include $inc.'mpdparsing.php';
include $inc.'datadownload.php';
include $inc.'assemble.php';
include $inc.'schematronIssuesAnalyzer.php';

include 'mpdprocessing.php';

set_time_limit(0);// php run without time limit

$adaptsetdepth=array();// array for Baseurl 
$depth = array();//array contains all relative URLs exist in all mpd levels 
$locate ;  // location of session folder on server
$foldername; // floder name for the session
$Adapt_urlbase = 0; // Baseurl in adaptationset
$id = array();  //mpd id
$codecs = array();
$width = array ();
$height = array ();
$period_baseurl=array();// all baseURLs included in given period
$scanType = array();
$frameRate = array();
$sar=array();
$bandwidth=array();
$Adaptationset=array();//array of all attributes in single adapatationset
$Adapt_arr = array();//array of all adaptationsets within 1 period
$Period_arr= array(); // array of all periods 
$init_flag; // flag decide if this is the first connection attempt
$repnolist = array(); // list of number of representation
$period_url = array(); // array contains location of all segments within period
$perioddepth=array(); //array with all relative baseurls up to period level
$type = "";
$minBufferTime = "";
$profiles = "";
$mediaPresentationDuration = "";
$count1=0; // Count number of adaptationsets processed
$count2=0;//count number of presentations proceessed

// Work out which validator binary to use
$bin_dir = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'webfe');
$validatemp4 = $bin_dir.DIRECTORY_SEPARATOR.((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? "validatemp4-vs2010.exe" : "validatemp4-linux");
$mpdvalidator = $bin_dir.DIRECTORY_SEPARATOR.'mpdvalidator2';

// Command line option defaults
$mpd_validation_only = false;

function print_r2($val){ //Print output line by line (for testing)
    print_r($val);
}

function usage()
{
    global $argv;
    fprintf(STDERR, "{$argv[0]} [OPTIONS]\n");
    fprintf(STDERR, "\n");
    fprintf(STDERR, " -h\tDisplay help\n");
}

$argv = $_SERVER['argv'];
$options = "h";
$opts = getopt($options);
foreach( $opts as $o => $a )
{
    // Strip out of $argv
    while( $k = array_search( "-" . $o, $argv ) )
    {
        if( $k )
            unset( $argv[$k] );
        if( preg_match( "/^.*".$o.":.*$/i", $options ) )
            unset( $argv[$k+1] );
    }
    
    // Process the option
    switch($o)
    {
        case 'h';
            usage();
            exit(1);
    }
}

$argv = array_merge( $argv );

$exit_code = 0;

for($i = 1; $i < count($argv); $i++)
{
    $failed = false;
    $url = $argv[$i];
    
    // Check the MPD
    $ret = process_mpd($url, $mpd_validation_only);
    if(false === $ret) 
    {
        fprintf(STDERR, "%s: Error: Failed loading the MPD file, please check the URL\n", $url);
        $exit_code = 1;
        continue;
    }

    print_r($ret);
    if('dynamic' === $ret[count($ret) - 1])
    {
        $dirid = $ret[count($ret) - 2];

        fprintf(STDERR, "%s: Warning: Dynamic MPD, not validating\n", $url);
        continue;
    }
    
    $dirid = $ret[count($ret) - 1];

    $xlink_resolved = array_shift($ret);
    if('true' === $xlink_resolved) {
        fprintf(STDOUT, "%s: XLink resolving ok\n", $url);
    } else {
        fprintf(STDERR, "%s: Error: XLink resolving failed, see %s\n", $url, $xlink_resolved);
        $failed = true;
    }

    $mpd_valid = array_shift($ret);
    if('true' === $mpd_valid) {
        fprintf(STDOUT, "%s: MPD validation ok\n", $url);
    } else {
        fprintf(STDERR, "%s: Error: MPD validation failed, see %s\n", $url, $mpd_valid);
        $failed = true;
    }

    $schematron_valid = array_shift($ret);
    if('true' === $schematron_valid) {
        fprintf(STDOUT, "%s: Schematron validation ok\n", $url);
    } else {
        fprintf(STDERR, "%s: Error: Schematron validation failed, see %s\n", $url, $schematron_valid);
        $failed = true;
    }

    if($failed) {
        $exit_code = 1;
        continue;
    }
    
    if($mpd_validation_only) {
        continue;
    }

    // Check all the representations
    for($adaptation_set = 0; $adaptation_set < $ret[0]; $adaptation_set++)
    {
        $childno = $adaptation_set + 1;
        fprintf(STDOUT, "%s: Adaptation set %d/%d\n", $url, $adaptation_set + 1, $ret[0]);
        for($representation = 0; $representation < $ret[$childno]; $representation++)
        {
            fprintf(STDOUT, "%s: Representation %d/%d\n", $url, $representation + 1, $ret[$childno]);

            $rep_ret = download($adaptation_set, $representation);
            print_r($rep_ret);
        }
    }
}

exit($exit_code);

?>
