<?php
ini_set('memory_limit','-1');//remove memory limit
error_reporting(E_ERROR | E_PARSE);
include 'featurlist.php'; 
set_time_limit(0);// php run without time limit
session_start();// initiate session for connected client

//placeholder="Enter mpd url" 
//logor_reporting(E_ALL | E_STRICT);
/////$url = 'http://castlabs-dl.s3.amazonaws.com/public/DASH/test_ept/Manifest.mpd';
if(isset($_POST['urlcode'])){// if client iniate first connection

$url_array = json_decode($_POST['urlcode']);   
$url = $url_array[0];// get mpd url from HTTP request
$_SESSION['url']=$url;// save mpd url to session variable
unset($_SESSION['period_url']); // reset session variable 'period_url' in order to remove any old segment url from previous sessions
unset($_SESSION['init_flag']);// reset for flag indicating first connection attempt
}

			$adaptsetdepth=array();// array for Baseurl b
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
			$count2=0;;//count number of presentations proceessed
			
			if(isset($_SESSION['locate'])) //get location from session variable if it is not secont  attempt to access server by same session
			$locate = $_SESSION['locate'];
			$Timeoffset;
 if(isset($_SESSION['count1']))//get Adaptationset counter in access
 $count1 =$_SESSION['count1'];
 
 if(isset($_SESSION['foldername']))//get folder name from session
 $foldername=$_SESSION['foldername'];
 
  if(isset($_SESSION['count2']))//get presentation counter
 $count2 =$_SESSION['count2'];

 if (isset($_SESSION['url']))//get mpd url from session variable
 $url=$_SESSION['url'];
 
if (isset($_SESSION['period_url']))//get period url from session variable
    $period_url=$_SESSION['period_url'];

if(isset($_SESSION['init_flag']))//check access flag status
    $init_flag = $_SESSION['init_flag'];

if(isset($_SESSION['Period_arr'])) //get array of periods in case of already processed 
    $Period_arr = $_SESSION['Period_arr'];

if(isset($_SESSION['type'])) 
    $type = $_SESSION['type'];
    
if(isset($_SESSION['minBufferTime']))
    $minBufferTime = $_SESSION['minBufferTime'];
    

$string_info = '<!doctype html> 
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>text demo</title>
  <style>
  p {
    color: blue;
    margin: 8px;
  }
  </style>
  <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
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
</html>';// String is added to an empty html file in order to access the report text file and show it in html format

function loadLeafInfoFile($fileName,$PresTimeOffset)
{
    $info=array();
    
    $leafInfoFile = fopen($fileName,"rt");
    if($leafInfoFile == FALSE)
    {
        echo "Error: Leaf info file".$fileName."not found, alignment wont be checked!";
        return;
    }
    
    fscanf($leafInfoFile,"%lu\n",$accessUnitDurationNonIndexedTrack);
    fscanf($leafInfoFile,"%u\n",$info['numTracks']);
    
    $info['leafInfo'] = array();
    $info['numLeafs'] = array();
    $info['trackTypeInfo'] = array();
    
    for($i = 0 ; $i < $info['numTracks'] ; $i++)
    {
        fscanf($leafInfoFile,"%lu %lu\n",$info['trackTypeInfo'][$i]['track_ID'],$info['trackTypeInfo'][$i]['componentSubType']);
    }
    
    for($i = 0 ; $i < $info['numTracks'] ; $i++)
    {
        fscanf($leafInfoFile,"%u\n",$info['numLeafs'][$i]);

        $info['leafInfo'][$i] = array();
        
        for($j = 0 ; $j < $info['numLeafs'][$i] ; $j++){
		$early = $info['leafInfo'][$i][$j]['earliestPresentationTime']-$PresTimeOffset;
		$last = $info['leafInfo'][$i][$j]['lastPresentationTime']-$PresTimeOffset;
	
            fscanf($leafInfoFile,"%d %f %f\n",$info['leafInfo'][$i][$j]['firstInSegment'],$early,$last);   
          
		  }   

   }

    fclose($leafInfoFile);
    
    return $info;

}

function checkAlignment($leafInfoA,$leafInfoB,$opfile,$segmentAlignment,$subsegmentAlignment,$bitstreamSwitching)
{
    if($leafInfoA['numTracks'] != $leafInfoB['numTracks'])
    {
        fprintf($opfile,"Error: Number of tracks logged %d for representation with id \"%s\" not equal to the number of indexed tracks %d for representation id \"%s\"\n",$leafInfoA['numTracks'],$leafInfoA['id'],$leafInfoB['numTracks'],$leafInfoB['id']);
        if($bitstreamSwitching=="true")
            fprintf($opfile,"Bitstream switching not possible, validation failed for bitstreamSwitching\n");
        return;
    }

    if($bitstreamSwitching=="true")
    {
        for($i = 0 ; $i < $leafInfoA['numTracks'] ; $i++)
        {
            $correspondingTrackFound = false;
            
            for($j = 0 ; $j < $leafInfoB['numTracks'] ; $j++)
            {
                if($leafInfoA['trackTypeInfo'][$i]['track_ID'] == $leafInfoB['trackTypeInfo'][$j]['track_ID'] && $leafInfoA['trackTypeInfo'][$i]['componentSubType'] == $leafInfoB['trackTypeInfo'][$j]['componentSubType'])
                {
                    $correspondingTrackFound = true;
                    break;
                }
            }
            
            if(!$correspondingTrackFound)
                fprintf($opfile,"Error: No corresponding track found in representation id \"%s\" for representation id \"%s\" track ID \"%s\" with type \"%s\", bitstream switching is not possible: Section 7.3.3.2. of ISO/IEC 23009-1:2012(E): The track IDs for the same media content component are identical for each Representation in each Adaptation Set \n",$leafInfoB['id'],$leafInfoA['id'],$leafInfoA['trackTypeInfo'][$i]['track_ID'],$leafInfoA['trackTypeInfo'][$i]['componentSubType']);            
        }
    }

    if($segmentAlignment != "true" && $subsegmentAlignment != "true" )
        return;
    
    //fprintf($opfile,"Error: test 123\n");
    for($i = 0 ; $i < $leafInfoA['numTracks'] ; $i++)
    {
        if($leafInfoA['numLeafs'][$i] != $leafInfoB['numLeafs'][$i])
        {
            fprintf($opfile,"Error: Number of leafs %d for track %d for representation id \"%s\" not equal to the number of leafs %d for track %d for representation id \"%s\"\n",$leafInfoA['numLeafs'][$i],$i+1,$leafInfoA['id'],$leafInfoB['numLeafs'][$i],$i+1,$leafInfoB['id']);
            continue;
        }
            
        for($j = 0 ; $j < ($leafInfoA['numLeafs'][$i]-1) ; $j++)
        {
            if($subsegmentAlignment=="true" || ($leafInfoA['leafInfo'][$i][$j+1]['firstInSegment'] > 0))
            {
                if($leafInfoA['leafInfo'][$i][$j+1]['earliestPresentationTime'] <= $leafInfoB['leafInfo'][$i][$j]['lastPresentationTime'])
                {
                    if($leafInfoA['leafInfo'][$i][$j+1]['firstInSegment'] > 0)
                        fprintf($opfile,"Error: Overlapping segment: EPT of leaf %f for leaf number %d for representation id \"%s\" is <= the latest presentation time %f corresponding leaf for representation id \"%s\"\n",$leafInfoA['leafInfo'][$i][$j+1]['earliestPresentationTime'],$j+1,$leafInfoA['id'],$leafInfoB['leafInfo'][$i][$j]['lastPresentationTime'],$leafInfoB['id']);
                    else
                        fprintf($opfile,"Error: Overlapping sub-segment: EPT of leaf %f for leaf number %d for representation id \"%s\" is <= the latest presentation time %f corresponding leaf for representation id \"%s\"\n",$leafInfoA['leafInfo'][$i][$j+1]['earliestPresentationTime'],$j+1,$leafInfoA['id'],$leafInfoB['leafInfo'][$i][$j]['lastPresentationTime'],$leafInfoB['id']);
                }
                
                if($leafInfoB['leafInfo'][$i][$j+1]['earliestPresentationTime'] <= $leafInfoA['leafInfo'][$i][$j]['lastPresentationTime'])
                {
                    if($leafInfoB['leafInfo'][$i][$j+1]['firstInSegment'] > 0)
                        fprintf($opfile,"Error: Overlapping segment: EPT of leaf %f for leaf number %d for representation id \"%s\" is <= the latest presentation time %f corresponding leaf for representation id \"%s\"\n",$leafInfoB['leafInfo'][$i][$j+1]['earliestPresentationTime'],$j+1,$leafInfoB['id'],$leafInfoA['leafInfo'][$i][$j]['lastPresentationTime'],$leafInfoA['id']);
                    else
                        fprintf($opfile,"Error: Overlapping sub-segment: EPT of leaf %f for leaf number %d for representation id \"%s\" is <= the latest presentation time %f corresponding leaf for representation id \"%s\"\n",$leafInfoB['leafInfo'][$i][$j+1]['earliestPresentationTime'],$j+1,$leafInfoB['id'],$leafInfoA['leafInfo'][$i][$j]['lastPresentationTime'],$leafInfoA['id']);
                }                
            }
        }
    }
}


