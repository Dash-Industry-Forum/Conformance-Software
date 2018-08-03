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

function process_mpd()
{
    global $Adapt_arr, $Period_arr, $repno, $repnolist, $period_url, $locate, $string_info
    , $count1, $count2, $perioddepth, $adaptsetdepth, $period_baseurl, $foldername, $type, $minBufferTime, $profiles, $MPD, $session_id, $progressXML; //Global variables to be used within the main function
    //  $path_parts = pathinfo($mpdurl); 
    $Baseurl = false; //define if Baseurl is used or no
    $setsegflag = false;
    // $mpdfilename = $path_parts['filename'];		// determine name of actual MPD file
    if (isset($_FILES['afile']['tmp_name']))
    {
        $_SESSION['fileContent'] = file_get_contents($_FILES['afile']['tmp_name']);
    }
    // if (isset($_POST['urlcode'])) { // in case of client send first connection attempt
    $sessname = 'sess' . rand(); // get a random session name
    session_name($sessname); // set session name

    $directories = array_diff(scandir(dirname(__FILE__) . '/' . 'temp'), array('..', '.'));

    foreach ($directories as $file)
    { // Clean temp folder from old sessions in order to save diskspace
        if (file_exists(dirname(__FILE__) . '/' . 'temp' . '/' . $file))
        { // temp is folder contains all sessions folders
            $tempXML = simplexml_load_file(dirname(__FILE__) . '/' . 'temp' . '/' . $file . '/progress.xml');
            $change1 = 0; //duration after conformance test is done
            $change2 = time() - filemtime(dirname(__FILE__) . '/' . 'temp' . '/' . $file); // duration of file implementation
            if ((string) $tempXML->completed === "true")
            {
                $change1 = time() - (int) $tempXML->completed->attributes();
            }
            if ($change1 > 7200 || $change2 > 7200)  //clean folder after 2 hours after test completed or 2 hours after test started
                rrmdir(dirname(__FILE__) . '/' . 'temp' . '/' . $file); // if last time folder was modified exceed 300 second it should be removed 
        }
    }

    // Work out which validator binary to use
    $validatemp4 = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? "validatemp4-vs2010.exe" : "ValidateMP4.exe";
    //var_dump( $path_parts  );
    if (isset($_POST['foldername']))
    {
        $foldername = $_POST['foldername'];
        $paths = explode("/", $foldername);
        if (count($paths) > 1)
            $foldername = end($paths);
    }
    else
        $foldername = 'id' . rand(); // get random name for session folder
        
//get a name for session folder from client.
    $_SESSION['foldername'] = $foldername;
    update_visitor_counter();
    // rrmdir($locate);
    $locate = dirname(__FILE__) . '/' . 'temp' . '/' . $foldername; //session  folder location
    $_SESSION['locate'] = $locate; // save session folder location

    $oldmask = umask(0);
    mkdir($locate, 0777, true); // create session folder
    umask($oldmask);
//        $totarr = array(); // array contains all data to be sent to client.
    copy(dirname(__FILE__) . "/" . $validatemp4, $locate . '/' . $validatemp4); // copy conformance tool to session folder to allow multi-session operation
    chmod($locate . '/' . $validatemp4, 0777);
    $url_array = json_decode($_POST['urlcode']);

    if (isset($_SESSION['fileContent']))
    {  // If file is uploaded 
        file_put_contents($locate . '/uploaded.mpd', $_SESSION['fileContent']);
        $url_array[0] = $locate . '/uploaded.mpd';
        $GLOBALS["url"] = $locate . '/uploaded.mpd';
        $MPD_abs = simplexml_load_file($GLOBALS["url"]); // load mpd from url 
        $dom_abs = dom_import_simplexml($MPD_abs);
        $abs = new DOMDocument('1.0');
        $dom_abs = $abs->importNode($dom_abs, true); //create dom element to contain mpd 

        $dom_abs = $abs->appendChild($dom_abs);

        $MPD_abs = $abs->getElementsByTagName('MPD')->item(0); // access the parent "MPD" in mpd file
        $Baseurl_abs = $MPD_abs->getElementsByTagName('BaseURL');

        if ($Baseurl_abs->length > 0)
        {
            $Baseurl_abs = $Baseurl_abs->item(0);
            $absolute = $Baseurl_abs->nodeValue;
            if (($absolute === './') || (strpos($absolute, 'http') === false))
            {
                $url_array[2] = 1;
            }
        }
        else
            $url_array[2] = 1;
    }

    $url_array[3] = $locate; //Used for e.g. placing intermediate files etc.
    $cmaf_val = $url_array[4];     
    //The status of the mpd is logged in the visitor's log file.
    writeMPDStatus($url_array[0]);
    
    copy(dirname(__FILE__) . "/" . "featuretable.html", $locate . '/' . "featuretable.html"); // copy features list html file to session folder
    //Create log file so that it is available if accessed
    $progressXML = simplexml_load_string('<root><Profile></Profile><Progress><percent>0</percent><dataProcessed>0</dataProcessed><dataDownloaded>0</dataDownloaded><CurrentAdapt>1</CurrentAdapt><CurrentRep>1</CurrentRep></Progress><completed>false</completed></root>'); // get progress bar update
    $progressXML->asXml($locate . '/progress.xml'); //progress xml location
    //libxml_use_internal_logors(true);
    $MPD_O = simplexml_load_file($GLOBALS["url"]); // load mpd from url 

    if (!$MPD_O)
    {
        $progressXML->MPDError = "1"; //MPD error is updated in the progress.xml file.
        $progressXML->asXml(trim($locate . '/progress.xml'));
        echo $progressXML->asXML();
        die("Error: Failed loading XML file");
    }
    else
    {
        $progressXML->MPDError = "0";
        $progressXML->asXml(trim($locate . '/progress.xml'));
    }

    $dom_sxe = dom_import_simplexml($MPD_O);

    if (!$dom_sxe)
    {
        echo $progressXML->asXML();
        exit;
    }

    $validate_result = mpdvalidator($url_array, $locate, $foldername);
    writeMPDEndTime();
    $exit = $validate_result[0];
    $totarr = $validate_result[1];
    $schematronIssuesReport = $validate_result[2];
    //MPD Conformance results are written into the progress.xml file.
    $temp_mpdres = "";
    for ($totindex = 0; $totindex < 3; $totindex++)
    {
        if ($totarr[$totindex] == "true")
            $temp_mpdres = $temp_mpdres . "true ";
        else
            $temp_mpdres = $temp_mpdres . "false ";
    }
    $progressXML->MPDConformance = $temp_mpdres;
    $progressXML->MPDConformance->addAttribute('url', str_replace($_SERVER['DOCUMENT_ROOT'], 'http://' . $_SERVER['SERVER_NAME'], $locate . '/mpdreport.txt'));
    $progressXML->MPDConformance->addAttribute('MPDLocation', $url_array[0]); // add exact MPD file location for issue debugging purposes
    $progressXML->asXml(trim($locate . '/progress.xml'));
       
    //Create feature list here so that only MPD Conformance also shows feature list.
    $dom = new DOMDocument('1.0');
    $dom_sxe = $dom->importNode($dom_sxe, true); //create dom element to contain mpd 
    //$dom_sxe = $dom->appendChild($dom_sxe);
    $dom->appendChild($dom_sxe);
    $MPD = $dom->getElementsByTagName('MPD')->item(0); // access the parent "MPD" in mpd file
    createMpdFeatureList($dom, $schematronIssuesReport);
    $dom->save($locate."/providedMPD.mpd"); // save the MPD for issue debugging purposes

    // skip the rest when we should exit
    if ($exit === true)
    { //If session should be destroyed
        if ($type !== 'dynamic')
        {
            $totarr[] = $foldername;
        }
        $stri = json_encode($totarr); //Send results to client
//            echo $stri;
        session_destroy(); //Destroy session
        $progressXML->completed = "true";
        $progressXML->completed->addAttribute('time', time());
        $progressXML->asXml(trim($locate . '/progress.xml'));
        echo $progressXML->asXML();
        writeEndTime((int)$progressXML->completed->attributes());
        exit; //Exit
    }

    ///////////////////////////////////////Processing mpd attributes in order to get value//////////////////////////////////////////////////////////
    
    $mediaPresentationDuration = $MPD->getAttribute('mediaPresentationDuration'); // get mediapersentation duration from mpd level
    $AST = $MPD->getAttribute('availabilityStartTime');
    $bufferdepth = $MPD->getAttribute('timeShiftBufferDepth');
    $bufferdepth = timeparsing($bufferdepth);
    $presentationduration = timeparsing($mediaPresentationDuration);

    //createMpdFeatureList($dom, $schematronIssuesReport);

    $type = $MPD->getAttribute('type'); // get mpd type
    if ($type === 'dynamic' && $dom->getElementsByTagName('SegmentTemplate')->length == 0)
    {
        $totarr[] = $foldername;
        //This is messed up right now: dynamic conformance
        //$totarr[]='dynamic'; // Incase of dynamic only mpd conformance.
        //$exit =true;		 //Session destroy flag is true
    }

    $minBufferTime = $MPD->getAttribute('minBufferTime'); //get min buffer time
    $profiles = $MPD->getAttribute('profiles'); // get profiles
    $progressXML->Profile = $profiles;
    $progressXML->asXml(trim($locate . '/progress.xml'));

    $periodCount = 0;
    foreach ($dom->documentElement->childNodes as $node)
    { // search for all nodes within mpd
        if ($node->nodeName === 'Period')
        {
            if ($type !== 'dynamic' && $periodCount === 0)
            { //only process the first Period
                $periodNode = $node;
            }
            elseif ($type === 'dynamic')
            {
                #$dynamic_start = $node->attributes->getNamedItem('start')->nodeValue;
                $periodNodes[] = $node;
            }
            $periodCount++;
        }
    }

    $val = $dom->getElementsByTagName('BaseURL'); // get BaseUrl node
    $segflag = $dom->getElementsByTagName('SegmentTemplate'); //check if segment template exists or not

    if ($segflag->length > 0)
        $setsegflag = true; // Segment template is supported

    if ($val->length > 0)
    { // if baseurl is used
        $Baseurl = true; // set Baseurl flag = true

        for ($i = 0; $i < sizeof($val); $i++)
        {
            //check if Baseurl node exist in MPD level or lower level
            $base = $val->item($i);
            $par = $base->parentNode;
            $name = $par->tagName;
            if ($name == 'MPD')
            { // if exist in mpd level
                $dir = $base->nodeValue;
                if (!isAbsoluteURL($dir))   // if baseurl is relative URl
                    $dir = dirname($GLOBALS["url"]) . '/' . $dir; // use location of Baseurl as location of mpd location
            }
        }

        if (!isset($dir))// if there is no Baseurl in mpd level 
            $dir = dirname($GLOBALS["url"]) . '/'; // set location of segments dir as mpd location
    }
    else
    {
        $dir = dirname($GLOBALS["url"]) . '/'; // if there is no Baseurl in mpd level,set location of segments dir as mpd location
    }
    //Process SupplementalProperty for MPD Chaining, if present.
    $supplemental=$dom->getElementsByTagName('SupplementalProperty');
    if($supplemental->length >0)
    {
      $supplementalScheme=$supplemental->item(0)->getAttribute('schemeIdUri');
      if(($supplementalScheme === 'urn:mpeg:dash:chaining:2016') || ($supplementalScheme ==='urn:mpeg:dash:fallback:2016')){
	  $MPDChainingURL=$supplemental->item(0)->getAttribute('value');
      }

      $progressXML->MPDChainingURL=$MPDChainingURL;
      $progressXML->asXml(trim($locate . '/progress.xml'));
    }
    if($type === 'static'){
        $start = processPeriod($periodNode, $dir); // start getting information from period level
        $start = timeparsing($start); //Get start time in seconds
    }
    elseif($type === 'dynamic'){
        for($c=0; $c<$periodCount; $c++){
            $dyn_start = processPeriod($periodNodes[$c], $dir);
            $starts[] = timeparsing($dyn_start);
            $periods[] = $Period_arr;
        }
        
        $now = time();
        for($p=0; $p< sizeof($periods); $p++){
            if(!empty($periodNodes[$p]->getAttribute('duration')))
                $p_duration = $periodNodes[$p]->getAttribute('duration');
            
            $whereami = $now - (strtotime($AST) + $starts[$p]);
            $p_duration = timeparsing($p_duration);
            if($whereami <= $p_duration){
                $Period_arr = $periods[$p];
                $start = $starts[$p];
                break;
            }
        }
    }
    $segm_url = array(); // contains segments url within one 
    $adapt_url = array(); // contains all segments urls within adapatations set
    if ($setsegflag)
    { // Segment template is used
        for ($k = 0; $k < sizeof($Period_arr); $k++)
        { // loop on period array
            $Adapt_initialization_setflag = 0;
            if (!empty($Period_arr[$k]['SegmentTemplate']))
            {
                //print_r2($Period_arr[$k]['SegmentTemplate']);
                if (!empty($Period_arr[$k]['SegmentTemplate']['duration']))// get duration of segment template 
                    $duration = $Period_arr[$k]['SegmentTemplate']['duration'];
                else
                    $duration = 0; // if duration doesn't exist set duration to 0
                if (!empty($Period_arr[$k]['SegmentTemplate']['timescale']))// check time scale for given segment template
                    $timescale = $Period_arr[$k]['SegmentTemplate']['timescale'];
                else
                    $timescale = 1; // if doesn't exist set default to 1

                if ($duration != 0)
                {
                    $duration = $duration / $timescale; // get duration

                    $segmentno = ceil(($presentationduration - $start) / $duration); //get segment number
                }

                $startnumber = $Period_arr[$k]['SegmentTemplate']['startNumber'];  // get first number in segment
                $initialization = $Period_arr[$k]['SegmentTemplate']['initialization']; // get initialization degment 
                if ($initialization != "")
                {
                    $Adapt_initialization_setflag = 1;
                }
                $media = $Period_arr[$k]['SegmentTemplate']['media']; // get  media template
//                    $timehash = null; // used only in segment timeline 
                $timehash = array(); // contains all segmenttimelines for all segments

                if (!empty($Period_arr[$k]['SegmentTemplate']['SegmentTimeline']))
                { // in case of using Segment timeline
                    $timeseg = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][0][0]; // get time segment 

                    for ($lok = 0; $lok < sizeof($Period_arr[$k]['SegmentTemplate']['SegmentTimeline']); $lok++)
                    { // loop on segment time line 
                        $d = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok][1]; // get d 
                        $r = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok][2]; // get r 
                        $te = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok][0]; // get t

                        if ($r == 0)
                        {
                            $timehash[] = $timeseg;
                            $timeseg = $timeseg + $d;
                        }

                        if ($r < 0)
                        { //Repeat untill the last segment within presentation
                            if (!isset($Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok + 1]))
                                $ende = $presentationduration * $timescale; // end of presentation duration
                            else
                                $ende = $Period_arr[$k]['SegmentTemplate']['SegmentTimeline'][$lok + 1];

                            $ende = $ende;

                            while ($timeseg < $ende)
                            { // calculate time segment until the end of duration
                                $timehash[] = $timeseg; //contain duration of all segments cumulatively
                                $timeseg = $timeseg + $d;
                            }
                        }

                        if ($r > 0)
                        {
                            for ($cn = 0; $cn <= $r; $cn++)
                            { // repeat untill the amount of repeat is finished
                                $timehash[] = $timeseg;
                                $timeseg = $timeseg + $d;
                            }
                        }
                    }
                }
            }
            for ($j = 0; $j < sizeof($Period_arr[$k]['Representation']['bandwidth']); $j++)
            { // loop on adaptationset level
                $direct = $dir;
                if ($Baseurl === true)
                { // incase of using Base url
                    if (!isset($perioddepth[0])) // period doesn't contain any baseurl infromation
                        $perioddepth[0] = "";

                    if (!isset($adaptsetdepth[$k]))  // adaptation set doesn't contain any baseurl information
                        $adaptsetdepth[$k] = "";

                    $direct = $dir . $perioddepth[0] . $adaptsetdepth[$k]; // combine baseURLs in both period level and adaptationset level
                }

                if (!empty($Period_arr[$k]['Representation']['SegmentTemplate'][$j]))
                { // in case of using segmenttemplate
                    $duration = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['duration']; // get  segment duration attribute

                    if (!empty($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['timescale'])) //get time scale
                        $timescale = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['timescale'];
                    else
                        $timescale = 1; // set to 1 if not avaliable 

                    if ($duration != 0)
                    {
                        $duration = $duration / $timescale; // get duration scaled
                        $segmentno = ceil(($presentationduration - $start) / $duration); // get number of segments
                        //print_r2($startnumber);
                    }
                    $startnumber = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['startNumber']; // get start number

                    if ($Adapt_initialization_setflag == 0)
                    {
                        $initialization = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['initialization']; // get initialization
                    }
                    $media = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['media']; // get media template

                    if (!empty($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline']))
                    { // check timeline 
                        $timeseg = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][0][0]; // segment start time

                        for ($lok = 0; $lok < sizeof($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline']); $lok++)
                        {//loop on timeline
                            $timehash = array(); //contains time tag for each segment

                            $d = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok][1]; //get d
                            $r = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok][2]; //get r
                            $te = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok][0]; //get te

                            if ($r == 0)
                            {// no duration repeat
                                $timehash[] = $timeseg; //segment time stamp is same as segment time
                                $timeseg = $timeseg + $d;
                            }

                            if ($r < 0)
                            { // segments untill the end of presentation duration
                                if (!isset($Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok + 1]))
                                    $ende = $presentationduration * $timescale; // multiply presentation duration by timescale
                                else
                                    $ende = $Period_arr[$k]['Representation']['SegmentTemplate'][$j]['SegmentTimeline'][$lok + 1];
                                $ende = $ende;

                                while ($timeseg < $ende)
                                {
                                    $timehash[] = $timeseg;
                                    $timeseg = $timeseg + $d; //get time stamp for each segment by adding segment duration to previous time stamp
                                }
                            }
                            else
                            {
                                for ($cn = 0; $cn <= $r; $cn++)
                                {//if r is positive number
                                    $timehash[] = $timeseg;
                                    $timeseg = $timeseg + $d; // add duration to time segment to get time stamp for each segment
                                }
                            }
                        }
                    }
                }

                $bandwidth = $Period_arr[$k]['Representation']['bandwidth'][$j]; // get bandwidth of given representation
                $id = $Period_arr[$k]['Representation']['id'][$j]; // get id of given representation

                if($initialization != ""){
                    $init = str_replace(array('$Bandwidth$', '$RepresentationID$'), array($bandwidth, $id), $initialization); //get initialization segment template is replaced by bandwidth and id 
                    //test is $direct contains "/" in the end
                    if (substr($direct, -1) == '/')
                        $initurl = $direct . $init; //full initialization URL
                    else
                        $initurl = $direct . "/" . $init; //full initialization URL
                    $segm_url[] = $initurl; //add segment to URL
                }
                
                $timehashmask = 0; // default value if timeline doesnt exist
                if (!empty($timehash))
                { // if time line exist
                    $segmentno = sizeof($timehash); // number of segments
                    $startnumber = 1; // start number set to 1
                    $timehashmask = $timehash;
                }

                if ($type === "dynamic")
                {
//                        if ($dom->getElementsByTagName('SegmentTimeline')->length !== 0) {
                    //TODO currently $duration and timing is not properly set
                    //get $duration from "d" attribute
                    //set proper timing from "t" attribute
                    //check "r" etc.
//                        }
                    $segmentinfo = dynamicnumber($bufferdepth, $duration, $AST, $start, $startnumber, $Period_arr);
                    $segmentno = $segmentinfo[1]; //Latest available segment number
                    $i = $segmentinfo[0]; // first segment in buffer
                }
                else
                    $i = 0;

                while ($i < $segmentno)
                {
                    // here $Number$ / $Time$ is replaced (if it exists)
                    $segmenturl = str_replace(array('$Bandwidth$', '$Number$', '$RepresentationID$', '$Time$'), array($bandwidth, $i + $startnumber, $id, $timehashmask[$i]), $media); //replace all media template values by actuall values
                    // when the format is $Number%xd$ / $Time%xd$
                    $pos = strpos($segmenturl, '$Number');
                    if ($pos !== false)
                    {
                        if (substr($segmenturl, $pos + strlen('$Number'), 1) === '%')
                        {
                            $segmenturl = sprintf($segmenturl, $startnumber + $i);
                            $segmenturl = str_replace('$Number', '', $segmenturl);
                            $segmenturl = str_replace('$', '', $segmenturl);
                        }
                        else
                        {
                            error_log("It cannot happen! the format should be either \$Number$ or \$Number%xd$!");
                        }
                    }
                    $pos = strpos($segmenturl, '$Time');
                    if ($pos !== false)
                    {
                        if (substr($segmenturl, $pos + strlen('$Time'), 1) === '%')
                        {
                            $segmenturl = sprintf($segmenturl, $timehashmask[$i]);
                            $segmenturl = str_replace('$Time', '', $segmenturl);
                            $segmenturl = str_replace('$', '', $segmenturl);
                        }
                        else
                        {
                            error_log("It cannot happen! the format should be either \$Time$ or \$Time%xd$!");
                        }
                    }
//                        $segmenturl = str_replace('$', '', $segmenturl); //clean segment url from any extra signs
                    $segmenturl = $direct . "/" . $segmenturl; // get full segment url
                    $segm_url[] = removeabunchofslashes($segmenturl); //add URL to segments URL array
                    $i++;
                }
                $adapt_url[] = $segm_url; // contains all representations within certain adaptation set

                $segm_url = array(); // delete segment url array and process the next representation
            }

            $period_url[] = $adapt_url; // add all adaptationset urls to period array
            $adapt_url = array(); // delete adaptationset array and process the next adaptation set
        }
    }

    if ($Baseurl)
    {// in case of using Base url node
        for ($i = 0; $i < sizeof($period_baseurl); $i++)
        { // loop on base url
            if (!isset($perioddepth[0]))// if period doesn't contain baseurl
                $perioddepth[0] = "";

            for ($j = 0; $j < sizeof($period_baseurl[$i]); $j++)
            { //loop on baseurl in adaptationset  
                if (!isset($adaptsetdepth[$i])) // if adaptationset doesn't contain baseurl
                    $adaptsetdepth[$i] = "";

                for ($lo = 0; $lo < sizeof($period_baseurl[$i][$j]); $lo++)
                { // loop on baseurl in period level
                    if (!isAbsoluteURL($period_baseurl[$i][$j][$lo]))
                    {
                        $period_baseurl[$i][$j][$lo] = removeabunchofslashes($dir . $perioddepth[0] . '/' . $adaptsetdepth[$i] . '/' . $period_baseurl[$i][$j][$lo]); //combine all baseurls    
                    }
                }
            }
        }
        if ($setsegflag === false)
            $period_url = $period_baseurl; // if segment template is not used, use baseurl
    }
    $_SESSION['period_url'] = $period_url; // save all period urls in session variable

    $_SESSION['Period_arr'] = $Period_arr; //save all period parameters in session variable
    $totarr[] = sizeof($period_url); // get number of adaptation sets
    for ($i = 0; $i < sizeof($period_url); $i++)
    { // loop on periods
        $totarr[] = sizeof($period_url[$i]); //get number of represenations per adaptation set
    }
    $totarr[] = $periodCount;
    $totarr[] = $foldername; // add session name 
    $stri = json_encode($totarr); // encode array to send to client
    //print_r2($period_url);
    if (isset($_SESSION['count1']))  // reset adaptationset counter before download start
        unset($_SESSION['count1']);

    if (isset($_SESSION['count2'])) //reset representation counter before  download start
        unset($_SESSION['count2']);

    $_SESSION['type'] = $type;
    $_SESSION['minBufferTime'] = $minBufferTime;

    if ($type === "dynamic")
    {
        $totarr[] = "dynamic";
        $stri = json_encode($totarr); //Send results to client
        $progressXML->dynamic = "true"; // Update progress.xml file with info on dynamic MPD.
        $progressXML->asXml(trim($locate . '/progress.xml'));
    }
    else
    {
        $progressXML->dynamic = "false";
        $progressXML->asXml(trim($locate . '/progress.xml'));
    }

    //Question: why should we tell if it's dynamic or not only when segment template is used?!
    if ($setsegflag)
    { // Segment template is used
        if ($type === "dynamic")
        {
//                $totarr[] = "dynamic";
//                $progressXML->dynamic = "true"; // Update progress.xml file with info on dynamic MPD.
//                $progressXML->asXml(trim($locate.'/progress.xml'));
//                $stri = json_encode($totarr); //Send results to client
            if ($dom->getElementsByTagName('SegmentTimeline')->length !== 0)
            {
                $progressXML->SegmentTimeline = "true";
//                    echo $stri;
                session_destroy(); //Destroy session
                $progressXML->completed = "true";
                $progressXML->completed->addAttribute('time', time());
                $progressXML->asXml(trim($locate . '/progress.xml'));
                echo $progressXML->asXML();
                writeEndTime((int)$progressXML->completed->attributes());
                exit;
            }
        }
//            else{
//                $progressXML->dynamic = "false";
//                $progressXML->asXml(trim($locate.'/progress.xml'));
//            }
    }

    //check if SegmentList exist
    if ($dom->getElementsByTagName('SegmentList')->length !== 0)
    {
        $progressXML->segmentList = "true";
        $progressXML->asXml(trim($locate . '/progress.xml'));
        $stri = json_encode($totarr); //Send results to client
//                    echo $stri;
        session_destroy(); //Destroy session
        $progressXML->completed = "true";
        $progressXML->completed->addAttribute('time', time());
        $progressXML->asXml(trim($locate . '/progress.xml'));
        echo $progressXML->asXML();
        writeEndTime((int)$progressXML->completed->attributes());
        exit;
    }

    $ResultXML = $progressXML->addChild('Results'); // Create Results tree in progress.xml and updates tree later.
    for ($i1 = 0; $i1 < $periodCount; $i1++)
    {
        $PeriodXML = $ResultXML->addChild('Period');
        for ($j1 = 0; $j1 < sizeof($period_url); $j1++)
        {
            $AdaptationXML = $PeriodXML->addChild('Adaptation');
            for ($k1 = 0; $k1 < sizeof($period_url[$j1]); $k1++)
            {
                $RepXML = $AdaptationXML->addChild('Representation');
                $RepXML->addAttribute('id', $k1 + 1);
                
                $str = '{';
                for($l1 = 0; $l1 < sizeof($period_url[$j1][$k1]); $l1++)
                {
                    $str = $str . $period_url[$j1][$k1][$l1] . ',';
                }
                $str = substr($str, 0, strlen($str)-1) . '}';
                $RepXML->addAttribute('url', $str);
            }
        }
    }
    $progressXML->asXml(trim($locate . '/progress.xml'));

