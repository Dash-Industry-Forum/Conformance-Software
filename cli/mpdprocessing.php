<?php

function process_mpd($url, $validation_only)
{
    global  $Adapt_arr,$Period_arr,$repno,$repnolist,$period_url,$locate,$string_info,
            $perioddepth,$adaptsetdepth,$period_baseurl,$foldername,
            $type,$minBufferTime,$profiles,$MPD; //Global variables to be used within the main function

    //  $path_parts = pathinfo($mpdurl); 
    $Baseurl=false; //define if Baseurl is used or no
    $setsegflag=false;

    $directories = array_diff(scandir(dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'), array('..', '.'));

    foreach ($directories as $file) // Clean temp folder from old sessions in order to save diskspace
    {
        if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$file)) // temp is folder contains all sessions folders
        {
            $change = time()-filemtime(dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$file); // duration of file implementation
            if($change > 300)
                rrmdir(dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$file); // if last time folder was modified exceed 300 second it should be removed 
        }
    }

    $foldername = 'id'.rand(); // get random name for session folder
    $locate = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$foldername; //session  folder location
    mkdir($locate,0777); // create session folder
    $totarr= array(); // array contains all data to be sent to client.

/* FIX for local files
    if(isset($_SESSION['fileContent'] ))        // If file is uploaded 
    {
        file_put_contents($locate.'/uploaded.mpd',$_SESSION['fileContent']); 
        $url_array[0] = $locate.'/uploaded.mpd';
        $GLOBALS["url"] = $locate.'/uploaded.mpd';
        $MPD_abs = simplexml_load_file($GLOBALS["url"]); // load mpd from url 
        $dom_abs = dom_import_simplexml($MPD_abs);
        $abs = new DOMDocument('1.0');
        $dom_abs = $abs->importNode($dom_abs, true); //create dom element to contain mpd 

        $dom_abs = $abs->appendChild($dom_abs);

        $MPD_abs = $abs->getElementsByTagName('MPD')->item(0); // access the parent "MPD" in mpd file
        $Baseurl_abs = $MPD_abs->getElementsByTagName('BaseURL');
        
        if($Baseurl_abs->length>0)
        {
            $Baseurl_abs = $Baseurl_abs->item(0);
            $absolute = $Baseurl_abs->nodeValue;
            if (($absolute==='./')||(strpos($absolute,'http') === false))
            {
                $url_array[2]=1;
            }
        }
        else
            $url_array[2]=1;
    }
*/

    //Create log file so that it is available if accessed
    $progressXML = simplexml_load_string('<root><percent>0</percent><dataProcessed>0</dataProcessed><dataDownloaded>0</dataDownloaded></root>');// get progress bar update
    $progressXML->asXml($locate.DIRECTORY_SEPARATOR.'progress.xml'); //progress xml location

    //libxml_use_internal_logors(true);
    $MPD_O = simplexml_load_file($url); // load mpd from url 
    if (!$MPD_O)
    {
        return(false);
    }

    $dom_sxe = dom_import_simplexml($MPD_O);
    if (!$dom_sxe) {
        return(false);
    }

    global $mpdvalidator;
    $validate_result = mpdvalidator($mpdvalidator, $url, $locate, $foldername);
    $exit=  $validate_result[0] || $validation_only;
    $totarr=$validate_result[1];
    $totarr[]=$validate_result[2];
    $schematronIssuesReport = $validate_result[2];

    ///////////////////////////////////////Processing mpd attributes in order to get value//////////////////////////////////////////////////////////
    $dom = new DOMDocument('1.0');
    $dom_sxe = $dom->importNode($dom_sxe, true); //create dom element to contain mpd 

    //$dom_sxe = $dom->appendChild($dom_sxe);
    $dom->appendChild($dom_sxe);
    $MPD = $dom->getElementsByTagName('MPD')->item(0); // access the parent "MPD" in mpd file
    $mediaPresentationDuration = $MPD ->getAttribute('mediaPresentationDuration'); // get mediapersentation duration from mpd level
    $AST = $MPD -> getAttribute('availabilityStartTime');
    $bufferdepth  = $MPD->getAttribute('timeShiftBufferDepth');
    $bufferdepth = timeparsing($bufferdepth);
    $presentationduration = timeparsing($mediaPresentationDuration);

    createMpdFeatureList($dom,$schematronIssuesReport);

    $type = $MPD->getAttribute('type'); // get mpd type
    if($type === 'dynamic' && $dom->getElementsByTagName('SegmentTemplate')->length==0)
    {
        $totarr[] = $foldername;
        $totarr[]='dynamic';    // Incase of dynamic only mpd conformance.
        $exit = true;           // Session destroy flag is true
    }

    if($exit === true) // If session should be destroyed
    {
        if($type !== 'dynamic')
        {
            $totarr[] = $foldername;
        }
        return($totarr);
    }

    $minBufferTime = $MPD->getAttribute('minBufferTime');//get min buffer time
    $profiles = $MPD -> getAttribute('profiles');// get profiles

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
                if (!isAbsoluteURL($dir))   // if baseurl is relative URl
                    $dir = dirname($GLOBALS["url"]);// use location of Baseurl as location of mpd location
            }

        }
    
        if(!isset($dir))// if there is no Baseurl in mpd level 
            $dir = dirname($GLOBALS["url"]);// set location of segments dir as mpd location
    } else {
        $dir = dirname($GLOBALS["url"]); // if there is no Baseurl in mpd level,set location of segments dir as mpd location
    }

    $start =  processPeriod($periodNode,$dir); // start getting information from period level
    $start = timeparsing($start);//Get start time in seconds
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

                if(!empty($Period_arr[$k]['SegmentTemplate']['timescale']))// check time scale for given segment template
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

                        if($r<0) //Repeat untill the last segment within presentation
                        {
                            if(!isset($Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok+1]))
                                $ende = $presentationduration*$timescale; // end of presentation duration
                            else 
                                $ende = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok+1];

                            $ende=$ende;
                            
                            while($timeseg<$ende) // calculate time segment until the end of duration
                            {
                                $timehash[]= $timeseg; //contain duration of all segments cumulatively
                                $timeseg=$timeseg+$d;
                            }
                        }

                        if($r > 0)
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

                    $direct = $dir.$perioddepth[0].'/'.$adaptsetdepth[$k]; // combine baseURLs in both period level and adaptationset level
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
                        $timehash=array(); //contains time tag for each segment

                        for($lok=0;$lok<sizeof($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline']);$lok++)//loop on timeline
                        {
                            $d = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok][1];//get d
                            $r = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok][2];//get r
                            $te = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok][0];//get te

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
                                for($cn=0;$cn<=$r;$cn++)//if r is positive number (handles no duration repeat as well)
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
                    if ($signlocation-strpos($media,'Number')===6) {
                        $media = str_replace('$Number','',$media);
                    }
                }

                if($type==="dynamic")
                {
                    if($dom->getElementsByTagName('SegmentTimeline')->length!==0)
                    {
                        $totarr[]='dynamic';
                        return($totarr);
                    }
                    $segmentinfo = dynamicnumber($bufferdepth,$duration,$AST,$start,$Period_arr);
                    $segmentno = $segmentinfo[1]; //Latest available segment number
                    $i = $segmentinfo[0];// first segment in buffer
                } else {
                     $i = 0;
                }

                while($i<$segmentno)
                {
                    $segmenturl = str_replace (array('$Bandwidth$','$Number$','$RepresentationID$','$Time$'),array($bandwidth,$i+$startnumber,$id,$timehashmask[$i]),$media);//replace all media template values by actuall values
                    $segmenturl = sprintf($segmenturl,$startnumber+$i);
                    $segmenturl = str_replace('$','',$segmenturl);//clean segment url from any extra signs
                    $segmenturl = $direct."/".$segmenturl; // get full segment url
                    $segm_url[] = removeabunchofslashes($segmenturl); //add URL to segments URL array
                    $i++;
                }

                $adapt_url[] = $segm_url; // contains all representations within certain adaptation set
                $segm_url= array();    // delete segment url array and process the next representation
            }

            $period_url[] = $adapt_url;// add all adaptationset urls to period array
            $adapt_url=array(); // delete adaptationset array and process the next adaptation set
        }
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
                    if( !isAbsoluteURL($period_baseurl[$i][$j][$lo]))
                        $period_baseurl[$i][$j][$lo] = removeabunchofslashes($dir.$perioddepth[0].'/'.$adaptsetdepth[$i].'/'.$period_baseurl[$i][$j][$lo]);//combine all baseurls                       
                }
            }
        }
        if($setsegflag===false)
            $period_url = $period_baseurl;// if segment template is not used, use baseurl
    }

    $size=array();

    $totarr[]=sizeof($period_url); // get number of periods
    for ($i=0;$i<sizeof($period_url);$i++) // loop on periods
    {
        $totarr[]=sizeof($period_url[$i]);//get number of adaptationsets
    }
    $peri=null;
    $totarr[] = $foldername;// add session name 
    return $totarr;
}