function crossRepresentationProcess()
{
    global $Period_arr, $foldername,$locate,$string_info;
    
    for($i = 0; $i<sizeof($Period_arr); $i++)
    {
        $AdaptationSetAttr = $Period_arr[$i];
        
        if(!empty($AdaptationSetAttr['segmentAlignment']))
            $segmentAlignment = $AdaptationSetAttr['segmentAlignment'];
        else
            $segmentAlignment = "false";
            
        if(!empty($AdaptationSetAttr['subsegmentAlignment']))
            $subsegmentAlignment = $AdaptationSetAttr['subsegmentAlignment'];
        else
            $subsegmentAlignment = "false";
            
        if(!empty($AdaptationSetAttr['bitstreamSwitching']))
            $bitstreamSwitching = $AdaptationSetAttr['bitstreamSwitching'];
        else
            $bitstreamSwitching = "false";
        
        if (!($opfile = fopen(".\\temp\\".$foldername."\\Adapt".$i."_infofile.txt", 'w')))
        {
            echo "Error opening cross-representation checks file".".\\temp\\".$foldername."\\Adapt".$i."_infofile.txt";
            return;
        }
        
        fprintf($opfile,"Cross representation checks for adaptation set with id \"%s\":\n",$AdaptationSetAttr['id']);

        if($segmentAlignment == "true" || $subsegmentAlignment == "true" || $bitstreamSwitching == "true" )
        {
            $leafInfo=array();
            
            for ($j = 0;$j<sizeof($AdaptationSetAttr['Representation']['bandwidth']);$j++)
            {
                $leafInfo[$j] = loadLeafInfoFile(".\\temp\\".$foldername."\\Adapt".$i."rep".$j."_infofile.txt",$AdaptationSetAttr['Representation']['presentationTimeOffset'][$j]);
                $leafInfo[$j]['id'] = $AdaptationSetAttr['Representation']['id'][$j];
            }
            
            for ($j = 0;$j<sizeof($AdaptationSetAttr['Representation']['bandwidth'])-1;$j++)
            {
                for ($k = $j+1;$k<sizeof($AdaptationSetAttr['Representation']['bandwidth']);$k++)
                {
                    checkAlignment($leafInfo[$j],$leafInfo[$k],$opfile,$segmentAlignment,$subsegmentAlignment,$bitstreamSwitching);
                }              
            }            
        }
        
        fprintf($opfile,"Checks completed.\n");
        fclose($opfile);
	$temp_string = str_replace (array('$Template$'),array("Adapt".$i."_infofile"),$string_info);
        file_put_contents($locate.'\\'."Adapt".$i."_infofile.html",$temp_string);
			
    }
}

