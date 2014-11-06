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
$bin_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'webfe';
$validatemp4 = $bin_dir.DIRECTORY_SEPARATOR.((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? "validatemp4-vs2010.exe" : "validatemp4-linux");
$mpdvalidator = $bin_dir.DIRECTORY_SEPARATOR.'mpdvalidator2';

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

for($i = 1; $i < count($argv); $i++)
{
    $ret = process_mpd($argv[$i]);
    if(false === $ret) {
        exit(1);
    }
    
    print_r($ret);
}

?>
