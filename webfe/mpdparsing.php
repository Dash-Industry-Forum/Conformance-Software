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

/**
  This group of functions are responsible for parsing the nodes and attributes within MPD in order to find URLs of all segments
 * */
function processPeriod($period, &$dir)
{
    global $Adapt_arr, $Period_arr, $period_baseurl, $perioddepth, $Adapt_urlbase, $profiles, $Timeoffset, $id;

    $domper = new DOMDocument('1.0'); // Empty document object 
    $period = $domper->importNode($period, true); // add period node to domper
    $period = $domper->appendChild($period); // appnd it to domper

    $Periodduration = $period->getAttribute('duration'); // Search for duration attribute
    if ($period->hasAttribute('start'))
        $start = $period->getAttribute('start'); //start time for period
    else
        $start = "PT0H0M0.00S";
    $Period_segmentbase = $period->getElementsByTagName('SegmentBase'); // Serach if segmentbase exist
    $Timeoffset = 0; // set default timeoffset to 0 
    for ($i = 0; $i < $Period_segmentbase->length; $i++)
    {
        $base = $Period_segmentbase->item(0); //pass by  segment base within period
        $par = $base->parentNode;
        $name = $par->tagName;
        if ($name == 'Period')
        { // check if segmentbase exist in period level 
            $basearray = processSegmentBase($base); //call function to process segmentbase attributes
            if (!empty($basearray[0])) // return array contains presentationtimeoffset 
                $Timeoffset = $basearray[0];

            if (!empty($basearray[1]))
                $timescale = $basearray[1]; //return array contains timescale 

            if (!empty($basearray[2]))
                $indexRange = $basearray[2]; //return array contains index range		
        }
    }
    $Adaptationset = $domper->getElementsByTagName("AdaptationSet"); //Get adapation set node
    $periodbase = $domper->getElementsByTagName("BaseURL"); // Get BaseURL node
    $periodProfiles = $period->getAttribute('profiles'); //get profile attribute
    if ($periodProfiles === "")
        $periodProfiles = $profiles;

    $periodBitstreamSwitching = $period->getAttribute('bitstreamSwitching'); //Get bitstreamswitching attribute

    for ($i = 0; $i < $periodbase->length; $i++)
    {
        $base = $periodbase->item($i);
        $par = $base->parentNode;
        $name = $par->tagName;
        if ($name == 'Period')//Check if BaseURL node exist in period level
        {
            $baseurl = $base->nodeValue;
            $perioddepth[$i] = $baseurl; // if yes then this is the first level of MPD url

            if (isAbsoluteURL($baseurl))   // if baseurl is absolute URL, do not use the location of MPD as base URL:
            {
                $dir = "";
            }
        }
    }

    for ($i = 0; $i < $Adaptationset->length; $i++) //Iterate over all existing adaptationsets
    {
        $set = $Adaptationset->item($i); //Adapatitionset $i
        $Adapt_urlbase = null;
        processAdaptationset($set, $periodProfiles, $periodBitstreamSwitching); //Run adapationset processing function
        $Period_arr[$i] = $Adapt_arr; //add each Adaptation-set URLs to array
        $period_baseurl[$i] = $Adapt_urlbase; // in case of using BaseURL
    }
    return $start;
}