function process_mpd($mpdurl)
{
    global  $Adapt_arr,$Period_arr,$repno,$repnolist,$period_url,$locate,$string_info
    ,$count1,$count2,$perioddepth,$adaptsetdepth,$period_baseurl,$foldername,$type,$minBufferTime,$profiles;
    
    $path_parts = pathinfo($mpdurl); 
    $Baseurl=false; //define if Baseurl is used or no
    $setsegflag=false;
    $mpdfilename = $path_parts['filename'];		// determine name of actual MPD file

    if(isset($_POST['urlcode'])) // in case of client send first connection attempt
    {
		
		
        $sessname = 'sess'.rand(); // get a random session name
        session_name($sessname);// set session name

        $directories = array_diff(scandir(dirname(__FILE__).'/'.'temp'), array('..', '.'));

        foreach ($directories as $file) // Clean temp folder from old sessions in order to save diskspace
        {
            if(file_exists(dirname(__FILE__).'/'.'temp'.'/'.$file)) // temp is folder contains all sessions folders
            {
                $change = time()-filemtime(dirname(__FILE__).'/'.'temp'.'/'.$file); // duration of file implementation

                if($change>300)
                    rrmdir(dirname(__FILE__).'/'.'temp'.'/'.$file); // if last time folder was modified exceed 300 second it should be removed 
            }
        }

        //print_r2("I'm inside the mpd processing");
        //var_dump( $path_parts  );
        $foldername = 'id'.rand(); // get random name for session folder
        $_SESSION['foldername']=$foldername;
        // rrmdir($locate);
        $locate = dirname(__FILE__).'\\'.'temp'.'\\'.$foldername; //session  folder location
        $_SESSION['locate'] = $locate; // save session folder location 
        mkdir($locate,0777); // create session folder
        $totarr= array(); // array contains all data to be sent to client.
        copy(dirname(__FILE__)."\\"."validatemp4-vs2010.exe",$locate.'\\'."validatemp4-vs2010.exe"); // copy conformance tool to session folder to allow multi-session operation
        copy(dirname(__FILE__)."\\"."featuretable.html",$locate.'\\'."featuretable.html"); // copy features list html file to session folder
        //Create log file so that it is available if accessed
        $progressXML = simplexml_load_string('<root><percent>0</percent><dataProcessed>0</dataProcessed><dataDownloaded>0</dataDownloaded></root>');// get progress bar update
        $progressXML->asXml($locate.'/progress.xml'); //progress xml location
        
        //libxml_use_internal_logors(true);
        $MPD = simplexml_load_file($GLOBALS["url"]); // load mpd from url 
              $url_array = json_decode($_POST['urlcode']);
		
					
				 
				 

        if (!$MPD)
        {
            die("Error: Failed loading XML file");
        }
        
        $dom_sxe = dom_import_simplexml($MPD);

        if (!$dom_sxe)
        {
            exit;
        }
       
				chdir($url_array[1]);// Change default execution directory to the location of the mpd validator
				$mpdvalidator = syscall("ant run -Dinput=".$mpdurl); //run mpd validator
						$mpdvalidator = str_replace('[java]',"",$mpdvalidator); //save the mpd validator output to variable
						$valid_word = 'Start XLink resolving'; 
						$report_start = strpos($mpdvalidator,$valid_word); // Checking the begining of the Xlink validation
						$mpdvalidator=substr ($mpdvalidator,$report_start); // 
						$mpdreport = fopen($locate.'/mpdreport.txt','a+b');
								fwrite($mpdreport,$mpdvalidator);//get mpd validator result to text file

						$temp_string = str_replace (array('$Template$'),array("mpdreport"),$string_info); // copy mpd report to html file 
            $mpd_rep_loc =  '/temp/'.$foldername.'/mpdreport.html';

            file_put_contents($locate.'//mpdreport.html',$temp_string);
						$exit=false;
						
						if(strpos($mpdvalidator,"XLink resolving successful")!==false)// check if Xlink resolving is successful
                            $totarr[]='true';
							else{
							$totarr[]=$mpd_rep_loc;// if failed send client the location of mpdvalidator report
							$exit = true;// if failed terminate conformance check 
							}
						if(strpos($mpdvalidator,"MPD validation successful")!==false)//check if Xlink resolving is successful 
                          $totarr[]='true';
							else{
							$totarr[]=$mpd_rep_loc;/// if failed send client the location of mpdvalidator report
							$exit = true;// if failed terminate conformance check
							}
							if(strpos($mpdvalidator,"Schematron validation successful")!==false) // check if Schematron validation is successful
                          $totarr[]='true';
							else{
							$totarr[]=$mpd_rep_loc;/// if failed send client the location of mpdvalidator report
							$exit =true;// if failed terminate conformance check
							}
							if ($url_array[2] ===1)  // only mpd validation requested
                               $exit =true;							
							/*if ($exit===true)// mpd validation failed.
							{
        $stri=json_encode($totarr);
							echo $stri;
							            session_destroy(); // destroy session variables 
							exit;
							}
							*/

						
			 
		///////////////////////////////////////Processing mpd attributes in order to get value//////////////////////////////////////////////////////////
        $dom = new DOMDocument('1.0');
        $dom_sxe = $dom->importNode($dom_sxe, true); //create dom element to contain mpd 

        $dom_sxe = $dom->appendChild($dom_sxe);

        $MPD = $dom->getElementsByTagName('MPD')->item(0); // access the parent "MPD" in mpd file
		        $mediaPresentationDuration = $MPD ->getAttribute('mediaPresentationDuration'); // get mediapersentation duration from mpd level

		$y=str_replace("PT","",$mediaPresentationDuration); // process mediapersentation duration
        if(strpos($y,'H')!==false)
        {
            $H = explode("H",$y); //get hours

           $y = substr($y,strpos($y,'H')+1);
        }
        else
            $H[0]=0;
            
        if(strpos($y,'M')!==false)
        {
            
		   $M = explode("M",$y);// get minutes
             $y = substr($y,strpos($y,'M')+1);

        }
        else
            $M[0]=0;

        $S=explode("S",$y);// get seconds
        $presentationduration=($H[0]*60*60)+($M[0]*60)+$S[0];// calculate durations in seconds
featurelist($MPD,$presentationduration);
        $type = $MPD->getAttribute ( 'type'); // get mpd type
		if($type === 'dynamic')
		{ 
		$totarr[] = $foldername;
		$totarr[]='dynamic'; // Incase of dynamic only mpd conformance.
             $exit =true;		 
		// $stri=json_encode($totarr);
					//echo $stri;
					 
						//	            session_destroy(); // destroy session variables 
					
					//exit;// if type is dynamic "Dynamic conformance is not supported"

		}
		
		if($exit ===true)
		{
		
		 $stri=json_encode($totarr);
		 echo $stri;
		 session_destroy();
		 exit;
		}
		
        $minBufferTime = $MPD->getAttribute('minBufferTime');//get min buffer time
        $profiles = $MPD -> getAttribute('profiles');// get profiles

        /////////////////////////////////////////////////////////////////////////////////
        
        //////////////////////////////////////////////////////////////////////////////////
        foreach ($dom->documentElement->childNodes as $node) // search for all nodes within mpd
        {
              
            if($node->nodeName === 'Period')
                $periodNode = $node;   
            
        }
        
        $val = $dom->getElementsByTagName('BaseURL'); // get BaseUrl node
        $segflag = $dom->getElementsByTagName('SegmentTemplate');//check if segment template exist or no

        if($segflag->length>0)
            $setsegflag=true; // Segment template is supported

        if($val->length>0) // if baseurl is used
        {
            $Baseurl=true;// set Baseurl flag = true
            
            for($i=0;$i<sizeof($val);$i++)
            {
			//check if Baseurl node exist in MPD level or lower level
                $base = $val->item($i);
                $par = $base->parentNode;
                $name = $par->tagName;
                if($name == 'MPD') // if exist in mpd level
                {
                    $dir = $base->nodeValue;
					if ($dir==='./')   // if baseurl is relative URl
		               $dir = dirname($GLOBALS["url"]);// use location of Baseurl as location of mpd location

                }
            }
        
            if(!isset($dir))// if there is no Baseurl in mpd level 
                $dir = dirname($GLOBALS["url"]);// set location of segments dir as mpd location
        }
        else
            $dir = dirname($GLOBALS["url"]); // if there is no Baseurl in mpd level,set location of segments dir as mpd location
        processPeriod($periodNode); // start getting information from period level
        $segm_url = array();// contains segments url within one 
        $adapt_url = array(); // contains all segments urls within adapatations set
        if($setsegflag) // Segment template is used
        {

            for($k = 0; $k<sizeof($Period_arr); $k++) // loop on period array
            {
                if(!empty($Period_arr[$k]['SegmentTemplate']))
                {
                    //print_r2($Period_arr[$k]['SegmentTemplate']);
                    if(!empty($Period_arr[$k]['SegmentTemplate']['duration']))// get duration of segment template 
                        $duration = $Period_arr[$k]['SegmentTemplate']['duration'];
                    else
                        $duration = 0; // if duration doesn't exist set duration to 0
                    if(!empty($Period_arr[$k]['SegmentTemplate']['timescale']))// cehck time scale for given segment template
                        $timescale = $Period_arr[$k]['SegmentTemplate']['timescale'];
                    else
                        $timescale = 1; // if doesn't exist set default to 1
                        
                    if($duration!=0)
                    {
                        $duration = $duration/$timescale; // get duration
                        $segmentno = $presentationduration/$duration; //get segment number
                    }

                    $startnumber = $Period_arr[$k]['SegmentTemplate']['startNumber'];  // get first number in segment
                    $initialization = $Period_arr[$k]['SegmentTemplate']['initialization']; // get initialization degment 
                    $media = $Period_arr[$k]['SegmentTemplate']['media']; // get  media template
                    $timehash=null; // used only in segment timeline 
                    $timehash=array(); // contains all segmenttimelines for all segments

                    if(!empty($Period_arr[$k]['SegmentTemplate']['SegmentTimeline'])) // in case of using Segment timeline
                    {
                        $timeseg = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][0][0];// get time segment 

                        for($lok=0;$lok<sizeof($Period_arr[$k]['SegmentTemplate']['SegmentTimeline']);$lok++) // loop on segment time line 
                        {
                            

                            $d = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok][1]; // get d 
                            $r = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok][2];// get r 
                            $te = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok][0];// get t

                            if($r == 0)
                            {
                                $timehash[]= $timeseg;
                                $timeseg = $timeseg+$d;
                            }
                            
                            if($r<0)
                            {
                                if(!isset($Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok+1]))
                                    $ende = $presentationduration*$timescale;
                                else 
                                    $ende = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok+1];

                                $ende=$ende;
                                
                                while($timeseg<$ende) // calculate time segment untill the end of duration
                                {
                                    $timehash[]= $timeseg; 
                                    $timeseg=$timeseg+$d;
                                }
                            }
                            
                            if( $r>0) 
                            {
                                for($cn=0;$cn<=$r;$cn++) // repeat untill the amount of repeat is finished
                                {
                                    $timehash[]= $timeseg; 
                                    $timeseg=$timeseg+$d;
                                }
                            }
                        }
                    }
                }
                for ($j = 0;$j<sizeof($Period_arr[$k]['Representation']['bandwidth']);$j++) // loop on adaptationset level
                {
				
                    $direct=$dir;
                    if($Baseurl===true) // incase of using Base url
                    {
                        if(!isset($perioddepth[0])) // period doesn't contain any baseurl infromation
                        $perioddepth[0]=""; 

                        if(!isset($adaptsetdepth[$k]))  // adaptation set doesn't contain any baseurl information
                        $adaptsetdepth[$k]=""; 

                        $direct = $dir.$perioddepth[0].'//'.$adaptsetdepth[$k]; // combine baseURLs in both period level and adaptationset level
                    }
                    
                    if(!empty($Period_arr[$k]['Representation']['SegmentTemplate'][$j])) // in case of using segmenttemplate
                    {
                        $duration = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['duration']; // get  segment duration attribute
                        
                        if(!empty($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['timescale'])) //get time scale
                            $timescale = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['timescale'];
                        else 
                            $timescale = 1; // set to 1 if not avaliable 
                            
                        if($duration!=0)
                        {
                            $duration = $duration/$timescale; // get duration scaled
                            $segmentno = $presentationduration/$duration; // get number of segments
                            //print_r2($startnumber);
                        }
                        $startnumber = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['startNumber']; // get start number

                        $initialization = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['initialization']; // get initialization
                        $media = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['media'];// get media template

                        if(!empty($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'])) // check timeline 
                        {
                            $timeseg = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][0][0]; // segment start time

                            for($lok=0;$lok<sizeof($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline']);$lok++)//loop on timeline
                            {
                                $timehash=array(); //contains time tag for each segment

                                $d = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok][1];//get d
                                $r = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok][2];//get r
                                $te = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok][0];//get te

                                if($r == 0)// no duration repeat
                                {
                                    $timehash[]= $timeseg;//segment time stamp is same as segment time
                                    $timeseg = $timeseg+$d;
                                }
                                
                                if($r<0) // segments untill the end of presentation duration
                                {
                                    if(!isset($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok+1]))
                                    $ende = $presentationduration*$timescale; // multiply presentation duration by timescale
                                    else 
                                    $ende = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok+1];
                                    $ende=$ende;
                                    
                                    while($timeseg<$ende)
                                    {
                                        $timehash[]= $timeseg;
                                        $timeseg=$timeseg+$d;//get time stamp for each segment by adding segment duration to previous time stamp
                                    }
                                }
                                else
                                {
                                    for($cn=0;$cn<=$r;$cn++)//if r is positive number
                                    {
                                        $timehash[]= $timeseg;
                                        $timeseg=$timeseg+$d; // add duration to time segment to get time stamp for each segment
                                    }
                                }
                               
                            }
                        }
                    }

                    $bandwidth = $Period_arr[$k]['Representation']['bandwidth'][$j];// get bandwidth of given representation
                    $id = $Period_arr[$k]['Representation']['id'][$j]; // get id of given representation

                    $init = str_replace (array('$Bandwidth$','$RepresentationID$'),array($bandwidth,$id),$initialization); //get initialization segment template is replaced by bandwidth and id 
                    $initurl = $direct."/".$init;//full initialization URL
                    $segm_url[] = $initurl; //add segment to URL
                   $timehashmask=0; // default value if timeline doesnt exist
                    if(!empty($timehash)) // if time line exist
                    {
                        $segmentno = sizeof($timehash);// number of segments
                        $startnumber = 1 ; // start number set to 1
			$timehashmask = $timehash; 
                    }

