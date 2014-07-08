<?php

//$ini_set('display_logors', 'On');
//include_once("analyticstracking.php")
ini_set('memory_limit','-1');
set_time_limit(0);
session_start();

//placeholder="Enter mpd url" 
//logor_reporting(E_ALL | E_STRICT);

if(isset($_POST['urlcode'])){


$url = $_POST['urlcode'];
$_SESSION['url']=$url;
unset($_SESSION['period_url']);
unset($_SESSION['init_flag']);
}
//$url = "http://dash.edgesuite.net/dash264/TestCases/1b/qualcomm/1/MultiRate.mpd" ;
//$url = "http://dash.edgesuite.net/dash264/TestCases/1b/thomson-networks/2/manifest.mpd";
//$url = "http://dash.edgesuite.net/dash264/TestCases/1b/envivio/manifest.mpd";
//$url = "http://dash.edgesuite.net/dash264/TestCases/1a/netflix/exMPD_BIP_TC1.mpd";
//$url = "http://dash.edgesuite.net/dash264/TestCases/1a/qualcomm/1/MultiRate.mpd";

//$url = "http://dash.edgesuite.net/dash264/TestCases/1c/qualcomm/2/MultiRate.mpd";

//$url = "CENC_SD_time_MPD.mpd";
			$adaptsetdepth=array();
			$depth = array();
	        $locate ;
			$foldername;
            $Adapt_urlbase = 0;
	        $id = array(); 
            $codecs = array();
			$width = array ();
			$height = array ();
			$period_baseurl=array();
			$scanType = array();
			$frameRate = array();
			$sar=array();
			$bandwidth=array();
            $Adaptationset=array();
			$Adapt_arr = array();
			$Period_arr= array();
			$init_flag;
            $repnolist = array();
            $period_url = array();
			$perioddepth=array();
			$type = "";
            $minBufferTime = "";
            $profiles = "";
            $mediaPresentationDuration = "";
			include("zip.lib.php"); 
			$count1=0;
			$count2=0;

        // Work out which validator binary to use
        $validatemp4 = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? "validatemp4-vs2010.exe" : "validatemp4-linux";

			if(isset($_SESSION['locate']))
			$locate = $_SESSION['locate'];
			
 if(isset($_SESSION['count1']))
 $count1 =$_SESSION['count1'];
 
 if(isset($_SESSION['foldername']))
 $foldername=$_SESSION['foldername'];
 
  if(isset($_SESSION['count2']))
 $count2 =$_SESSION['count2'];

 if (isset($_SESSION['url']))
 $url=$_SESSION['url'];
 
if (isset($_SESSION['period_url']))
    $period_url=$_SESSION['period_url'];

if(isset($_SESSION['init_flag']))
    $init_flag = $_SESSION['init_flag'];

if(isset($_SESSION['Period_arr']))
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
</html>';

function loadLeafInfoFile($fileName)
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
        
        for($j = 0 ; $j < $info['numLeafs'][$i] ; $j++)
            fscanf($leafInfoFile,"%d %f %f\n",$info['leafInfo'][$i][$j]['firstInSegment'],$info['leafInfo'][$i][$j]['earliestPresentationTime'],$info['leafInfo'][$i][$j]['lastPresentationTime']);   
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
        
        if (!($opfile = fopen(".".DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR.$foldername.DIRECTORY_SEPARATOR."Adapt".$i."_infofile.txt", 'w')))
        {
            echo "Error opening cross-representation checks file .".DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR.$foldername.DIRECTORY_SEPARATOR."Adapt".$i."_infofile.txt";
            return;
        }
        
        fprintf($opfile,"Cross representation checks for adaptation set with id \"%s\":\n",$AdaptationSetAttr['id']);

        if($segmentAlignment == "true" || $subsegmentAlignment == "true" || $bitstreamSwitching == "true" )
        {
            $leafInfo=array();
            
            for ($j = 0;$j<sizeof($AdaptationSetAttr['Representation']['bandwidth']);$j++)
            {
                $leafInfo[$j] = loadLeafInfoFile(".".DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR.$foldername.DIRECTORY_SEPARATOR."Adapt".$i."rep".$j."_infofile.txt");
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
        file_put_contents($locate.DIRECTORY_SEPARATOR."Adapt".$i."_infofile.html",$temp_string);
			
    }
}