//Process Adapationset
function processAdaptationset($Adapt, $periodProfiles, $periodBitstreamSwitching)
{
    global $Adapt_arr, $Period_arr, $Adapt_urlbase, $adaptsetdepth, $Timeoffset, $perioddepth;
    //var_dump($Adapt);
    $dom = new DOMDocument('1.0');
    $Adapt = $dom->importNode($Adapt, true);
    $Adapt = $dom->appendChild($Adapt);
    if ($Adapt->hasAttributes())
    {
        //Get some attributes from Adaptationset
        $startWithSAP = $Adapt->getAttribute('startWithSAP');
        $segmentAlignment = $Adapt->getAttribute('segmentAlignment');
        $subsegmentAlignment = $Adapt->getAttribute('subsegmentAlignment');
        $idadapt = $Adapt->getAttribute('id');
        $scanType = $Adapt->getAttribute('scanType');
        $mimeType = $Adapt->getAttribute('mimeType');
        $codecs_AdaptSet = $Adapt->getAttribute('codecs'); // Get codecs, if present in Adaptation Set level
        if (empty($codecs_AdaptSet))
            $codecs_AdaptSet = 0;
        $height_AdaptSet = $Adapt->getAttribute('height'); // Get height, if present in Adaptation Set level
        if (empty($height_AdaptSet))
            $height_AdaptSet = 0;
        $width_AdaptSet = $Adapt->getAttribute('width'); // Get width, if present in Adaptation Set level
        if (empty($width_AdaptSet))
            $width_AdaptSet = 0;

        $lang_AdaptSet = $Adapt->getAttribute ('lang'); // Get language, if present in Adaptation Set level
        if(empty($lang_AdaptSet))
            $lang_AdaptSet=0;
            
        $adapsetProfiles = $Adapt->getAttribute('profiles');
        if ($adapsetProfiles === "")
            $adapsetProfiles = $periodProfiles;
        
        $sar_AdaptSet = $Adapt->getAttribute('sar');
        if($sar_AdaptSet === '')
            $sar_AdaptSet = 0;

        $bitstreamSwitching = $Adapt->getAttribute('bitstreamSwitching');
        if ($bitstreamSwitching === "")
            $bitstreamSwitching = $periodBitstreamSwitching;

        $ContentProtection = $dom->getElementsByTagName("ContentProtection"); // Search for Content Protection element
       
        //To extract default_KID and pssh information from ContentProtection
        $psshBox=array();
        if($ContentProtection->length >0)
        {
            for($i=0; $i < $ContentProtection->length; $i++ )
            {
             //   $tempContentProtection[$i]=processContentProtection($ContentProtection->item($i));
               // function processContentProtection($contentprotect)
                   $tempContentProtect=$ContentProtection->item($i);
                   $KID= $tempContentProtect->getAttribute('cenc:default_KID');
                if (!empty($KID))
                {    $default_KID = $KID;//$contentprotect->getAttribute('cenc:default_KID');
                }
                //$cenc_pssh=$tempContentProtect->textContent;
                $cencNS="urn:mpeg:cenc:2013";
                $cenc_pssh=$tempContentProtect->getElementsByTagNameNS($cencNS,'pssh')->item(0)->nodeValue;
                if(!empty($cenc_pssh))
                {
                    $psshBox[]=$cenc_pssh;
                }  
            }
            $ContentProtect_arr = array('default_KID' => $default_KID, 'psshBox' => $psshBox);
        }
        $Contentcomponent = $dom->getElementsByTagName("ContentComponent"); //Search for content component attribute
        $tr = $dom->childNodes->item(0)->nodeName;

        if ($Contentcomponent->length > 0)
        { //Check if content component exist
            $tempContentcomponent = $Contentcomponent->item(0);
            $contentType = $tempContentcomponent->getAttribute('contentType'); //Get content type
        }

        $Adapt_segmentbase = $Adapt->getElementsByTagName('SegmentBase'); //If segment base exist 
        $Adapt_Timeoffset = 0;
        for ($i = 0; $i < $Adapt_segmentbase->length; $i++)
        {
            $base = $Adapt_segmentbase->item(0);
            $par = $base->parentNode;
            $name = $par->tagName;
            if ($name === 'AdaptationSet')
            { // Check if segment base is direct child of Adapatationset 
                $basearray = processSegmentBase($base); //Process segmentbase
                if (!empty($basearray[0]))
                    $Adapt_Timeoffset = $basearray[0]; //Get timeoffset

                if (!empty($basearray[1]))
                    $timescale = $basearray[1]; //get timescale

                if (!empty($basearray[2]))
                {
                    $indexRange_AdaptSet = $basearray[2]; //return array contains indexRange
                }
            }
        }
        if ($Adapt_Timeoffset === 0) // if timeoffset exist then It has to replace the one existed on higher nodes
            $Adapt_Timeoffset = $Timeoffset;
        $baseurl = $Adapt->getElementsByTagName("BaseURL"); // check and process baseurl node if it exist in adapationset level
//        $adaptsetdepth = array();

        for ($i = 0; $i < $baseurl->length; $i++)
        {
            $base = $baseurl->item($i);
            $par = $base->parentNode;
            $name = $par->tagName;
            if ($name == 'AdaptationSet')
            {//Confirm Baseurl is direct child of adapationset
                $Adaptbase = $base->nodeValue;
                $adaptsetdepth[] = $Adaptbase; // Cumulative baseURL

                if (isAbsoluteURL($Adaptbase))
                {   // if baseurl is absolute URL, do not use the location of MPD as base URL:
                    $dir = "";
                    $perioddepth[0] = "";
                }
            }
        }

        $rep_seg_temp = array();
        $segmenttemplate = $dom->getElementsByTagName("SegmentTemplate"); //Check if segment template exist in adaptationSet level

        if ($segmenttemplate->length > 0)
        {
            $Adapt_seg_temp_setflag = 0;
            for ($i = 0; $i < $segmenttemplate->length; $i++)
            {
                $seg_arr = array();
                $seg = $segmenttemplate->item($i);
                $par = $seg->parentNode;
                $name = $par->tagName;
                if ($name == "AdaptationSet")
                {

                    $Adapt_seg_temp = processTemplate($seg);
                    $Adapt_seg_temp_setflag = 1;
                }
                else
                {
                    if (!$Adapt_seg_temp_setflag)
                    {
                        $Adapt_seg_temp = null;
                    }
                }
            }
        }
        else
        {
            $Adapt_seg_temp = 0;
        }

        //Check if AudioChannelConfiguration exists at AdapatationSet level.
        $audioChannelConfig_Adapt = $Adapt->getElementsByTagName("AudioChannelConfiguration");
        if ($audioChannelConfig_Adapt->length > 0)
        {
            $audioCh_Adapt_item = $audioChannelConfig_Adapt->item(0);
            $parNode = $audioCh_Adapt_item->parentNode;
            $parName = $parNode->tagName;
            if ($parName == "AdaptationSet")
            {
                $audioCh_Adapt_value = $audioCh_Adapt_item->getAttribute('value');
            }
            else
            {
                $audioCh_Adapt_value = 0;
            }
        }
        else
        {
            $audioCh_Adapt_value = 0;
        }

	//Check if SupplementalProperty for Aligned Switching Set is present
	$Supplemental_Adapt=$Adapt->getElementsByTagName("SupplementalProperty");
	//ToDo- Check if schemeIdUri is adaptation-set-switching:2016
        $AlignedSet_Adapt_value=0;
	if($Supplemental_Adapt->length>0)
	{
            
            $AlignedSet_Adapt_schemeIdUri = $Supplemental_Adapt->item(0)->getAttribute ('schemeIdUri');
            if(strpos($AlignedSet_Adapt_schemeIdUri,'urn:mpeg:dash:adaptation-set-switching:2016')!==FALSE){
                $AlignedSet_Adapt_value = $Supplemental_Adapt->item(0)->getAttribute ('value');
                $AlignedSet_Adapt_value = (int)$AlignedSet_Adapt_value;
            }
	}
	
	//
	
	//Check if Role is present at Adapt Set level- With respect to subtitle conformance- CMAF.
	$Role_Adapt=$Adapt->getElementsByTagName("Role");
	if($Role_Adapt->length>0)
	{
	    $Role_schemeId=$Role_Adapt->item(0)->getAttribute('schemeIdUri');
	    $Role_value=$Role_Adapt->item(0)->getAttribute('value');
	}
	else
	{
	    $Role_schemeId=0;
	    $Role_value=0;
	}
	$Role_AdaptSet=array('schemeIdUri'=> $Role_schemeId, 'value' => $Role_value);
	//

        $Representation = $dom->getElementsByTagName("Representation"); //Get representations node within Adapatationset
        if ($Representation->length > 0)
        {
            $rep_url = array();
            $rep_seg_temp = array();
            //Iterate on all representations within the given Adapatationset
            for ($i = 0; $i < $Representation->length; $i++)
            {
                $lastbase = array(); // Contains the latest BaseURL if exist
                $temprep = $Representation->item($i);
                $repbaseurl = $temprep->getElementsByTagName('BaseURL'); //check if representation contains BaseURL
                $Rep_segmentbase = $temprep->getElementsByTagName('SegmentBase'); //Check if segment Base exist

                if ($Rep_segmentbase->length > 0)
                {

                    $base = $Rep_segmentbase->item(0);
                    $segarray[] = processSegmentBase($base); // Process segment base
                    if (!empty($segarray[$i][0]))
                        $Rep_Timeoffset[] = $segarray[$i][0]; //Get presentationtimeoffset if exist
                    else
                        $Rep_Timeoffset[] = $Adapt_Timeoffset; // if not exist get the upper timeoffset

                    if (!empty($segarray[$i][1]))
                        $timescale = $segarray[$i][1];  // get timescale if exist

                    if (!empty($segarray[$i][2]))
                        $indexRange_RepSet[] = $segarray[$i][2];  //get index range if it exists              
                }
                else
                    $Rep_Timeoffset[] = $Adapt_Timeoffset; // if not exist get the upper timeoffset

                for ($j = 0; $j < $repbaseurl->length; $j++)
                { // Get baseurl and Iterate it 
                    $base = $repbaseurl->item($j); // baseURL
                    $lastbase[] = $base->nodeValue; // the last compnent in BaseURL
                }

                $rep_url[] = $lastbase; // add them in baseURL

                $repsegment = $temprep->getElementsByTagName("SegmentTemplate"); //In case presentation use SegmentTemplate
                $pass_seg = $repsegment->item(0);
                if ($repsegment->length > 0)
                    $rep_seg_temp[$i] = processTemplate($pass_seg); //Process segmentTemplate

                $idvar = $temprep->getAttribute('id'); // Get presentation ID
                if (empty($idvar))
                    $idvar = 0;

                $id[$i] = $idvar; // save id within array of ID
                //Get some Attributes from Presentation
                $repStartWithSAP[$i] = $temprep->getAttribute('startWithSAP');
                if ($repStartWithSAP[$i] === "")
                    $repStartWithSAP[$i] = $startWithSAP;

                $repProfiles[$i] = $temprep->getAttribute('profiles');
                if ($repProfiles[$i] === "")
                    $repProfiles[$i] = $adapsetProfiles;

                $repmimeTypevar = $temprep->getAttribute('mimeType');
                if(empty($repmimeTypevar))
                    $repmimeTypevar = 0;
                $repmimeType [$i] = $repmimeTypevar;
                
                $codecsvar = $temprep->getAttribute('codecs');
                if (empty($codecsvar))
                    $codecsvar = 0;
                $codecs[$i] = $codecsvar;

                $widthvar = $temprep->getAttribute('width');
                if (empty($widthvar))
                    $widthvar = 0;
                $width [$i] = $widthvar;

                $heightvar = $temprep->getAttribute('height');
                if (empty($heightvar))
                    $heightvar = 0;
                $height[$i] = $heightvar;
                if (empty($scantypevar))
                    $scantypevar = $temprep->getAttribute('scanType');
                if (empty($scantypevar))
                    $scantypevar = 0;
                $scanType = $scantypevar;

                $frameRatevar = $temprep->getAttribute('frameRate');
                if (empty($frameRatevar))
                    $frameRatevar = 0;
                $frameRate[$i] = $frameRatevar;

                $sarvar = $temprep->getAttribute('sar');
                if (empty($sarvar))
                    $sarvar = $sar_AdaptSet;
                $sar[$i] = $sarvar;
                
                $bandwidthvar = $temprep->getAttribute('bandwidth');
                if (empty($bandwidthvar))
                    $bandwidthvar = 0;
                $bandwidth[$i] = $bandwidthvar;

                if ($temprep->hasAttribute('timescale'))
                    $timescale = $temprep->getAttribute('timescale');

                $ContentProtectionElementCountRep[$i] = $temprep->getElementsByTagName("ContentProtection")->length;  //Process ContentProtection
                if ($ContentProtectionElementCountRep[$i] == 0)
                {
                    $ContentProtectionElementCountRep[$i] = $ContentProtection->length;
                }

                $audioChannelConfig_Rep = $temprep->getElementsByTagName('AudioChannelConfiguration'); //Check if AudioChannelConfiguration exists
                if ($audioChannelConfig_Rep->length > 0)
                {
                    $audioCh_Rep_item = $audioChannelConfig_Rep->item(0);
                    $audioCh_Rep_value = $audioCh_Rep_item->getAttribute('value');
                    $audioCh_value [$i] = $audioCh_Rep_value;
                }
                else
                {
                    $audioCh_value [$i] = 0;
                }
            }
        }
    }

    $Adapt_urlbase = $rep_url; //Incase of using BaseURL just add all BaseURLs within array containint all presentations
    //Array of each presentation containing all attributes and nodes within Presentations

    $Rep_arr = array('id' => $id, 'codecs' => $codecs, 'mimeType' => $repmimeType, 'width' => $width, 'height' => $height, 'scanType' => $scanType, 'frameRate' => $frameRate,
        'sar' => $sar, 'bandwidth' => $bandwidth, 'SegmentTemplate' => $rep_seg_temp, 'SegmentBase' => $segarray, 'startWithSAP' => $repStartWithSAP, 'profiles' => $repProfiles,
        'ContentProtectionElementCount' => $ContentProtectionElementCountRep, 'presentationTimeOffset' => $Rep_Timeoffset, 'timescale' => $timescale, 'AudioChannelValue' => $audioCh_value, 'indexRange' => $indexRange_RepSet);
    // Array of all adapationsets containing all attributes and nodes including Presentations 

    $Adapt_arr = array('startWithSAP' => $startWithSAP, 'segmentAlignment' => $segmentAlignment, 'subsegmentAlignment' => $subsegmentAlignment, 'bitstreamSwitching' => $bitstreamSwitching,
        'id' => $idadapt, 'scanType' => $scanType, 'mimeType' => $mimeType, 'SegmentTemplate' => $Adapt_seg_temp, 'SegmentBase' => $basearray, 'codecs' => $codecs_AdaptSet, 'width' => $width_AdaptSet, 'height' => $height_AdaptSet, 'sar' => $sar_AdaptSet, 'Representation' => $Rep_arr, 'AudioChannelValue' => $audioCh_Adapt_value, 'indexRange' => $indexRange_AdaptSet, 'ContentProtection' => $ContentProtect_arr,'alignedToSet'=>$AlignedSet_Adapt_value, 'language' => $lang_AdaptSet, 'Role' => $Role_AdaptSet);


    /* $Rep_arr=array('id'=>$id,'codecs'=>$codecs,'width'=>$width,'height'=>$height,'scanType'=>$scanType,'frameRate'=>$frameRate,
      'sar'=>$sar,'bandwidth'=>$bandwidth,'SegmentTemplate'=>$rep_seg_temp, 'startWithSAP'=>$repStartWithSAP, 'profiles'=>$repProfiles,
      'ContentProtectionElementCount'=>$ContentProtectionElementCountRep,'presentationTimeOffset'=>$Rep_Timeoffset,'timescale'=>$timescale,'AudioChannelValue'=>$audioCh_value);
      // Array of all adapationsets containing all attributes and nodes including Presentations

      $Adapt_arr=array('startWithSAP'=>$startWithSAP,'segmentAlignment'=>$segmentAlignment,'subsegmentAlignment'=>$subsegmentAlignment,'bitstreamSwitching'=>$bitstreamSwitching, 'id'=>$idadapt,'scanType'=>$scanType,'mimeType'=>$mimeType,'SegmentTemplate'=>$Adapt_seg_temp,'codecs'=>$codecs_AdaptSet,'width'=>$width_AdaptSet,'height'=>$height_AdaptSet,'Representation'=>$Rep_arr,'AudioChannelValue'=>$audioCh_Adapt_value);
     */
}