$signlocation = strpos($media,'%');  // clean media attribute from non existing values
                     if($signlocation!==false)
					 {
                          if ($signlocation-strpos($media,'Number')===6)
						  {
						  $media = str_replace('$Number','',$media);
						  
						  }
						  
					 }
					 
                    for  ($i =0;$i<$segmentno;$i++ ) 
                    {
                        $segmenturl = str_replace (array('$Bandwidth$','$Number$','$RepresentationID$','$Time$'),array($bandwidth,$i+$startnumber,$id,$timehashmask[$i]),$media);//replace all media template values by actuall values
                          $segmenturl = sprintf($segmenturl,$startnumber+$i);
					   $segmenturl = str_replace('$','',$segmenturl);//clean segment url from any extra signs
						$segmenturl = $direct."/".$segmenturl; // get full segment url
                        $segm_url[]=removeabunchofslashes($segmenturl); //add URL to segments URL array
                    }
                    $adapt_url[] = $segm_url; // contains all representations within certain adaptation set
                    
                    $segm_url= array();	// delete segment url array and process the next representation
                }

                $period_url[] = $adapt_url;// add all adaptationset urls to period array
                $adapt_url=array(); // delete adaptationset array and process the next adaptation set
            }
            //print_r2($period_url);
        }

        if($Baseurl)// in case of using Base url node
        {
            for($i=0;$i<sizeof($period_baseurl);$i++) // loop on base url
            {
                if(!isset($perioddepth[0]))// if period doesn't contain baseurl
                    $perioddepth[0]="";
                    
                for($j=0;$j<sizeof($period_baseurl[$i]);$j++) //loop on baseurl in adaptationset  
                {
                    if(!isset($adaptsetdepth[$i])) // if adaptationset doesn't contain baseurl
                    $adaptsetdepth[$i]="";

                    for($lo=0;$lo<sizeof($period_baseurl[$i][$j]);$lo++) // loop on baseurl in period level
                    {
                        $period_baseurl[$i][$j][$lo] = removeabunchofslashes($dir.$perioddepth[0].'/'.$adaptsetdepth[$i].'/'.$period_baseurl[$i][$j][$lo]);//combine all baseurls
                    }
                }
            }
            if($setsegflag===false)
            $period_url = $period_baseurl;// if segment template is not used, use baseurl
        }
        
        $size=array();

        //print_r2("Alo I'm here");
        //print_r2($sum_bits);
		
        $_SESSION['period_url'] = $period_url;// save all period urls in session variable
        
        $_SESSION['Period_arr'] = $Period_arr; //save all period parameters in session variable
        $totarr[]=sizeof($period_url); // get number of periods
        for ($i=0;$i<sizeof($period_url);$i++) // loop on periods
        {
            $totarr[]=sizeof($period_url[$i]);//get number of adaptationsets
        }
        $peri=null;
        $totarr[] = $foldername;// add session name 
        $stri=json_encode($totarr); // encode array to send to client

		
        if(isset($_SESSION['count1']))  // reset adaptationset counter before download start
            unset($_SESSION['count1']);

        if(isset($_SESSION['count2'])) //reset representation counter before  download start
            unset($_SESSION['count2']);

        $_SESSION['type'] = $type;
        $_SESSION['minBufferTime'] = $minBufferTime;

          
        echo $stri; // send no. of periods,adaptationsets, representation, mpd file to client
    }
    ////////////////////////////////////////////////////////////////////////////////////
    if(isset($_POST['download'])) // get request from client to download segments
    {
	    $root= dirname(__FILE__);
        $destiny=array();

        if($count2>=sizeof($period_url[$count1]))//check if all representations within a segment is downloaded
        {
            $count2=0;  // reset representation counter when new adaptation set is proccesed 
            $count1=$count1+1; // increase adapatationset counter
        }
        
        if ($count1>=sizeof($period_url)) //check if all adapatationsets is processed 
        {    
		crossRepresentationProcess();
			$missingexist = file_exists ($locate.'\missinglink.txt'); //check if any broken urls is detected
			if($missingexist){
			$temp_string = str_replace (array('$Template$'),array("missinglink"),$string_info);
        file_put_contents($locate.'\missinglink.html',$temp_string);//create html file contains report for all missing segments
		}
			$file_error[] = "done"; 
			for($i=0;$i<sizeof($Period_arr);$i++){  // check all info files if they contain Error 
			if(file_exists($locate.'\\Adapt'. $i .'_infofile.txt')) 
			{
			            $searchadapt = file_get_contents($locate.'\\Adapt'. $i .'_infofile.txt');
						if(strpos($searchadapt,"Error")==false) 
                $file_error[] = "noerror"; // no error found in text file
            else
                $file_error[] = "temp".'/'.$foldername.'/'.'Adapt'. $i .'_infofile.html'; // add error file location to array
				}
				else
				$file_error[]="noerror";
         }
            session_destroy();
			if($missingexist){
               $file_error[]="temp".'/'.$foldername.'/missinglink.html';

			   }
			   else 
			   $file_error[]="noerror";
	   $send_string = json_encode($file_error); //encode array to string and send it 


            echo $send_string; // send string with location of all error logs to client
            exit;
        }
        else
        {
            $repno = "Adapt".$count1."rep".$count2; // presentation unique name
            $pathdir=$locate."\\".$repno."\\";
            
            if (!file_exists($pathdir))
            {
                mkdir($pathdir, 0777, true); // create folder for each presentation
            }
            
            $sizearray = downloaddata($pathdir,$period_url[$count1][$count2]); // download data 
			if($sizearray !==0)
			{
			
            Assemble($pathdir,$period_url[$count1][$count2],$sizearray); // Assemble all presentation in to one presentation
            rename($locate.'\\'."mdatoffset.txt",$locate.'\\'.$repno."mdatoffset.txt"); //rename txt file contains mdatoffset

            $file_location = array();
            $exeloc=dirname(__FILE__);
            chdir($locate);
            $timeSeconds=str_replace("PT","",$minBufferTime);
            $timeSeconds=str_replace("S","",$timeSeconds);
            $processArguments=" -minbuffertime ".$timeSeconds." -bandwidth ";
            $processArguments=$processArguments.$Period_arr[$count1]['Representation']['bandwidth'][$count2]." ";
            
            if($type=== "dynamic")
                $processArguments=$processArguments."-dynamic ";
            
            if($Period_arr[$count1]['Representation']['startWithSAP'][$count2] != "")
                $processArguments=$processArguments."-startwithsap ".$Period_arr[$count1]['Representation']['startWithSAP'][$count2]." ";
                
            if(strpos($Period_arr[$count1]['Representation']['profiles'][$count2],"urn:mpeg:dash:profile:isoff-on-demand:2011") !== false)
                $processArguments=$processArguments."-isoondemand ";
                
            if(strpos($Period_arr[$count1]['Representation']['profiles'][$count2],"urn:mpeg:dash:profile:isoff-live:2011") !== false)
                $processArguments=$processArguments."-isolive ";
                
            if(strpos($Period_arr[$count1]['Representation']['profiles'][$count2],"urn:mpeg:dash:profile:isoff-main:2011") !== false)
                $processArguments=$processArguments."-isomain ";

            $dash264=false;
            if(strpos($Period_arr[$count1]['Representation']['profiles'][$count2],"http://dashif.org/guidelines/dash264") !== false)
            {
                   $processArguments=$processArguments."-dash264base ";
                   $dash264=true;
            }
                
            // Checked in php along with segment and subsegment alignment
            //if($Period_arr[$count1]['bitstreamSwitching'] === "true")
            //    $processArguments=$processArguments."-bss ";
                
            if($Period_arr[$count1]['Representation']['ContentProtectionElementCount'][$count2] > 0 && $dash264 == true)
            {
                $processArguments=$processArguments."-dash264enc ";
            }
            
            exec("validatemp4-vs2010 ".$locate.'\\'.$repno.".mp4 "."-infofile ".$locate.'\\'.$repno.".txt"." -offsetinfo ".$locate.'\\'.$repno."mdatoffset.txt -logconsole".$processArguments );
            rename($locate.'\\'."leafinfo.txt",$locate.'\\'.$repno."_infofile.txt");
            $temp_string = str_replace (array('$Template$'),array($repno."_infofile"),$string_info);
            //print_r2($temp_string);
            file_put_contents($locate.'\\'.$repno."_infofile.html",$temp_string);
            $file_location[] = "temp".'/'.$foldername.'/'.$repno."_infofile.html";

            $destiny[]=$locate.'\\'.$repno."_infofile.txt";
            rename($locate.'\\'."stderr.txt",$locate.'\\'.$repno."log.txt");
            $temp_string = str_replace (array('$Template$'),array($repno."log"),$string_info);

            file_put_contents($locate.'\\'.$repno."log.html",$temp_string);
            $file_location[] = "temp".'/'.$foldername.'/'.$repno."log.html";

            $destiny[]=$locate.'\\'.$repno."log.txt";

            rename($locate.'\\'."stdout.txt",$locate.'\\'.$repno."myfile.txt");
            $temp_string = str_replace (array('$Template$'),array($repno."myfile"),$string_info);
            file_put_contents($locate.'\\'.$repno."myfile.html",$temp_string);
            $file_location[] = "temp".'/'.$repno."myfile.html";
            $destiny[]=$locate.'\\'.$repno."myfile.txt";

            $period_url[$count1][$count2]=null;
            //print_r2(memory_get_peak_usage(true));
            ob_flush();
            //ob_clean();
            $count2 = $count2+1;
            $search = file_get_contents($locate.'\\'.$repno."log.txt");
            
            if(strpos($search,"error")==false)
                $file_location[] = "noerror";
            else
                $file_location[] = "error";

            $_SESSION['count2'] = $count2;
            $_SESSION['count1'] = $count1;
            $send_string = json_encode($file_location);
            echo $send_string;
			}
			else 
		         {
				             $count2 = $count2+1;
							 $_SESSION['count2'] = $count2;
            $_SESSION['count1'] = $count1;

				 $file_location[] = 'notexist';
				             $send_string = json_encode($file_location);

				echo $send_string;
				 
				 }

        }
    }
}