function process_mpd($mpdurl)
{
    global  $Adapt_arr,$Period_arr,$repno,$repnolist,$period_url,$locate,$string_info
    ,$count1,$count2,$perioddepth,$adaptsetdepth,$period_baseurl,$foldername,$type,$minBufferTime,$profiles,$validatemp4;
    
    $path_parts = pathinfo($mpdurl);
    $Baseurl=false;
    $setsegflag=false;
    $mpdfilename = $path_parts['filename'];		// determine name of actual MPD file

    if(isset($_POST['urlcode']))
    {
	
	
        $sessname = 'sess'.rand();
        session_name($sessname);

        $directories = array_diff(scandir(dirname(__FILE__).'/'.'temp'), array('..', '.'));

        foreach ($directories as $file)
        {
            if(file_exists(dirname(__FILE__).'/'.'temp'.'/'.$file))
            {
                $change = time()-filemtime(dirname(__FILE__).'/'.'temp'.'/'.$file);

                if($change>300)
                    rrmdir(dirname(__FILE__).'/'.'temp'.'/'.$file);
            }
        }

        //print_r2("I'm inside the mpd processing");
        //var_dump( $path_parts  );
        $foldername = 'id'.rand();
        $_SESSION['foldername']=$foldername;
        // rrmdir($locate);
        $locate = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$foldername;
        $_SESSION['locate'] = $locate;
        mkdir($locate,0777);
        $totarr= array();

        copy(dirname(__FILE__).DIRECTORY_SEPARATOR.$validatemp4,$locate.DIRECTORY_SEPARATOR.$validatemp4);
        
        //Create log file so that it is available if accessed
        $progressXML = simplexml_load_string('<root><percent>0</percent><dataProcessed>0</dataProcessed><dataDownloaded>0</dataDownloaded></root>');
        $progressXML->asXml($locate.'/progress.xml');
        
        //libxml_use_internal_logors(true);
        $MPD = simplexml_load_file($GLOBALS["url"]);

        if (!$MPD)
        {
            die("Error: Failed loading XML file");
        }
        
        $dom_sxe = dom_import_simplexml($MPD);

        if (!$dom_sxe)
        {
            exit;
        }
        //print_r( $dom_sxe);		
/////////////////////Validate MPD//////////////////////////////////////////////////////////////
             copy_folder(dirname(__FILE__).DIRECTORY_SEPARATOR."mpdvalidator",$locate.DIRECTORY_SEPARATOR."mpdvalidator");
			             chdir($locate.DIRECTORY_SEPARATOR."mpdvalidator");
						//  system ("ant run -Dinput=".$mpdurl." 2>&1",$mpdvalidator);
						$mpdvalidator = syscall("ant run -Dinput=".$mpdurl);
						$mpdvalidator = str_replace('[java]',"",$mpdvalidator);
						$valid_word = 'Start XLink resolving';
						$report_start = strpos($mpdvalidator,$valid_word);
						$mpdvalidator=substr ($mpdvalidator,$report_start);
					//	print_r2($mpdvalidator);
						$mpdreport = fopen($locate.'/mpdreport.txt','a+b');
								fwrite($mpdreport,$mpdvalidator);

						$temp_string = str_replace (array('$Template$'),array("mpdreport"),$string_info);
            $mpd_rep_loc =  '/temp/'.$foldername.'/mpdreport.html';

            file_put_contents($locate.'//mpdreport.html',$temp_string);
						$exit=false;
						
						if(strpos($mpdvalidator,"XLink resolving successful")!==false)
                            $totarr[]='true';
							else{
							$totarr[]=$mpd_rep_loc;
							$exit = true;
							}
						if(strpos($mpdvalidator,"MPD validation successful")!==false)
                          $totarr[]='true';
							else{
							$totarr[]=$mpd_rep_loc;
							$exit = true;
							}
							if(strpos($mpdvalidator,"Schematron validation successful")!==false)
                          $totarr[]='true';
							else{
							$totarr[]=$mpd_rep_loc;
							$exit =true;
							}
							
							if ($exit===true)
							{
        $stri=json_encode($totarr);
							echo $stri;
							            session_destroy();
							exit;
							}

						
			 
		/////////////////////////////////////////////////////////////////////////////////////////////////
        $dom = new DOMDocument('1.0');
        $dom_sxe = $dom->importNode($dom_sxe, true);
        //				print_r($dom_sxe);		

        $dom_sxe = $dom->appendChild($dom_sxe);

        $MPD = $dom->getElementsByTagName('MPD')->item(0);

        $type = $MPD->getAttribute ( 'type');
		if($type === 'dynamic')
		{
		            echo 'dynamic';
					exit;

		}
        $minBufferTime = $MPD->getAttribute('minBufferTime');
        $profiles = $MPD -> getAttribute('profiles');
        $mediaPresentationDuration = $MPD ->getAttribute('mediaPresentationDuration');

        /////////////////////////////////////////////////////////////////////////////////
        $y=str_replace("PT","",$mediaPresentationDuration);
        if(strpos($y,'H')!==false)
        {
            $H = explode("H",$y);
            $y=str_replace("H","",$y);
        }
        else
            $H[0]=0;
            
        if(strpos($y,'M')!==false)
        {
            $y=str_replace($H[0],"",$y);
            $M = explode("M",$y);
            $y=str_replace("M","",$y);
            $y=str_replace($M[0],"",$y);
        }
        else
            $M[0]=0;

        $S=explode("S",$y);
        $presentationduration=($H[0]*60*60)+($M[0]*60)+$S[0];
        //////////////////////////////////////////////////////////////////////////////////
        foreach ($dom->documentElement->childNodes as $node)
        {
            if($node->nodeName === 'Location')
                $locationNode = $node;
            if($node->nodeName === 'BaseURL')
                $baseURLNode = $node;    
            if($node->nodeName === 'Period')
                $periodNode = $node;   
            if($node->nodeName === 'AdaptationSet')
                $AdapNode = $node;
        }
        
        $val = $dom->getElementsByTagName('BaseURL');
        $segflag = $dom->getElementsByTagName('SegmentTemplate');

        if($segflag->length>0)
            $setsegflag=true;

        if($val->length>0)
        {
            $Baseurl=true;
            
            for($i=0;$i<sizeof($val);$i++)
            {
                $base = $val->item($i);
                $par = $base->parentNode;
                $name = $par->tagName;
                if($name == 'MPD')
                {
                    $dir = $base->nodeValue;
					if ($dir==='./')
		               $dir = dirname($GLOBALS["url"]);

                }
            }
        
            if(!isset($dir))
                $dir = dirname($GLOBALS["url"]);
        }
        else
            $dir = dirname($GLOBALS["url"]);

        //print_r2($dir);
        /*if(isset($baseURLNode) )
        {
        $domper = new DOMDocument ('1.0');

        $baseURLNode = $domper->importNode ( $baseURLNode , true);
        $baseURLNode = $domper->appendChild($baseURLNode);
        $dir = $baseURLNode->nodeValue;
        print_r2($dir);
        }*/
        //else
        //$dir = dirname($GLOBALS["url"]);

        processPeriod($periodNode);
        $segm_url = array();
        $adapt_url = array();
        if($setsegflag)
        {

            for($k = 0; $k<sizeof($Period_arr); $k++)
            {
                if(!empty($Period_arr[$k]['SegmentTemplate']))
                {
                    //print_r2($Period_arr[$k]['SegmentTemplate']);
                    if(!empty($Period_arr[$k]['SegmentTemplate']['duration']))
                        $duration = $Period_arr[$k]['SegmentTemplate']['duration'];
                    else
                        $duration = 0;
                    if(!empty($Period_arr[$k]['SegmentTemplate']['timescale']))
                        $timescale = $Period_arr[$k]['SegmentTemplate']['timescale'];
                    else
                        $timescale = 1;
                        
                    if($duration!=0)
                    {
                        $duration = $duration/$timescale;
                        $segmentno = $presentationduration/$duration;
                    }

                    $startnumber = $Period_arr[$k]['SegmentTemplate']['startNumber'];
                    $initialization = $Period_arr[$k]['SegmentTemplate']['initialization'];
                    $media = $Period_arr[$k]['SegmentTemplate']['media'];
                    $timehash=null;
                    $timehash=array();

                    if(!empty($Period_arr[$k]['SegmentTemplate']['SegmentTimeline']))
                    {
                        $timeseg = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][0][0];

                        for($lok=0;$lok<sizeof($Period_arr[$k]['SegmentTemplate']['SegmentTimeline']);$lok++)
                        {
                            //print_r2($Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok]);

                            $d = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok][1];
                            $r = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok][2];
                            $te = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok][0];

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
                                
                                while($timeseg<$ende)
                                {
                                    $timehash[]= $timeseg;
                                    $timeseg=$timeseg+$d;
                                }
                            }
                            
                            if( $r>0)
                            {
                                for($cn=0;$cn<=$r;$cn++)
                                {
                                    $timehash[]= $timeseg;
                                    $timeseg=$timeseg+$d;
                                }
                            }
                        }
                    }
                }
                
                for ($j = 0;$j<sizeof($Period_arr[$k]['Representation']['bandwidth']);$j++)
                {
                    $direct=$dir;
                    if($Baseurl===true)
                    {
                        if(!isset($perioddepth[0]))
                        $perioddepth[0]="";

                        if(!isset($adaptsetdepth[$k]))
                        $adaptsetdepth[$k]="";

                        $direct = $dir.$perioddepth[0].'//'.$adaptsetdepth[$k];
                        //print_r2($dir);
                    }
                    
                    if(!empty($Period_arr[$k]['Representation']['SegmentTemplate'][$j]))
                    {
                        $duration = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['duration'];
                        
                        if(!empty($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['timescale']))
                            $timescale = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['timescale'];
                        else 
                            $timescale = 1;
                            
                        //print_r2($timescale);
                        if($duration!=0)
                        {
                            $duration = $duration/$timescale;
                            $segmentno = $presentationduration/$duration;
                            //print_r2($startnumber);
                        }
                        $startnumber = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['startNumber'];

                        $initialization = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['initialization'];
                        $media = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['media'];

                        if(!empty($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline']))
                        {
                            $timeseg = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][0][0];

                            for($lok=0;$lok<sizeof($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline']);$lok++)
                            {
                                $timehash=array();
                                //print_r2($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok]);

                                $d = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok][1];
                                $r = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok][2];
                                $te = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok][0];

                                if($r == 0)
                                {
                                    $timehash[]= $timeseg;
                                    $timeseg = $timeseg+$d;
                                }
                                
                                if($r<0)
                                {
                                    if(!isset($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok+1]))
                                    $ende = $presentationduration*$timescale;
                                    else 
                                    $ende = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok+1];
                                    $ende=$ende;
                                    
                                    while($timeseg<$ende)
                                    {
                                        $timehash[]= $timeseg;
                                        $timeseg=$timeseg+$d;
                                    }
                                }
                                else
                                {
                                    for($cn=0;$cn<=$r;$cn++)
                                    {
                                        $timehash[]= $timeseg;
                                        $timeseg=$timeseg+$d;
                                    }
                                }
                                //print_r2($timehash);
                            }
                        }
                    }

                    $bandwidth = $Period_arr[$k]['Representation']['bandwidth'][$j];
                    $id = $Period_arr[$k]['Representation']['id'][$j];

                    $init = str_replace (array('$Bandwidth$','$RepresentationID$'),array($bandwidth,$id),$initialization);
                    $initurl = $direct."/".$init;
                    $segm_url[] = $initurl; 
                   $timehashmask=0;
                    if(!empty($timehash))
                    {
                        $segmentno = sizeof($timehash);
                        $startnumber = 1 ;
			$timehashmask = $timehash;
                    }