/**

  This function process all attributes for all segmentTemplates

 * */
function processTemplate($segmentTemp)
{
    global $init_flag;
    $timelineseg = $segmentTemp->getElementsByTagName('SegmentTimeline'); //Check if segmentTemplate contains node segmentTimeline

    if ($timelineseg->length > 0)// If segmentTimeline exist...process SegmentTimeline
        $SegmentTimeline = processtimeline($timelineseg);
    else
        $SegmentTimeline = 0;

    //Get Some attributes in SegmentTemplate
    $timescale = $segmentTemp->getAttribute("timescale");
    $duration = $segmentTemp->getAttribute("duration");
    $startNumber = $segmentTemp->getAttribute("startNumber");
    $media = $segmentTemp->getAttribute("media");
    $initialization = $segmentTemp->getAttribute("initialization");
    $presentationTimeOffset = $segmentTemp->getAttribute("presentationTimeOffset");
    if (isset($initialization))
    {
        $init_flag = true;
        $_SESSION['init_flag'] = $init_flag;
    }
    $seg_array = array();
    $segmentbase = $segmentTemp->getElementsByTagName('SegmentBase'); //Process SegmentBase if it exist
    if ($segmentbase->length > 0)
    {
        $base = $segmentbase->item(0);

        $basearray = processSegmentBase($base);

        if (!empty($basearray[0]))
            $presentationTimeOffset = $basearray[0];
        if (!empty($basearray[1]))
            $timescale = $basearray[1];
        if (!empty($basearray[2]))
            $indexRange = $basearray[2]; //return array contains indexRange
    }
    //Add all nodes and attributes to array of segment Template
    $seg_array = array('duration' => $duration, 'startNumber' => $startNumber, 'media' => $media,
        'initialization' => $initialization, 'timescale' => $timescale, 'presentationTimeOffset' => $presentationTimeOffset, 'SegmentTimeline' => $SegmentTimeline, 'indexRange' => $indexRange);

    return $seg_array;
}