function processPeriod($period)
{
    global $Adapt_arr,$Period_arr,$period_baseurl,$perioddepth,$Adapt_urlbase, $profiles,$Timeoffset;

    //var_dump($period);
    $domper = new DOMDocument ('1.0');
    $period = $domper->importNode ( $period , true);
    $period = $domper->appendChild($period);

    //var_dump ($domper);
    $Periodduration = $period->getAttribute('duration');
	$Period_segmentbase = $period->getElementsByTagName('SegmentBase');
	$Timeoffset=0;
	for($i=0;$i<$Period_segmentbase->length;$i++)
    {
        $base = $Period_segmentbase->item(0);
        $par = $base->parentNode;
        $name = $par->tagName;
        if($name == 'Period')
        {
           $Timeoffset = processSegmentBase($base);
		
		}
		
    }
    //print_r($Periodduration);
    $Adaptationset = $domper->getElementsByTagName( "AdaptationSet" ); 
    $periodbase = $domper->getElementsByTagName("BaseURL");
    $periodProfiles = $period->getAttribute('profiles');
    if($periodProfiles === "")
        $periodProfiles = $profiles;
        
    $periodBitstreamSwitching = $period->getAttribute('bitstreamSwitching');
    
    for($i=0;$i<$periodbase->length;$i++)
    {
        $base = $periodbase->item($i);
        $par = $base->parentNode;
        $name = $par->tagName;
        if($name == 'Period')
        {
            $baseurl = $base->nodeValue;
            $perioddepth[$i]=$baseurl;
            
		}
    }
    
    global $id;
    
    for ($i = 0; $i < $Adaptationset->length; $i++)
    {
        $set=$Adaptationset->item($i);
        $Adapt_urlbase=null;
        processAdaptationset($set,$periodProfiles,$periodBitstreamSwitching);
        $Period_arr[$i] = $Adapt_arr;
        $period_baseurl[$i] = $Adapt_urlbase;
    }
}

