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
$verbose = false;

function print_r2($val){ //Print output line by line (for testing)
    print_r($val);
}

function usage()
{
    global $argv;
    fprintf(STDERR, "{$argv[0]} [OPTIONS]\n");
    fprintf(STDERR, "\n");
    fprintf(STDERR, " -h\tDisplay help\n");
    fprintf(STDERR, " -v\tVerbose\n");
    fprintf(STDERR, " -m\tMPD parsing only\n");
}

$argv = $_SERVER['argv'];
$options = "hvm";
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
        case 'v';
            $verbose = true;
            break;

        case 'm';
            $mpd_validation_only = true;
            break;

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

    if('dynamic' === $ret[count($ret) - 1])
    {
        $dirid = $ret[count($ret) - 2];

        fprintf(STDERR, "%s: Warning: Dynamic MPD, not validating\n", $url);
        continue;
    }
    
    $dirid = $ret[count($ret) - 1];

    $xlink_resolved = array_shift($ret);
    if('true' === $xlink_resolved) {
        $verbose && fprintf(STDOUT, "%s: XLink resolving ok\n", $url);
    } else {
        fprintf(STDERR, "%s: Error: XLink resolving failed, see %s\n", $url, $xlink_resolved);
        $failed = true;
    }

    $mpd_valid = array_shift($ret);
    if('true' === $mpd_valid) {
        $verbose && fprintf(STDOUT, "%s: MPD validation ok\n", $url);
    } else {
        fprintf(STDERR, "%s: Error: MPD validation failed, see %s\n", $url, $mpd_valid);
        $failed = true;
    }

    $schematron_valid = array_shift($ret);
    if('true' === $schematron_valid) {
        $verbose && fprintf(STDOUT, "%s: Schematron validation ok\n", $url);
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
        $verbose && fprintf(STDOUT, "%s: Adaptation set %d/%d\n", $url, $adaptation_set + 1, $ret[0]);
        for($representation = 0; $representation < $ret[$childno]; $representation++)
        {
            $verbose && fprintf(STDOUT, "%s: Representation %d/%d downloading\n", $url, $representation + 1, $ret[$childno]);

            $rep_ret = download($adaptation_set, $representation);
            $retcode = $rep_ret[count($rep_ret) - 1];
            chdir(dirname(__FILE__));
            switch ($retcode) 
            {
                case 'noerror':
                    $verbose && fprintf(STDOUT, "%s: Representation %d/%d ok\n", $url, $representation + 1, $ret[$childno]);
                    break;

                case 'error':
                    foreach (file($rep_ret[1]) as $line) 
                    {
                        $line = trim($line);
                        if($line !== '### error:')
                        {
                            $line = preg_replace("/^\#+ */", '', $line);
                            fprintf(STDERR, "%s: Error: %s\n", $url, $line);
                        }
                    }
                    break;

                case 'notexist':
                    foreach (file($rep_ret[0]) as $line) {
                        $line = trim($line);
                        fprintf(STDERR, "%s: Error: not found\n", $line);
                    }
                    break;

                case 'validatorerror':
                    fprintf(STDERR, "%s: Error: executing %s\n", $url, $rep_ret[0]);
                    foreach (file($rep_ret[1]) as $line) 
                    {
                        $line = trim($line);
                        fprintf(STDERR, "validatemp4: %s\n", $line);
                    }
                    break;

                default:
                    break;
            }
        }
    }

    // Check the cross representations
    $cross_ret = cross_representation_check();
    $cross_count = 0;
    for($adaptation_set = 0; $adaptation_set < $ret[0]; $adaptation_set++)
    {
        $childno = $adaptation_set + 1;
        for($representation = 0; $representation < $ret[$childno]; $representation++)
        {
            if('noerror' == $cross_ret[$cross_count]) 
            {
                $verbose && fprintf(STDOUT, "%s: Adaptation set %d, Representation %d Cross-representation validation success\n", $url, $adaptation_set + 1, $representation + 1);
            }
            else
            {
                fprintf(STDERR, "%s: Error: Adaptation set %d, Representation %d Cross-representation validation failed\n", $url, $adaptation_set + 1, $representation + 1);
                foreach (file($cross_ret[$cross_count]) as $line) {
                    $line = trim($line);
                    fprintf(STDERR, "%s: Error: %s\n", $url, $line);
                }
            }

            $cross_count++;
        }
    }
}

exit($exit_code);

?>