function processtimeline($timelinearray)
{
    //Get both t S R d from segmentTimeline and start processing them
    $Sarray = array();
    $timetemp = $timelinearray->item(0);
    $stag = $timetemp->getElementsByTagName('S');
    for ($i = 0; $i < $stag->length; $i++)
    {
        $tempstag = $stag->item($i);
        $t = $tempstag->getAttribute('t');
        if (empty($t))
            $t = 0;
        $d = $tempstag->getAttribute('d');

        $r = $tempstag->getAttribute('r');
        if (empty($r))
            $r = 0;

        $Satt = array($t, $d, $r);
        $Sarray[] = $Satt;
    }
    //print_r2($Sarray);
    return $Sarray;
}

//Process Segment Base to get timescale and presentationTimeeOffset if they exist
function processSegmentBase($basedom)
{
    $basearray = array();
    if ($basedom->hasAttribute('presentationTimeOffset'))
        $basearray[0] = $basedom->getAttribute('presentationTimeOffset');

    if ($basedom->hasAttribute('timescale'))
        $basearray[1] = $basedom->getAttribute('timescale');

    if ($basedom->hasAttribute('indexRange'))
        $basearray[2] = $basedom->getAttribute('indexRange');

    return $basearray;
}

//timeparsing function convert the time format specified in mpd into absolute seconds example :PT1H2M4.00S>>  3724 second
function timeparsing($mediaPresentationDuration)
{
    $y = str_replace("P", "", $mediaPresentationDuration); // process mediapersentation duration
    if(strpos($y, 'D') !== false){
        $D = explode("D", $y); //get days

        $y = substr($y, strpos($y, 'D') + 1);
    }
    else
        $D[0] = 0;
    
    $y = str_replace("T", "", $y);
    
    if (strpos($y, 'H') !== false)
    {
        $H = explode("H", $y); //get hours

        $y = substr($y, strpos($y, 'H') + 1);
    }
    else
        $H[0] = 0;

    if (strpos($y, 'M') !== false)
    {

        $M = explode("M", $y); // get minutes
        $y = substr($y, strpos($y, 'M') + 1);
    }
    else
        $M[0] = 0;

    $S = explode("S", $y); // get seconds
    $presentationduration = ($D[0] * 24 * 60 * 60) + ($H[0] * 60 * 60) + ($M[0] * 60) + $S[0]; // calculate durations in seconds

    return $presentationduration;
}