$signlocation = strpos($media,'%');
                     if($signlocation!==false)
					 {
                          if ($signlocation-strpos($media,'Number')===6)
						  {
						  $media = str_replace('$Number','',$media);
						  
						  }
						  
					 }
					 
                    for  ($i =0;$i<$segmentno;$i++ ) 
                    {
                        $segmenturl = str_replace (array('$Bandwidth$','$Number$','$RepresentationID$','$Time$'),array($bandwidth,$i+$startnumber,$id,$timehashmask[$i]),$media);
                          $segmenturl = sprintf($segmenturl,$startnumber+$i);
					   $segmenturl = str_replace('$','',$segmenturl);
						$segmenturl = $direct."/".$segmenturl;
                        $segm_url[]=$segmenturl;
                    }
                    
                    //unset ($timehash);
                    $adapt_url[] = $segm_url;
                    //print_r2($segm_url);
                    $segm_url= array();	
                }

                $period_url[] = $adapt_url;
                $adapt_url=array();
            }
            //print_r2($period_url);
        }

        if($Baseurl)
        {
            for($i=0;$i<sizeof($period_baseurl);$i++)
            {
                if(!isset($perioddepth[0]))
                    $perioddepth[0]="";
                    
                for($j=0;$j<sizeof($period_baseurl[$i]);$j++)
                {
                    if(!isset($adaptsetdepth[$i]))
                    $adaptsetdepth[$i]="";

                    for($lo=0;$lo<sizeof($period_baseurl[$i][$j]);$lo++)
                    {
                        $period_baseurl[$i][$j][$lo] = $dir.$perioddepth[0].'/'.$adaptsetdepth[$i].'/'.$period_baseurl[$i][$j][$lo];
                    }
                }
            }
            if($setsegflag===false)
            $period_url = $period_baseurl;
        }

        $size=array();

        //print_r2("Alo I'm here");
        //print_r2($sum_bits);
        $_SESSION['period_url'] = $period_url;
        
        $_SESSION['Period_arr'] = $Period_arr;

        $totarr[]=sizeof($period_url);
        for ($i=0;$i<sizeof($period_url);$i++)
        {
            $totarr[]=sizeof($period_url[$i]);
        }
        $peri=null;
        $totarr[] = $foldername;
        $stri=json_encode($totarr);

        //print_r2("printingtime".$time);
        if(isset($_SESSION['count1']))
            unset($_SESSION['count1']);

        if(isset($_SESSION['count2']))
            unset($_SESSION['count2']);

        $_SESSION['type'] = $type;
        $_SESSION['minBufferTime'] = $minBufferTime;

        echo $stri;
    }
    ////////////////////////////////////////////////////////////////////////////////////
    if(isset($_POST['download']))
    {
        $root= dirname(__FILE__);
        $destiny=array();

        if($count2>=sizeof($period_url[$count1]))
        {
            $count2=0;
            $count1=$count1+1;
        }
        
        if ($count1>=sizeof($period_url))
        {
            crossRepresentationProcess();
			$missingexist = file_exists ($locate.DIRECTORY_SEPARATOR.'missinglink.txt');
			if($missingexist){
			$temp_string = str_replace (array('$Template$'),array("missinglink"),$string_info);
        file_put_contents($locate.DIRECTORY_SEPARATOR.'missinglink.html',$temp_string);
		}
			$file_error[] = "done";
			for($i=0;$i<sizeof($Period_arr);$i++){
			            $searchadapt = file_get_contents($locate.DIRECTORY_SEPARATOR.'Adapt'. $i .'_infofile.txt');
						if(strpos($searchadapt,"Error")==false)
                $file_error[] = "noerror";
            else
                $file_error[] = "temp".'/'.$foldername.'/'.'Adapt'. $i .'_infofile.html';
         }
            session_destroy();
			if($missingexist){
               $file_error[]="temp".'/'.$foldername.'/missinglink.html';

			   }
			   else 
			   $file_error[]="noerror";
	   $send_string = json_encode($file_error);


            echo $send_string;
            exit;
        }
        else
        {
            $repno = "Adapt".$count1."rep".$count2;
            $file_string =  $locate.DIRECTORY_SEPARATOR.$repno.".zip";
            $pathdir=$locate.DIRECTORY_SEPARATOR.$repno.DIRECTORY_SEPARATOR;
            
            if (!file_exists($pathdir))
            {
                mkdir($pathdir, 0777, true);
            }
            
            $sizearray = downloaddata($pathdir,$period_url[$count1][$count2]);
            Assemble($pathdir,$period_url[$count1][$count2],$sizearray);
            rename($locate.DIRECTORY_SEPARATOR."mdatoffset.txt",$locate.DIRECTORY_SEPARATOR.$repno."mdatoffset.txt");

            //$repnolist[]=$repno;
            //print_r2(memory_get_peak_usage(true));
            ////////////////////////////////////////////////////
            $file_location = array();
            $exeloc=dirname(__FILE__);
            chdir($locate);
            //$xmlDurationParser=new sspmod_janus_Xml_Duration_Parser($minBufferTime);
            //$xmlDurationParser->parse();
            //$timeSeconds=$xmlDurationParser->getSeconds();
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
            
            exec($locate.DIRECTORY_SEPARATOR.$validatemp4." ".$locate.DIRECTORY_SEPARATOR.$repno.".mp4 "."-infofile ".$locate.DIRECTORY_SEPARATOR.$repno.".txt"." -offsetinfo ".$locate.DIRECTORY_SEPARATOR.$repno."mdatoffset.txt -logconsole".$processArguments );
            rename($locate.DIRECTORY_SEPARATOR."leafinfo.txt",$locate.DIRECTORY_SEPARATOR.$repno."_infofile.txt");
            $temp_string = str_replace (array('$Template$'),array($repno."_infofile"),$string_info);
            //print_r2($temp_string);
            file_put_contents($locate.DIRECTORY_SEPARATOR.$repno."_infofile.html",$temp_string);
            $file_location[] = "temp".'/'.$foldername.'/'.$repno."_infofile.html";

            $destiny[]=$locate.DIRECTORY_SEPARATOR.$repno."_infofile.txt";
            rename($locate.DIRECTORY_SEPARATOR."stderr.txt",$locate.DIRECTORY_SEPARATOR.$repno."log.txt");
            $temp_string = str_replace (array('$Template$'),array($repno."log"),$string_info);

            file_put_contents($locate.DIRECTORY_SEPARATOR.$repno."log.html",$temp_string);
            $file_location[] = "temp".'/'.$foldername.'/'.$repno."log.html";

            $destiny[]=$locate.DIRECTORY_SEPARATOR.$repno."log.txt";

            rename($locate.DIRECTORY_SEPARATOR."stdout.txt",$locate.DIRECTORY_SEPARATOR.$repno."myfile.txt");
            $temp_string = str_replace (array('$Template$'),array($repno."myfile"),$string_info);
            file_put_contents($locate.DIRECTORY_SEPARATOR.$repno."myfile.html",$temp_string);
            $file_location[] = "temp".'/'.$repno."myfile.html";
            $destiny[]=$locate.DIRECTORY_SEPARATOR.$repno."myfile.txt";

            $period_url[$count1][$count2]=null;
            //print_r2(memory_get_peak_usage(true));
            ob_flush();
            //ob_clean();
            $count2 = $count2+1;
            $search = file_get_contents($locate.DIRECTORY_SEPARATOR.$repno."log.txt");
            
            if(strpos($search,"error")==false)
                $file_location[] = "noerror";
            else
                $file_location[] = "error";

            $_SESSION['count2'] = $count2;
            $_SESSION['count1'] = $count1;
            $send_string = json_encode($file_location);
            echo $send_string;

        }
    }
}