function download($adaptation_set, $representation)
{
    global  $Adapt_arr,$Period_arr,$repno,$repnolist,$period_url,$locate,$string_info,
            $perioddepth,$adaptsetdepth,$period_baseurl,$foldername,
            $type,$minBufferTime,$profiles,$MPD,$validatemp4,$verbose; //Global variables to be used within the main function

    $root = dirname(__FILE__);
    $destiny =array();

    $repno = "Adapt".$adaptation_set."rep".$representation; // presentation unique name
    $pathdir=$locate.DIRECTORY_SEPARATOR.$repno.DIRECTORY_SEPARATOR;

    if (!file_exists($pathdir)) {
        mkdir($pathdir, 0777, true); // create folder for each presentation
    }
        
    $sizearray = downloaddata($pathdir,$period_url[$adaptation_set][$representation]); // download data 
    if(!file_exists($locate.DIRECTORY_SEPARATOR."missinglink.txt") && $sizearray !== 0)
    {
        Assemble($pathdir,$period_url[$adaptation_set][$representation],$sizearray); // Assemble all presentation in to one presentation
        rename($locate.DIRECTORY_SEPARATOR."mdatoffset.txt",$locate.DIRECTORY_SEPARATOR.$repno."mdatoffset.txt"); //rename txt file contains mdatoffset

        $file_location = array();
        $exeloc=dirname(__FILE__);
        chdir($locate);
        $timeSeconds=str_replace("PT","",$minBufferTime);
        $timeSeconds=str_replace("S","",$timeSeconds);

        // Check the features that are supported by the ISO validator
        exec("\"$validatemp4\" -help 2>&1", $out, $exit_code);
        $validateMp4Features = join("\n", $out);

        $processArguments = " -minbuffertime ".$timeSeconds." -bandwidth ";
        $processArguments=$processArguments.$Period_arr[$adaptation_set]['Representation']['bandwidth'][$representation]." ";

        if(false !== strpos($validateMp4Features, '-sbw')) {
            $processArguments=$processArguments."-sbw ";
        }

        if($type=== "dynamic")
            $processArguments=$processArguments."-dynamic ";

        if($Period_arr[$adaptation_set]['Representation']['startWithSAP'][$representation] != "")
            $processArguments=$processArguments."-startwithsap ".$Period_arr[$adaptation_set]['Representation']['startWithSAP'][$representation]." ";

        if(false !== strpos($validateMp4Features, '-indexrange') && $Period_arr[$adaptation_set]['Representation']['indexRange'][$representation] != "")
            $processArguments=$processArguments."-indexrange ".$Period_arr[$adaptation_set]['Representation']['indexRange'][$representation]." ";

        if(strpos($Period_arr[$adaptation_set]['Representation']['profiles'][$representation],"urn:mpeg:dash:profile:isoff-on-demand:2011") !== false)
            $processArguments=$processArguments."-isoondemand ";

        if(strpos($Period_arr[$adaptation_set]['Representation']['profiles'][$representation],"urn:mpeg:dash:profile:isoff-live:2011") !== false)
            $processArguments=$processArguments."-isolive ";

        if(strpos($Period_arr[$adaptation_set]['Representation']['profiles'][$representation],"urn:mpeg:dash:profile:isoff-main:2011") !== false)
            $processArguments=$processArguments."-isomain ";

        $dash264=false;
        if(strpos($Period_arr[$adaptation_set]['Representation']['profiles'][$representation],"http://dashif.org/guidelines/dash264") !== false)
        {
             $processArguments=$processArguments."-dash264base ";
             $dash264=true;
        }

        if($Period_arr[$adaptation_set]['Representation']['ContentProtectionElementCount'][$representation] > 0 && $dash264 == true)
        {
            $processArguments=$processArguments."-dash264enc ";
        }

        $test = '"'.$validatemp4.'" '.
                $locate.DIRECTORY_SEPARATOR.$repno.".mp4 ".
                "-infofile ".$locate.DIRECTORY_SEPARATOR.$repno.".txt ".
                "-offsetinfo ".$locate.DIRECTORY_SEPARATOR.$repno."mdatoffset.txt ".
                "-logconsole".$processArguments;
        $verbose && fprintf(STDOUT, "Executing command: %s\n", $test);
        exec($test, $out, $exit_code); //Excute conformance software
        if(0 == $exit_code)
        {
            rename($locate.DIRECTORY_SEPARATOR."leafinfo.txt",$locate.DIRECTORY_SEPARATOR.$repno."_infofile.txt"); //Rename infor file to contain representation number (to avoid over writing 
            $file_location[] = "temp".DIRECTORY_SEPARATOR.$foldername.DIRECTORY_SEPARATOR.$repno."_infofile.txt";
            $destiny[]=$locate.DIRECTORY_SEPARATOR.$repno."_infofile.txt";

            rename($locate.DIRECTORY_SEPARATOR."stderr.txt",$locate.DIRECTORY_SEPARATOR.$repno."_log.txt");//Rename conformance software output file to representation number file
            $file_location[] = "temp".DIRECTORY_SEPARATOR.$foldername.DIRECTORY_SEPARATOR.$repno."_log.txt";// add it to file location which is sent to client to get URL of log file on server
            $destiny[]=$locate.DIRECTORY_SEPARATOR.$repno."_log.txt";

            $file_location[] = "temp".DIRECTORY_SEPARATOR.$foldername.DIRECTORY_SEPARATOR.$repno."_myfile.txt";
            $destiny[]=$locate.DIRECTORY_SEPARATOR.$repno."_myfile.txt";

            $period_url[$adaptation_set][$representation] = null;

            $search = file_get_contents($locate.DIRECTORY_SEPARATOR.$repno."_log.txt");//Search for errors within log file
            if(strpos($search,"error")==false) //if no error , notify client with no error
                $file_location[] = "noerror";
            else
                $file_location[] = "error";//else notify client with error
        }
        else
        {
            $file_location[] = $test;

            rename($locate.DIRECTORY_SEPARATOR."stderr.txt",$locate.DIRECTORY_SEPARATOR.$repno."_log.txt");//Rename conformance software output file to representation number file
            $file_location[] = "temp".DIRECTORY_SEPARATOR.$foldername.DIRECTORY_SEPARATOR.$repno."_log.txt";// add it to file location which is sent to client to get URL of log file on server
            $destiny[]=$locate.DIRECTORY_SEPARATOR.$repno."_log.txt";

            $file_location[] = 'validatorerror';
        }
    }
    else
    {
        rename($locate.DIRECTORY_SEPARATOR."missinglink.txt",$locate.DIRECTORY_SEPARATOR.$repno."_missinglink.txt");//Rename conformance software output file to representation number file
        $file_location[] = "temp".DIRECTORY_SEPARATOR.$foldername.DIRECTORY_SEPARATOR.$repno."_missinglink.txt";// add it to file location which is sent to client to get URL of log file on server
        $destiny[]=$locate.DIRECTORY_SEPARATOR.$repno."_missinglink.txt";
        $file_location[] = 'notexist';
    }

    return $file_location;
}

function cross_representation_check()
{
    // Global variables to be used within the main function
    global  $Period_arr,$locate;

    crossRepresentationProcess();

    // check all info files if they contain Error
    for($i = 0; $i < sizeof($Period_arr); $i++)
    {
        if(file_exists($locate.DIRECTORY_SEPARATOR.'Adapt'. $i .'_infofile.txt')) 
        {
            $searchadapt = file_get_contents($locate.DIRECTORY_SEPARATOR.'Adapt'. $i .'_infofile.txt');
            if(strpos($searchadapt,"Error")==false) 
                $file_error[] = "noerror"; // no error found in text file
            else
                $file_error[] = "temp".DIRECTORY_SEPARATOR.$foldername.DIRECTORY_SEPARATOR.'Adapt'. $i .'_infofile.txt'; // add error file location to array
        } else {
            $file_error[] = "noerror";
        }
    }

    return($file_error);
}


?>