function processAdaptationset ($Adapt, $periodProfiles, $periodBitstreamSwitching)
{
    global $Adapt_arr,$Period_arr, $Adapt_urlbase,$adaptsetdepth,$Timeoffset;
    //var_dump($Adapt);
    $dom = new DOMDocument ('1.0');
    $Adapt = $dom->importNode ( $Adapt, true);
    $Adapt = $dom->appendChild($Adapt);
    if ( $Adapt->hasAttributes())
    {
        $startWithSAP = $Adapt->getAttribute ( 'startWithSAP') ;
        $segmentAlignment = $Adapt->getAttribute ('segmentAlignment');
        $idadapt = $Adapt->getAttribute ('id');
        $scanType = $Adapt->getAttribute ('scanType');
        $mimeType = $Adapt->getAttribute ('mimeType');
        $adapsetProfiles = $Adapt->getAttribute ('profiles');
        if($adapsetProfiles === "")
            $adapsetProfiles = $periodProfiles;
            

        $bitstreamSwitching = $Adapt->getAttribute ('bitstreamSwitching');
        if($bitstreamSwitching === "")
            $bitstreamSwitching = $periodBitstreamSwitching;
            
        $ContentProtection = $dom->getElementsByTagName( "ContentProtection" ); 

        $Contentcomponent = $dom->getElementsByTagName ("ContentComponent");
        $tr=$dom->childNodes->item(0)->nodeName;

        if($Contentcomponent->length> 0)
        {
            $tempContentcomponent = $Contentcomponent->item(0);
            //$contid = $tempContentcomponent->getAttribute('id');
            $contentType = $tempContentcomponent->getAttribute('contentType');
            //print_r($contentType);
            //print_r($contid);
        }
		
		$Adapt_segmentbase = $Adapt->getElementsByTagName('SegmentBase');
		$Adapt_Timeoffset=0;
	for($i=0;$i<$Adapt_segmentbase->length;$i++)
    {
        $base = $Adapt_segmentbase->item(0);
        $par = $base->parentNode;
        $name = $par->tagName;
        if($name === 'AdaptationSet')
        {
           $Adapt_Timeoffset = processSegmentBase($base);
		}
    }
     if ($Adapt_Timeoffset===0)
       $Adapt_Timeoffset=$Timeoffset;	 
        $baseurl = $Adapt->getElementsByTagName ("BaseURL");
        $adaptsetdepth=array();
        
        for($i=0;$i<$baseurl->length;$i++)
        {
            $base = $baseurl->item($i);
            $par = $base->parentNode;
            $name = $par->tagName;
            if($name == 'AdaptationSet')
            {
                $Adaptbase = $base->nodeValue;
                $adaptsetdepth[] = $Adaptbase;
            }
        }
        //$segmenttemplate = $dom->getElementsByTagName("SegmentTemplate");
        //$seg=$segmenttemplate->item(0);
        //$par = $seg->parentNode;

        //print_r2("Could".var_dump($par));
        //$name = $par->tagName;
        //print_r2($name);
        $rep_seg_temp = array();
        $segmenttemplate = $dom->getElementsByTagName("SegmentTemplate");
        
        if($segmenttemplate->length>0)
        {
            for($i=0;$i<$segmenttemplate->length; $i++)
            {
                $seg_arr = array();
                $seg=$segmenttemplate->item($i);
                $par = $seg->parentNode;
                $name = $par->tagName;
                if($name=="AdaptationSet")
                {
                    //   print_r2($name);
                    $Adapt_seg_temp= processTemplate($seg);
                    //   print_r2("It is he son of Adaptationset");
                }
                else 
                {
                    $Adapt_seg_temp=null;
                    // print_r2($name);
                    //	print_r2("I'm in the representation");
                }
            }
        }
        else
        {
            $Adapt_seg_temp = 0;
        }
        //$Adapt_seg_temp = processTemplate($Adapt);

        $Representation=  $dom->getElementsByTagName ("Representation");
        if($Representation->length>0)
        {
            $rep_url = array();
            //var_dump($Representation);
            $rep_seg_temp = array();	
            
            for ($i = 0; $i < $Representation->length; $i++)
            {
                $lastbase = array();
                $temprep=$Representation->item($i);
                $repbaseurl = $temprep->getElementsByTagName('BaseURL');
				$Rep_segmentbase = $temprep->getElementsByTagName('SegmentBase');

           if($Rep_segmentbase->length>0)
		   {
		  
		   $base= $Rep_segmentbase->item(0);
           $Rep_Timeoffset[] = processSegmentBase($base);
		   
		   }
		   else
		   $Rep_Timeoffset[] = $Adapt_Timeoffset;
		         
                for($j=0;$j<$repbaseurl->length;$j++)
                {
                    $base = $repbaseurl->item($j);
                    $lastbase[] = $base->nodeValue;
                }
                
                $rep_url[]=$lastbase;
          
                $repsegment = $temprep->getElementsByTagName("SegmentTemplate");
                $pass_seg = $repsegment->item(0);
                if($repsegment->length>0)
                    $rep_seg_temp[$i] = processTemplate($pass_seg);

                $idvar = $temprep->getAttribute ('id');
                if(empty($idvar))
                    $idvar=0;
                    
                $id[$i] = $idvar;
                
                $repStartWithSAP[$i] = $temprep->getAttribute  ('startWithSAP');
                if($repStartWithSAP[$i] === "")
                    $repStartWithSAP[$i] = $startWithSAP;
                    
                $repProfiles[$i] = $temprep->getAttribute  ('profiles');
                if($repProfiles[$i] === "")
                    $repProfiles[$i] = $adapsetProfiles;

                $codecsvar = $temprep->getAttribute  ('codecs');
                if(empty($codecsvar))
                    $codecsvar=0;
                $codecs[$i]=$codecsvar;

                $widthvar = $temprep->getAttribute ('width');
                if(empty($widthvar))
                    $widthvar=0;
                $width [$i] = $widthvar;

                $heightvar = $temprep->getAttribute ('height');
                if(empty($heightvar))
                    $heightvar=0;
                $height[$i] = $heightvar;
                if(empty($scantypevar))	
                    $scantypevar = $temprep->getAttribute ('scanType' );
                if(empty($scantypevar))	
                    $scantypevar=0;
                $scanType= $scantypevar;

                $frameRatevar = $temprep->getAttribute ('frameRate');
                if(empty($frameRatevar))
                    $frameRatevar = 0;
                $frameRate[$i] = $frameRatevar;

                $sarvar = $temprep->getAttribute ('sar' ) ;
                if(empty($sarvar))
                    $sarvar=0;
                $sar[$i] = $sarvar;
                $bandwidthvar = $temprep->getAttribute ('bandwidth');
                if(empty($bandwidthvar))
                    $bandwidthvar=0;
                $bandwidth[$i]=$bandwidthvar;
                
                $ContentProtectionElementCountRep[$i] = $temprep->getElementsByTagName( "ContentProtection" )->length; 
                if($ContentProtectionElementCountRep[$i] == 0)
                {
                    $ContentProtectionElementCountRep[$i] = $ContentProtection->length;
                }

                //echo $id ; 
				}
            }
       
    }
    //print_r2($rep_seg_temp);
    //print_r2($Adapt_seg_temp);
    $Adapt_urlbase  = $rep_url;
    //print_r2($Adapt_urlbase);
    $Rep_arr=array('id'=>$id,'codecs'=>$codecs,'width'=>$width,'height'=>$height,'scanType'=>$scanType,'frameRate'=>$frameRate,
    'sar'=>$sar,'bandwidth'=>$bandwidth,'SegmentTemplate'=>$rep_seg_temp, 'startWithSAP'=>$repStartWithSAP, 'profiles'=>$repProfiles,
	'ContentProtectionElementCount'=>$ContentProtectionElementCountRep,'presentationTimeOffset'=>$Rep_Timeoffset);
        $Adapt_arr=array('startWithSAP'=>$startWithSAP,'segmentAlignment'=>$segmentAlignment,'bitstreamSwitching'=>$bitstreamSwitching,
    'id'=>$idadapt,'scanType'=>$scanType,'mimeType'=>$mimeType,'SegmentTemplate'=>$Adapt_seg_temp,'Representation'=>$Rep_arr);
}