function processPeriod($period)
{
    global $Adapt_arr,$Period_arr,$period_baseurl,$perioddepth,$Adapt_urlbase, $profiles;

    //var_dump($period);
    $domper = new DOMDocument ('1.0');
    $period = $domper->importNode ( $period , true);
    $period = $domper->appendChild($period);

    //var_dump ($domper);
    $Periodduration = $period->getAttribute('duration');
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
    global $Adapt_arr,$Period_arr, $Adapt_urlbase,$adaptsetdepth;
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
                // echo $Representation->item($i)->nodeName . "\n";
                $temprep=$Representation->item($i);
                $repbaseurl = $temprep->getElementsByTagName('BaseURL');
                
                for($j=0;$j<$repbaseurl->length;$j++)
                {
                    $base = $repbaseurl->item($j);
                    $lastbase[] = $base->nodeValue;
                }
                
                $rep_url[]=$lastbase;
                //var_dump ( $temprep);
                //var_dump ( $Representation);
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
    'sar'=>$sar,'bandwidth'=>$bandwidth,'SegmentTemplate'=>$rep_seg_temp, 'startWithSAP'=>$repStartWithSAP, 'profiles'=>$repProfiles, 'ContentProtectionElementCount'=>$ContentProtectionElementCountRep);
    
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

process_mpd($url);

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
$fp1 = fopen($locate.DIRECTORY_SEPARATOR.$repno.".mp4", 'a+');
if(file_exists($path.$names[$i])){

$size=$sizearr[$i];
$file2 = file_get_contents($path.$names[$i]);


fwrite($fp1,$file2);
fclose($fp1);
file_put_contents($locate.DIRECTORY_SEPARATOR.$repno.".txt",$index." ".$size."\n",FILE_APPEND);
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
            $progressXML->asXml($locate.'/progress.xml');

        }

        fflush($newfile);
        fclose($newfile);
        $initoffset = $initoffset+$file_size;
        $totalDataProcessed = $totalDataProcessed + $file_size;
    }
 
 }
 return $file_sizearr;
 
}

 function remote_file_size2($url){
	# Get all header information
	$data = get_headers($url, true);
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
    if(false === $dir) return;
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

?>