//        echo $stri; // send no. of periods,adaptationsets, representation, mpd file to client
    //  }
    ////////////////////////////////////////////////////////////////////////////////////
    //if (isset($_POST['download'])) { // get request from client to download segments
    //Segments are downloaded in a sequence and conformance results are written into progress.xml.
    while ($count1 <= sizeof($period_url))
    {
        $root = dirname(__FILE__);
        $destiny = array();

        if ($count2 >= sizeof($period_url[$count1]))
        {//check if all representations within a segment is downloaded
	    if ($cmaf_val == "yes" )//&& $shouldCompare)  // if all data in an adaptation set is downloaded properly, then start comparing
                compareRepresentations();
            $count2 = 0;  // reset representation counter when new adaptation set is proccesed 
            $count1 = $count1 + 1; // increase adapatationset counter
            if ($count1 < sizeof($period_url))
            {
                //$AdaptationXML = $ResultXML->addChild('Adaptation');
                //$AdaptationXML->addAttribute('id',$count1+1);
                $progressXML->Progress->CurrentAdapt = $count1 + 1; // Update currently running AdaptationSet, used in display status message.
                $progressXML->asXml(trim($locate . '/progress.xml'));
            }
        }

        if ($count1 >= sizeof($period_url))
        { //check if all adapatationsets is processed 
            error_log("AllAdaptDownloaded");
            if($cmaf_val == "yes"){
                checkRepresentationsConformance();//Check after downloading all AdaptationSets.
                checkSwitchingSets();
                }
            crossRepresentationProcess();
            $missingexist = file_exists($locate . '/missinglink.txt'); //check if any broken urls is detected
            if ($missingexist)
            {
                $temp_string = str_replace(array('$Template$'), array("missinglink"), $string_info);
                file_put_contents($locate . '/missinglink.html', $temp_string); //create html file contains report for all missing segments
            }
            $file_error[] = "done";
            for ($i = 0; $i < sizeof($Period_arr); $i++)
            {  // check all info files if they contain Error 
                if (file_exists($locate . '/Adapt' . $i . '_infofile.txt'))
                {
                    $searchadapt = file_get_contents($locate . '/Adapt' . $i . '_infofile.txt');
                    if (strpos($searchadapt, "Error") == false)
                    {
                        $ResultXML->Period[0]->Adaptation[$i]->addChild('CrossRepresentation', 'noerror');
                        $file_error[] = "noerror"; // no error found in text file
                    }
                    else
                    {
                        $ResultXML->Period[0]->Adaptation[$i]->addChild('CrossRepresentation', 'error');
                        $file_error[] = "temp" . '/' . $foldername . '/' . 'Adapt' . $i . '_infofile.html'; // add error file location to array
                    }
                }
                else
                {
                    $ResultXML->Period[0]->Adaptation[$i]->addChild('CrossRepresentation', 'noerror');
                    $file_error[] = "noerror";
                }
                  
                if($cmaf_val == "yes" && file_exists($locate . '/Adapt' . $i . '_compInfo.txt')){
                    $searchfiles = file_get_contents($locate . '/Adapt' . $i . '_compInfo.txt');
                    if(strpos($searchfiles, "Error") == false && strpos($searchfiles, "CMAF check violated") == false){
                        $ResultXML->Period[0]->Adaptation[$i]->addChild('ComparedRepresentations', 'noerror');
                        $file_error[] = "noerror"; // no error found in text file
                    }
                    else{
                        $ResultXML->Period[0]->Adaptation[$i]->addChild('ComparedRepresentations', 'error');
                        $file_error[] = $locate.'/Adapt'.$i.'_compInfo.html'; // add error file location to array
                    }
                    $ResultXML->Period[0]->Adaptation[$i]->ComparedRepresentations->addAttribute('url', str_replace($_SERVER['DOCUMENT_ROOT'], 'http://' . $_SERVER['SERVER_NAME'], $locate.'/Adapt'.$i.'_compInfo.txt'));
                }
                
                $ResultXML->Period[0]->Adaptation[$i]->CrossRepresentation->addAttribute('url', str_replace($_SERVER['DOCUMENT_ROOT'], 'http://' . $_SERVER['SERVER_NAME'], $locate . '/Adapt' . $i . '_infofile.txt'));
                $progressXML->asXml(trim($locate . '/progress.xml'));
            }
            //Add SelectionSet and Presentation profile error elements if present to progress xml.
            if ($cmaf_val == "yes"){
                if(file_exists($locate.'/SelectionSet_infofile.txt')){
                    $selSetFile=file_get_contents($locate.'/SelectionSet_infofile.txt');
                    if(strpos($selSetFile, "CMAF check violated") == false){
                         $ResultXML->addChild('SelectionSet', 'noerror');
                         $file_error[] = "noerror"; // no error found in text file
                    }
                    else{
                        $ResultXML->addChild('SelectionSet', 'error');
                        $tempr_string = str_replace(array('$Template$'), array("SelectionSet_infofile"), $string_info); // this string shows a text file on HTML
                        file_put_contents($locate . '/SelectionSet_infofile.html', $tempr_string); // Create html file containing log file result
                        $file_error[] = $locate.'/SelectionSet_infofile.html'; // add error file location to array
                    }
                         
                }
                if(file_exists($locate.'/Presentation_infofile.txt')){
                    $presentnFile=file_get_contents($locate.'/Presentation_infofile.txt');
                    if(strpos($presentnFile, "CMAF check violated") == false){
                         $ResultXML->addChild('CMAFProfile', 'noerror');
                         $file_error[] = "noerror"; // no error found in text file
                    }
                    else{
                        $ResultXML->addChild('CMAFProfile', 'error');
                        $tempr_string = str_replace(array('$Template$'), array("Presentation_infofile"), $string_info); // this string shows a text file on HTML
                        file_put_contents($locate . '/Presentation_infofile.html', $tempr_string); // Create html file containing log file result
                        $file_error[] = $locate.'/Presentation_infofile.html'; // add error file location to array
                    }
                         
                }
                //$progressXML->asXml(trim($locate.'/progress.xml'));
            }
            session_destroy();
            if ($missingexist)
            {
                $ResultXML->addChild('BrokenURL', "error");
                $ResultXML->BrokenURL->addAttribute('url', str_replace($_SERVER['DOCUMENT_ROOT'], 'http://' . $_SERVER['SERVER_NAME'], $locate . '/missinglink.txt'));
                $file_error[] = "temp" . '/' . $foldername . '/missinglink.html';
            }
            else
            {
                $ResultXML->addChild('BrokenURL', "noerror");
                $file_error[] = "noerror";
            }
            $send_string = json_encode($file_error); //encode array to string and send it 

            error_log("ReturnFinish:" . $send_string);

//            echo $send_string; // send string with location of all error logs to client
            $progressXML->completed = "true";
            $progressXML->completed->addAttribute('time', time());
            $progressXML->asXml(trim($locate . '/progress.xml'));
            echo $progressXML->asXML();
            writeEndTime((int) $progressXML->completed->attributes());
            exit;
        }
        else
        {
            $repno = "Adapt" . $count1 . "rep" . $count2; // presentation unique name
            $pathdir = $locate . "/" . $repno . "/";

            $progressXML->Progress->CurrentRep = $count2 + 1; // Update currently running Representation, used in display status message.
            $progressXML->asXml(trim($locate . '/progress.xml'));
            error_log("Download_pathdir:" . $pathdir);

            if (!file_exists($pathdir))
            {
                $oldmask = umask(0);
                mkdir($pathdir, 0777, true); // create folder for each presentation
                umask($oldmask);
            }

            $tempcount1 = $count1; //don't know why we need a buffer, but it only works this way with php 7
            $sizearray = downloaddata($pathdir, $period_url[$count1][$count2]); // download data 
            if ($sizearray !== 0)
            {

                Assemble($pathdir, $period_url[$count1][$count2], $sizearray); // Assemble all presentation in to one presentation

                chmod($locate . '/' . "mdatoffset.txt", 0777);
                rename($locate . '/' . "mdatoffset.txt", $locate . '/' . $repno . "mdatoffset.txt"); //rename txt file contains mdatoffset

                $file_location = array();
                $exeloc = dirname(__FILE__);
                chdir($locate);
                $timeSeconds = str_replace("PT", "", $minBufferTime);
                $timeSeconds = str_replace("S", "", $timeSeconds);
                $processArguments = " -minbuffertime " . $timeSeconds . " -bandwidth ";
                $processArguments = $processArguments . $Period_arr[$count1]['Representation']['bandwidth'][$count2] . " ";
                $processArguments = $processArguments . "-width ";
                if ($Period_arr[$count1]['width'] === 0)
                {
                    $processArguments = $processArguments . $Period_arr[$count1]['Representation']['width'][$count2] . " -height ";
                }
                else
                {
                    $processArguments = $processArguments . $Period_arr[$count1]['width'] . " -height ";
                }
                if ($Period_arr[$count1]['height'] === 0)
                {
                    $processArguments = $processArguments . $Period_arr[$count1]['Representation']['height'][$count2] . " ";
                }
                else
                {
                    $processArguments = $processArguments . $Period_arr[$count1]['height'] . " ";
                }
                
                if($Period_arr[$count1]['sar'] !== 0){
                    $sar_x_y = explode(':', $Period_arr[$count1]['sar']);
                    $processArguments = $processArguments . '-sarx ' . $sar_x_y[0] . ' -sary ' . $sar_x_y[1] . " ";
                }
                elseif($Period_arr[$count1]['Representation']['sar'][$count2] !== 0){
                    $sar_x_y = explode(':', $Period_arr[$count1]['Representation']['sar'][$count2]);
                    $processArguments = $processArguments . '-sarx ' . $sar_x_y[0] . ' -sary ' . $sar_x_y[1] . " ";
                }

                if ($type === "dynamic")
                    $processArguments = $processArguments . "-dynamic ";

                if ($Period_arr[$count1]['Representation']['startWithSAP'][$count2] != "")
                    $processArguments = $processArguments . "-startwithsap " . $Period_arr[$count1]['Representation']['startWithSAP'][$count2] . " ";

                if (strpos($Period_arr[$count1]['Representation']['profiles'][$count2], "urn:mpeg:dash:profile:isoff-on-demand:2011") !== false)
                    $processArguments = $processArguments . "-isoondemand ";

                if (strpos($Period_arr[$count1]['Representation']['profiles'][$count2], "urn:mpeg:dash:profile:isoff-live:2011") !== false)
                    $processArguments = $processArguments . "-isolive ";

                if (strpos($Period_arr[$count1]['Representation']['profiles'][$count2], "urn:mpeg:dash:profile:isoff-main:2011") !== false)
                    $processArguments = $processArguments . "-isomain ";

                $dash264 = false;
                if (strpos($Period_arr[$count1]['Representation']['profiles'][$count2], "http://dashif.org/guidelines/dash264") !== false)
                {
                    $processArguments = $processArguments . "-dash264base ";
                    $dash264 = true;
                }

                if ($Period_arr[$count1]['Representation']['ContentProtectionElementCount'][$count2] > 0 && $dash264 == true)
                {
                    $processArguments = $processArguments . "-dash264enc ";
                }

                $processArguments = $processArguments . "-codecs ";
                if ($Period_arr[$count1]['codecs'] === 0)
                {
                    $codecs = $Period_arr[$count1]['Representation']['codecs'][$count2];
                }
                else
                {
                    $codecs = $Period_arr[$count1]['codecs'];
                }
                $processArguments = $processArguments . $codecs;

                // add indexRange to process arguments to give it to MPD validator

                if ($Period_arr[$count1]['Representation']['indexRange'][$count2] !== null)
                {
                    $indexRange = $Period_arr[$count1]['Representation']['indexRange'][$count2];
                    $processArguments = $processArguments . " -indexrange ";
                    $processArguments = $processArguments . $indexRange;
                }
                elseif ($Period_arr[$count1]['indexRange'] !== null)
                {
                    $indexRange = $Period_arr[$count1]['indexRange'];
                    $processArguments = $processArguments . " -indexrange ";
                    $processArguments = $processArguments . $indexRange;
                }

                $processArguments = $processArguments . " -audiochvalue ";
                if ($Period_arr[$count1]['AudioChannelValue'] === 0)
                {
                    $audioChValue = $Period_arr[$count1]['Representation']['AudioChannelValue'][$count2];
                }
                else
                {
                    $audioChValue = $Period_arr[$count1]['AudioChannelValue'];
                }
                $processArguments = $processArguments . $audioChValue;

                if ($Period_arr[$count1]['Representation']['SegmentTemplate']['RepresentationIndex'] !== null ||
                        $Period_arr[$count1]['Representation']['SegmentBase']['RepresentationIndex'] !== null ||
                        $Period_arr[$count1]['SegmentTemplate']['RepresentationIndex'] !== null ||
                        $Period_arr[$count1]['SegmentBase']['RepresentationIndex'] !== null)
                {
                    $processArguments = $processArguments . "-repIndex ";
                }
                
                if($Period_arr[$count1]['ContentProtection']!== null)
                {
                    if($Period_arr[$count1]['ContentProtection']['default_KID']!==null)
                    {
                        $default_KID=$Period_arr[$count1]['ContentProtection']['default_KID'];
                        $processArguments = $processArguments . " -default_kid ";
                        $processArguments = $processArguments . $default_KID;
                    }
                    $psshCount=sizeof($Period_arr[$count1]['ContentProtection']['psshBox']);
                    if($psshCount>0)
                    { 
                        $processArguments = $processArguments . " -pssh_count ";
                        $processArguments = $processArguments . $psshCount;                   
                        for($i=0; $i< $psshCount ; $i++)
                        {
                            $psshBox= $Period_arr[$count1]['ContentProtection']['psshBox'][$i];
                            $processArguments = $processArguments . " -psshbox ";
                            $pssh_file_loc=$locate."/psshBox".$i.".txt";
                            $pssh_file=fopen($pssh_file_loc, "w");
                            fwrite($pssh_file, $psshBox);
                            fclose($pssh_file);
                            $processArguments = $processArguments . $pssh_file_loc;
                        }
                    }
                }
                 

                error_log("validatemp4");
                // Work out which validator binary to use
                $validatemp4 = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? "validatemp4-vs2010.exe" : "ValidateMP4.exe";

                $file_loc = $locate . "/config_file.txt";
                $config_file = fopen($file_loc, "w");
                fwrite($config_file, $locate . '/' . $repno . ".mp4 " . "\n");
                fwrite($config_file, "-infofile" . "\n");
                fwrite($config_file, $locate . '/' . $repno . ".txt" . "\n");
                fwrite($config_file, "-offsetinfo" . "\n");
                fwrite($config_file, $locate . '/' . $repno . "mdatoffset.txt" . "\n");
                //fwrite($config_file, "-logconsole"."\n");
                $piece = explode(" ", $processArguments);
                foreach ($piece as $pie)
                {
                    if ($pie !== "")
                        fwrite($config_file, $pie . "\n");
                }

                fclose($config_file);

                if($cmaf_val == "yes")
                    $command = $locate . '/' . $validatemp4 . " -atomxml -cmaf -logconsole -configfile " . $file_loc;//
                else
                    $command = $locate . '/' . $validatemp4 . " -logconsole -configfile " . $file_loc;
                file_put_contents("command.txt", $command);
                $output = [];
                $returncode = 0; //the return code should stay 0 when there is no error!
                exec($command, $output, $returncode); //Excute conformance software
                if ($returncode !== 0)
                {
                    error_log("Processing " . $repno . " returns: " . $returncode);
                    if (filesize($locate . '/' . "stderr.txt") == 0)
                    {
                        // file is empty, add error information
                        $pos = strlen('Adapt');
                        $Adaptnum = (int) substr($repno, $pos, 1) + 1;
                        $pos += strlen('rep');
                        $repnum = (int) substr($repno, $pos, 1) + 1;
                        if($Period_arr[$count1]['mimeType']== "application/ttml+xml" || $Period_arr[$count1]['mimeType']== "image/jpeg")
                            file_put_contents($locate . '/' . "stderr.txt", "### error:  \n###        Failed to process Adaptation Set " . $Adaptnum . ", Representation " . $repnum . "!, as mimeType= '".$Period_arr[$count1]['mimeType']."' not supported");
                        else
                            file_put_contents($locate . '/' . "stderr.txt", "### error:  \n###        Failed to process Adaptation Set " . $Adaptnum . ", Representation " . $repnum . "!");
                    }
                }
                rename($locate . '/' . "leafinfo.txt", $locate . '/' . $repno . "_infofile.txt"); //Rename infor file to contain representation number (to avoid over writing 

                $file_location[] = "temp" . '/' . $foldername . '/' . $repno . "_infofile.html";

                $destiny[] = $locate . '/' . $repno . "_infofile.txt";
                rename($locate . '/' . "stderr.txt", $locate . '/' . $repno . "log.txt"); //Rename conformance software output file to representation number file
                $temp_string = str_replace(array('$Template$'), array($repno . "log"), $string_info); // this string shows a text file on HTML

                file_put_contents($locate . '/' . $repno . "log.html", $temp_string); // Create html file containing log file result
                $file_location[] = "temp" . '/' . $foldername . '/' . $repno . "log.html"; // add it to file location which is sent to client to get URL of log file on server

                $destiny[] = $locate . '/' . $repno . "log.txt";


                $file_location[] = "temp" . '/' . $repno . "myfile.html";
                $destiny[] = $locate . '/' . $repno . "myfile.txt";

                $period_url[$count1][$count2] = null;
                ob_flush();
                $count2 = $count2 + 1;
                $search = file_get_contents($locate . '/' . $repno . "log.txt"); //Search for errors within log file

                if (strpos($search, "error") === false)
                { //if no error , notify client with no error
                    $ResultXML->Period[0]->Adaptation[$tempcount1]->Representation[$count2 - 1] = "noerror";
                    $file_location[] = "noerror";
                }
                else
                {
                    $ResultXML->Period[0]->Adaptation[$tempcount1]->Representation[$count2 - 1] = "error";
                    $file_location[] = "error"; //else notify client with error
                }
                
                // Compare representations
                //if($shouldCompare){
                if($cmaf_val == "yes"){
                    $new_pathdir = $locate . "/Adapt" . $count1;
                    if (!file_exists($new_pathdir)){
                        $oldmask = umask(0);
                        mkdir($new_pathdir, 0777, true); // create folder for each presentation
                        umask($oldmask);
                    }
                    rename($locate . '/' . "atominfo.xml", $new_pathdir . '/' . $repno . ".xml");
                
                    //if($cmaf_val == "yes"){
                        $new_pathdir =  $new_pathdir . "/comparisonResults"; 
                        if (!file_exists($new_pathdir)){
                            $oldmask = umask(0);
                            mkdir($new_pathdir, 0777, true); // create folder for each presentation
                            umask($oldmask);
                        }
                    }
                //}
               // else{
               //     unlink($locate . '/' . "atominfo.xml");
               // }
                
                
                $ResultXML->Period[0]->Adaptation[$tempcount1]->Representation[$count2 - 1]->addAttribute('url', str_replace($_SERVER['DOCUMENT_ROOT'], 'http://' . $_SERVER['SERVER_NAME'], $locate . '/' . $repno . "log.txt"));
                $progressXML->asXml(trim($locate . '/progress.xml'));

                $_SESSION['count2'] = $count2; //Save the counters to session variables in order to use it the next time the client request download of next presentation
                $_SESSION['count1'] = $count1;
                $send_string = json_encode($file_location);
                error_log("RepresentationDownloaded_Return:" . $send_string);
//                echo $send_string;
            }
            else
            {
                $count2 = $count2 + 1;
                $_SESSION['count2'] = $count2;
                $_SESSION['count1'] = $count1;

                $file_location[] = 'notexist';
                $ResultXML->Period[0]->Adaptation[$tempcount1]->Representation[$count2 - 1] = "notexist";

                $send_string = json_encode($file_location);

                error_log("DownloadError_Return:" . $send_string);

//                echo $send_string;
            }
        }
    }
}

?>