function processTemplate($segmentTemp)
{
//print_r2(var_dump($segmentTemp));
	$seg_array=array();

	$timelineseg = $segmentTemp->getElementsByTagName('SegmentTimeline');
	
	if($timelineseg->length>0)
    	$SegmentTimeline = processtimeline($timelineseg);
	else 
    	$SegmentTimeline = 0;
	
    //print_r2("length ".$SegmentTemplate->length);
	
	$timescale=$segmentTemp->getAttribute("timescale");
	$duration = $segmentTemp->getAttribute ( "duration" );
	$startNumber = $segmentTemp->getAttribute ( "startNumber");
    $media = $segmentTemp->getAttribute ("media") ;
    $initialization = $segmentTemp->getAttribute ("initialization") ;
	if(isset($initialization)){
	$init_flag=true;
		$_SESSION['init_flag']=$init_flag;	
	}
	$seg_array=array('duration'=>$duration,'startNumber'=>$startNumber,'media'=>$media,
	'initialization'=>$initialization,'timescale'=>$timescale,'SegmentTimeline'=>$SegmentTimeline);
	
	
	
  return $seg_array;
}
function processtimeline($timelinearray)
{
$Sarray = array();
$timetemp = $timelinearray->item(0);
$stag = $timetemp->getElementsByTagName('S');
for($i=0;$i<$stag->length;$i++)
{
$tempstag =$stag->item($i);
$t=$tempstag->getAttribute('t');
if(empty($t))
$t=0;
$d = $tempstag->getAttribute('d');
 
$r = $tempstag->getAttribute('r');
  if (empty($r))
  $r = 0 ;
  
  
  $Satt = array($t,$d,$r);
  $Sarray[]=$Satt;

}
//print_r2($Sarray);
return $Sarray;
}
function print_r2($val){
        echo '<pre>';
        print_r($val);
        echo  '</pre>';
}

function unzip($src_file, $dest_dir=false, $create_zip_name_dir=true, $overwrite=true) 
{
  if ($zip = zip_open($src_file)) 
  {
    if ($zip) 
    {
      $splitter = ($create_zip_name_dir === true) ? "." : "/";
      if ($dest_dir === false) $dest_dir = substr($src_file, 0, strrpos($src_file, $splitter))."/";
      
      // Create the directories to the destination dir if they don't already exist
      create_dirs($dest_dir);

      // For every file in the zip-packet
      while ($zip_entry = zip_read($zip)) 
      {
        // Now we're going to create the directories in the destination directories
        
        // If the file is not in the root dir
        $pos_last_slash = strrpos(zip_entry_name($zip_entry), "/");
        if ($pos_last_slash !== false)
        {
          // Create the directory where the zip-entry should be saved (with a "/" at the end)
          create_dirs($dest_dir.substr(zip_entry_name($zip_entry), 0, $pos_last_slash+1));
        }

        // Open the entry
        if (zip_entry_open($zip,$zip_entry,"r")) 
        {
          
          // The name of the file to save on the disk
          $file_name = $dest_dir.zip_entry_name($zip_entry);
          
          // Check if the files should be overwritten or not
          if ($overwrite === true || $overwrite === false && !is_file($file_name))
          {
            // Get the content of the zip entry
            $fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            file_put_contents($file_name, $fstream );
            // Set the rights
            chmod($file_name, 0777);
          }
          
          // Close the entry
          zip_entry_close($zip_entry);
        }       
      }
      // Close the zip-file
      zip_close($zip);
    }
  } 
  else
  {
    return false;
  }
  
  return true;
}

/**
 * This function creates recursive directories if it doesn't already exist
 *
 * @param String  The path that should be created
 *  
 * @return  void
 */
function create_dirs($path)
{
  if (!is_dir($path))
  {
    $directory_path = "";
    $directories = explode("/",$path);
    array_pop($directories);
    
    foreach($directories as $directory)
    {
      $directory_path .= $directory."/";
      if (!is_dir($directory_path))
      {
        mkdir($directory_path);
        chmod($directory_path, 0777);
      }
    }
  }
}