function dynamicnumber($bufferduration, $segmentduration, $AST, $start, $startNumber, $periodarray)
{
    $avgsum = array();
    $sumbandwidth = array();
    for ($k = 0; $k < sizeof($periodarray); $k++)
    {
        $sumbandwidth[] = array_sum($periodarray[$k]['Representation']['bandwidth']);
        $avgsum[] = (array_sum($periodarray[$k]['Representation']['bandwidth']) / sizeof($periodarray[$k]['Representation']['bandwidth']));
    }
    $sumbandwidth = array_sum($sumbandwidth);
    $avgsum = array_sum($avgsum) / sizeof($avgsum);
    $percent = $avgsum / $sumbandwidth;

    $buffercapacity = $bufferduration / $segmentduration; //actual buffer capacity

    date_default_timezone_set("UTC"); //Set default timezone to UTC
    $now = time(); // Get actual time
    $AST = strtotime($AST, $now);
    $LST = $now - ($AST + $start - $segmentduration);

    $LSN = intval($LST / $segmentduration);
    $earlistsegment = (($LSN - $buffercapacity * $percent) > 0) ? ($LSN - $buffercapacity * $percent) : $startNumber;


    $result = array();
    $result[0] = intval($earlistsegment);
    $result[1] = $LSN;

    return $result;
}

function isAbsoluteURL($URL)
{
    $parsedURL = parse_url($URL);
    return $parsedURL['scheme'] && $parsedURL['host'];
}

?>