process_mpd($url);// start processing mpd and get segments url

function Assemble ($path,$period,$sizearr)
{
global $init_flag,$repno,$locate;
if($init_flag)
$index = 0 ;
else
$index = 1;

foreach($period as $unit)
$names[]=basename($unit);



for ($i = 0;$i<sizeof($names);$i++){
$fp1 = fopen($locate.'\\'.$repno.".mp4", 'a+');
if(file_exists($path.$names[$i])){

$size=$sizearr[$i];
$file2 = file_get_contents($path.$names[$i]);


fwrite($fp1,$file2);
fclose($fp1);
file_put_contents($locate.'\\'.$repno.".txt",$index." ".$size."\n",FILE_APPEND);
$index++;

}
}

}
function retrieve_remote_file_size($type){
     $ch = curl_init($type);

     curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
     curl_setopt($ch, CURLOPT_HEADER, TRUE);
     curl_setopt($ch, CURLOPT_NOBODY, TRUE);

     $data = curl_exec($ch);
     $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

     curl_close($ch);
     return $size;
}

function downspeed()
{

$downexample = 'http://download.bethere.co.uk/images/61859740_3c0c5dbc30_o.jpg';
$ch = curl_init($downexample);

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_USERAGENT, 'Sitepoint Examples (thread 581410; http://www.sitepoint.com/forums/showthread.php?t=581410)');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);


$execute = curl_exec($ch);
$info = curl_getinfo($ch);

// Time spent downloading, I think
$time = $info['total_time'] 
      - $info['namelookup_time'] 
      - $info['connect_time'] 
      - $info['pretransfer_time'] 
      - $info['starttransfer_time'] 
      - $info['redirect_time'];


// Echo friendly messages
header('Content-Type: text/plain');

 $downspeed = $info['speed_download'];
 return $downspeed;

}
function rrmdir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir")
		 rrmdir($dir."/".$object);
		 else {
		 chmod($dir."/".$object,0777);
		 unlink($dir."/".$object);
		 }
       }
     }
     reset($objects);
     rmdir($dir);
   }
 }
 
function downloaddata($directory,$array_file)
{
    global $locate;
    $sizefile = fopen($locate.'/mdatoffset.txt','a+b');
    $initoffset = 0;
    $totaldownloaded = 0;
    $totalDataProcessed = 0;
    $totalDataDownloaded = 0;
    // Load XML with SimpleXml from string
    $progressXML = simplexml_load_string('<root><percent>0</percent><dataProcessed>0</dataProcessed><dataDownloaded>0</dataDownloaded></root>');
    
    for($index=0;$index<sizeof($array_file);$index++)
    {
        $filePath = $array_file[$index];
        $file_size = remote_file_size2($filePath);
		if ($file_size===false)
		{
			$missing = fopen($locate.'/missinglink.txt','a+b');

		fwrite($missing,$filePath."\n");
		
		
		}
		else{

        $file_sizearr[$index] = $file_size;
        $tok = explode('/', $filePath);
        $filename = $tok[sizeof($tok)-1];
        $sizepos = 0;
        
        while($sizepos<$file_size)
        {

            //print_r2($file_size);

            //$content = file_get_contents($filePath, FILE_TEXT,Null,$sizepos,5000);
            //$content = file_get_contents($filePath, FILE_TEXT,Null,$sizepos,5000);
            $content = partialdownload($filePath,$sizepos,$sizepos+1500);
            $totalDataDownloaded=$totalDataDownloaded+1500;
            //print_r2($content);
            $byte_array = unpack('C*', $content);
            //print_r2($content);
            //print_r2($byte_array);
            $location = 1;
            $name=null;
            $size = 0;
            $newfile = fopen($directory.$filename, 'a+b');

            //print_r2(sizeof($byte_array));
            //print_r2($byte_array);
            
            while($location<sizeof($byte_array))
            {
                $size =$byte_array[$location]*16777216 +$byte_array[$location+1]*65536+$byte_array[$location+2]*256 +$byte_array[$location+3];
                if (sizeof($array_file)===1)
                {
                    $totaldownloaded=$totaldownloaded+$size;
                    $percent = (int)(100*$totaldownloaded/$file_size);
                }
                else 
                    $percent = (int)(100*$index/(sizeof($array_file)-1));
                
                $name = substr($content,$location+3,4);

                if($name!='mdat')
                {
                    $total = $location+$size;
                    if($total<sizeof($byte_array))
                    {
                        fwrite($newfile,substr($content,$location-1,$size));
                        //print_r2(substr($content,$location-1,$size));
                    }
                    else
                    {
                        $rest = partialdownload($filePath,$sizepos,$sizepos+$size-1);
                        $totalDataDownloaded=$totalDataDownloaded+$size-1;
                        fwrite($newfile,$rest);
                    }
                }
                else
                {
                    fwrite($sizefile,($initoffset+$sizepos+8)." ".($size-8)."\n");
                    fwrite($newfile,substr($content,$location-1,8));
                    //fwrite($newfile,str_pad("0",$size-8,"0"));
                }

                $sizepos=$sizepos+$size;
                $location = $location + $size;

            }
            
            // Modify node
            $progressXML->percent = strval($percent);
            $progressXML->dataProcessed = strval($totalDataProcessed + $sizepos);
            $progressXML->dataDownloaded = strval($totalDataDownloaded);
            // Saving the whole modified XML to a new filename
            $progressXML->asXml(trim($locate.'/progress.xml'));

        }

        fflush($newfile);
        fclose($newfile);
        $initoffset = $initoffset+$file_size;
        $totalDataProcessed = $totalDataProcessed + $file_size;
    }
 
 }
 if (!isset($file_sizearr))
 $file_sizearr = 0;
 return $file_sizearr;
 
}

 function remote_file_size2($url){
	# Get all header information
	$data = get_headers($url, true);
	
	if ($data[0]==='HTTP/1.1 404 Not Found')
	{
	return false;
	
	}
	
	# Look up validity
	if (isset($data['Content-Length']))
		# Return file size
		return (int) $data['Content-Length'];
		else
		return false;
}
 function partialdownload($url,$begin,$end){
global $locate;
$range = $begin.'-'.$end;
$fileName = $locate.'//'."getthefile.mp4";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);


	//$from = filesize($fileName);
	curl_setopt($ch, CURLOPT_RANGE, $range);


$fp = fopen($fileName, "w+");
if (!$fp) {
	exit;
}
curl_setopt($ch, CURLOPT_FILE, $fp);
$result = curl_exec($ch);
curl_close($ch);


fclose($fp);
$content = file_get_contents($fileName);
return $content;
//print_r2($byte_array);
}


function copy_folder($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                copy_folder($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
} 

function syscall($command){
$result=0;
    if ($proc = popen("($command)2>&1","r")){
        while (!feof($proc)) $result .= fgets($proc, 1000);
        pclose($proc);
        return $result; 
        }
    }
	function removeabunchofslashes($url){
  $explode = explode('://',$url);
  while(strpos($explode[1],'//'))
    $explode[1] = str_replace('//','/',$explode[1]);
  return implode('://',$explode);
}
function processSegmentBase($basedom){
$timeoffset = 0;
if ($basedom->hasAttribute('presentationTimeOffset'))
$timeoffset = $basedom->getAttribute('presentationTimeOffset');

return $timeoffset;
}	
?>
