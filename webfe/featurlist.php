<?php


function featurelist($mpdfile,$duration)
	{
	global $locate;
				 $xml = new DOMDocument();
				 
				 $mpdlist = $xml->createElement("MPDlist");
				 $xml->appendChild($mpdlist);
				 $id=$mpdfile->hasAttribute("id");
				 if($id)
				 $mpdlist->setAttribute("id",'true');
				 
	                 
                  $type = $mpdfile->getAttribute("type");
                     
                       $mpdlist->setAttribute("type",$type);
					   
					   if($mpdfile->hasAttribute("availabilityStartTime"))
					   $availabilityStartTime = $mpdfile->getAttribute("availabilityStartTime");
					 
					 					   if($mpdfile->hasAttribute("availabilityEndTime"))
					 $availabilityEndTime = $mpdfile->getAttribute("availabilityEndTime");
					 date_default_timezone_set("UTC");
					 $check = time();
					   if(isset($availabilityStartTime))
					   {
		                $avalibilitytime = strtotime($availabilityStartTime);

                        if(isset($availabilityEndTime))
						{
						$endtime = strtotime($availabilityEndTime);
						
					     
						if(($endtime-$check)<($endtime-$avalibilitytime) && ($endtime-$check)>-1 )
								$mpdlist->setAttribute("availabilitystartTime",'true');
                        
					/*	if($check<$avalibilitytime)
								 $mpdlist->setAttribute("availabilitystartTime",'false');
								 
								 else 
								 				            $mpdlist->setAttribute("availabilitystartTime","false");


						*/
						$mpdlist->setAttribute("availabilityEndTime",'true');
						 
						 
						}
						else
						{
						  	//$mpdlist->setAttribute("availabilityEndTime",'false');
							
							$showtime = $avalibilitytime +$duration;
							
							
							if ($check>$showtime)
				            $mpdlist->setAttribute("availabilitystartTime","Session has expired");
							if(($showtime-$check)<$duration && ($showtime-$check)>-1 )
			                    $mpdlist->setAttribute("availabilitystartTime",'true');
								/*else
			                      $mpdlist->setAttribute("availabilitystartTime",'false');
								  */	
						}
										   
					   }
					   
				/*	 if(!($mpdfile->hasAttribute("timeShiftBufferDepth")))
 $mpdlist->setAttribute("timeShiftBufferDepth",'false');					 
										 if($type ==="Dynamic"){
										 if(!($mpdfile->hasAttribute("suggestedPresentationDelay")))
										 $mpdlist->setAttribute("suggestedPresentationDelay",'false');
                                           
										   }*/
                                         										  
				                 $perioddom =   $mpdfile->getElementsByTagName('Period');
								  
								  if ($perioddom->length>1)
								   $mpdlist->setAttribute("Period",$perioddom->length);
								   
								   
								
$Metricsdom =   $mpdfile->getElementsByTagName('Metrics');
								   if($Metricsdom->length>=1){
								   
                                      if($Metricsdom->length>1)
								   	  $mpdlist->setAttribute("Metrics",$Metricsdom->length);
								  for($Metindex=0;$metindex<$Metricsdom->length;$Metindex++)
								    Metricsinfo($Metricsdom->item($Metindex),$xml,$mpdlist);
}	 
					 $mpdbase = $mpdfile->getElementsByTagName("BaseURL");
    
	if($mpdbase->length>0){
        $base = $mpdbase->item(0);
        $par = $base->parentNode;
        $name = $par->tagName;
		
       if($name === 'MPD')
        {
             $baseurl = $base->nodeValue;
			 if(strpos($baseurl,'http')!==false)
			 	 $mpdlist->setAttribute("BaseURL","absolute");
				 else
				 $mpdlist->setAttribute("BaseURL","relative");
				
				
			 checkbaseurl ($mpdbase->item(0),$xml,$mpdlist);

        }
		
}
		        $ProgramInformation = $mpdfile->getElementsByTagName('ProgramInformation');
				for($progindex=0;$progindex<$ProgramInformation->length;$progindex++)
                checkproginfo($ProgramInformation->item($progindex),$xml,$mpdlist);		
								
				$periodlistorigin =  $mpdfile->getElementsByTagName('Period');
				$periodlength = $periodlistorigin->length;
				for($i=0;$i<$periodlength;$i++)
                {
                  $perioditem=$periodlistorigin->item($i);
				  $periodlist = $xml->createElement("Period");
				 $mpdlist->appendChild($periodlist);
				  if ($perioditem->hasAttribute('xlink:href'))
				  {
				  if($perioditem->hasAttribute('xlink:actuate'))
				  $periodlist->setAttribute("Xlink",$perioditem->getAttribute('xlink:actuate'));
				  
				  
				  }
/*else
				  				  $periodlist->setAttribute("Xlink",'false');
								  */
   if($type==='dynamic'){
                   if(!($perioditem->hasAttribute("start")))
				   {
				   	   if($i!=0){
				
					  $periodlist->setAttribute("start",'true');

				   if($periodlist->item($i-1)->hasAttribute("duration"))
								   	  $periodlist->setAttribute("duration",'true');

				   
				   }
								  }
}

                        if($perioditem->hasAttribute("bitstreamSwitching"))
						$periodlist->setAttribute("bitstreamSwitching",$perioditem->getAttribute("bitstreamSwitching"));
						
						$segmentbase = $perioditem->getElementsByTagName("SegmentBase");
						$compress = false;
						if($segmentbase->length>0){
						
						for($segbase = 0; $segbase<$perioddom->length;$segbase++)
						{
        $segment = $segmentbase->item($segbase);
        $par = $segment->parentNode;
        $name = $par->tagName;
		if($name =="Period")
		{
		$compress =true;
		$tempseg = $segment;
		}
		else
		$compress = false;
		}
		
		if($compress){
		$periodlist->setAttribute("SegmentBase",'true');
	     
		 checksegmentbase($tempseg,$xml,$periodlist);
		 
		 }
}


$compress = false;
$segmentlist = $perioditem->getElementsByTagName("SegmentList");
						
						if($segmentlist->length>0){
						
						for($seglist = 0; $seglist<$perioddom->length;$seglist++)
						{
        $segmentlistitem = $segmentlist->item($seglist);
        $par = $segmentlistitem->parentNode;
        $name = $par->tagName;
		if($name==="Period")
		$compress = true;
		else
		$compress = false;
		}
		if($compress){
		$periodlist->setAttribute("SegmentList",'true');
		checkseglist ($segmentlistitem,$xml,$periodlist);
		
		}
	
}
	
		$compress = false;
		
		$segmenttemplate = $perioditem->getElementsByTagName("SegmentTemplate");
					
					$segtemplen = 	$segmenttemplate->length;
						if($segmenttemplate->length>0){
						for($segtemp = 0;$segtemp<$segtemplen;$segtemp++)
						{
        $segmenttemplateitem = $segmenttemplate->item($segtemp);
        $par = $segmenttemplateitem->parentNode;
        $name = $par->tagName;
		if($name === "Period")
		{
		$compress = true;
		$tempsegtemp = $segmenttemplateitem;
		}
		else 
		$compress = false;
	
		}
		if($compress)
		{
		$periodlist->setAttribute("Segmenttemplate",'true');
           checksegtemp($tempsegtemp,$xml,$periodlist);		
		   
}
}

				
				$AssetIdentifier = $perioditem->getElementsByTagName("AssetIdentifier");
						if($AssetIdentifier->length>0)
        
		$periodlist->setAttribute("AssetIdentifier",'true');
		
				
				
				$EventStream = $perioditem->getElementsByTagName("EventStream");
						
		if($EventStream->length>1)
				$periodlist->setAttribute("EventStream",$EventStream->length);
				for($Eventindex=0;$Eventindex<$EventStream->length;$Evenetindex++)
				checkeventStream($EventStream->item($Eventindex),$xml,$periodlist);
				
$AdaptationSets = $perioditem->getElementsByTagName("AdaptationSet");
					
		if($AdaptationSets->length>1)
				$periodlist->setAttribute("AdaptationSet",$AdaptationSets->length);
         
		 
		 $Subset = $perioditem->getElementsByTagName("Subset");
						if($Subset->length==1)
        
		$periodlist->setAttribute("Subset",1);
		elseif($Subset->length>1)
				$periodlist->setAttribute("Subset",$Subset->length);
         
		 for($j=0;$j<$AdaptationSets->length;$j++)
{
       $Adaptationsetlist = $xml->createElement("AdaptationSet");
          $periodlist->appendChild($Adaptationsetlist);
		  $Adaptationsetitem = $AdaptationSets->item($j); 
		  	  if ($Adaptationsetitem->hasAttribute('xlink:href'))
				  {
				  if($Adaptationsetitem->hasAttribute('xlink:actuate'))
				  $Adaptationsetlist->setAttribute("Xlink",$Adaptationsetitem->getAttribute('xlink:actuate'));
				  }
                if($Adaptationsetitem->hasAttribute("id"))
				$Adaptationsetlist->setAttribute("id",'true');
              				
								if($Adaptationsetitem->hasAttribute("group"))
				$Adaptationsetlist->setAttribute("group",'true');
              
				if($Adaptationsetitem->hasAttribute("lang"))
				$Adaptationsetlist->setAttribute("lang",'true');
				
				if($Adaptationsetitem->hasAttribute("contentType"))
				$Adaptationsetlist->setAttribute("contentType",'true');
				
				if($Adaptationsetitem->hasAttribute("par"))
				$Adaptationsetlist->setAttribute("par",'true');
				
				if($Adaptationsetitem->hasAttribute("minBandwidth"))
				$Adaptationsetlist->setAttribute("minBandwidth",'true');
				
				if($Adaptationsetitem->hasAttribute("maxBandwidth"))
				$Adaptationsetlist->setAttribute("maxBandwidth",'true');
			
	            if($Adaptationsetitem->hasAttribute("minWidth"))
				$Adaptationsetlist->setAttribute("minWidth",'true');
							   if($Adaptationsetitem->hasAttribute("MaxWidth"))
				$Adaptationsetlist->setAttribute("MaxWidth",'true');
				
				 if($Adaptationsetitem->hasAttribute("MinHeight"))
				$Adaptationsetlist->setAttribute("MinHeight",'true');
				 
				  if($Adaptationsetitem->hasAttribute("MaxHeight"))
				$Adaptationsetlist->setAttribute("MaxHeight",'true');
				
				if($Adaptationsetitem->hasAttribute("minFrameRate"))
				$Adaptationsetlist->setAttribute("minFrameRate",'true');
				
					if($Adaptationsetitem->hasAttribute("maxFrameRate"))
				$Adaptationsetlist->setAttribute("maxFrameRate",'true');
				
				if($Adaptationsetitem->hasAttribute("segmentAlignment"))
								$Adaptationsetlist->setAttribute("segmentAlignment",$Adaptationsetitem->getAttribute("segmentAlignment"));
								
				if($Adaptationsetitem->hasAttribute("subsegmentAlignment"))
								$Adaptationsetlist->setAttribute("subsegmentAlignment",$Adaptationsetitem->getAttribute("subsegmentAlignment"));
																
								if($Adaptationsetitem->hasAttribute("bitstreamSwitching"))
								$Adaptationsetlist->setAttribute("bitstreamSwitching",$Adaptationsetitem->getAttribute("bitstreamSwitching"));
								
								if($Adaptationsetitem->hasAttribute("subsegmentStartsWithSAP"))
								$Adaptationsetlist->setAttribute("subsegmentStartsWithSAP",$Adaptationsetitem->getAttribute("subsegmentStartsWithSAP"));
								
								 $Accessibility = $Adaptationsetitem->getElementsByTagName("Accessibility");
						
		if($Accessibility->length>0)
				$Adaptationsetlist->setAttribute("Accessibility",$Accessibility->length);
         
				
				
				
								 $Role = $Adaptationsetitem->getElementsByTagName("Role");
						if($Role->length>=1)
        
	$Adaptationsetlist->setAttribute("Role",$Role->length);
		
        
								

							 $Rating = $Adaptationsetitem->getElementsByTagName("Rating");
						if($Rating->length>=1)
	$Adaptationsetlist->setAttribute("Rating",$Rating->length);
		
				
							 $ViewPoint = $Adaptationsetitem->getElementsByTagName("ViewPoint");
						if($ViewPoint->length>=1)
    
				$Adaptationsetlist->setAttribute("ViewPoint",$ViewPoint->length);
        
				
							$mpdbase = $Adaptationsetitem->getElementsByTagName("BaseURL");
    
	if($mpdbase->length>0){
        $base = $mpdbase->item(0);
        $par = $base->parentNode;
        $name = $par->tagName;
        if($name === 'Adaptationset')
        {
             if	($mpdbase->length>=1)
			 $Adaptationsetlist->setAttribute("BaseUrl",$mpdbase->length);
			 checkbaseurl ($mpdbase->item(0),$xml,$Adaptationsetlist);
			

			 }
			 }
			 $compress = false;
			 $Segbase = $Adaptationsetitem->getElementsByTagName("SegmentBase");
    
	for($adaseg=0;$adaseg<$Segbase->length;$adaseg++){
        $base = $Segbase->item($adaseg);
        $par = $base->parentNode;
        $name = $par->tagName;
        if($name === 'Adaptationset')
 		 {
		 $compress = true;
         
         }		 
		 else 
		 $compress = false;
                
				}
if($compress ===true)
{

            			 $Adaptationsetlist->setAttribute("SegmentBase",'true');
                            checksegmentbase($base,$xml,$Adaptationsetlist);
}				
		 
				
           
			
			$Seglist = $Adaptationsetitem->getElementsByTagName("SegmentList");
    $compress = false;
	for($seglisco = 0 ; $seglisco<$Seglist->length;$seglisco++){
        $base = $Seglist->item($seglisco);
        $par = $base->parentNode;
        $name = $par->tagName;
        if($name === 'Adaptationset')
 		 {
$compress = true;
$baselis = $base;         
         }
else 
$compress = false;		 
		 
				}
				if($compress)
				{
					 $Adaptationsetlist->setAttribute("SegmentList",'true');
					 checkseglist($baselis,$xml,$Adaptationsetlist);

				}
            			 
				 
						 $SegTemplate = $Adaptationsetitem->getElementsByTagName("SegmentTemplate");
    $compress=false;
	for($segtempcount=0; $segtempcount<$SegTemplate->length;$segtempcount++){
	
        $base = $SegTemplate->item($segtempcount);
        $par = $base->parentNode;
        $name = $par->tagName;

        if($name === 'AdaptationSet')
 		 {
		$compress=true;
              $tempbase = $base;
                   		
         }		 
		 else
		 $compress=false;
				}
				if($compress){
				    	 $Adaptationsetlist->setAttribute("SegmentTemplate",'true');
						 checksegtemp($tempbase,$xml,$Adaptationsetlist);
                      
         	}
			
			$Representation = $Adaptationsetitem->getElementsByTagName("Representation");
              if($Representation->length>=1)
                $Adaptationsetlist->setAttribute("Representation",$Representation->length);	
              	
			   
			       
				   $ContentComponent = $Adaptationsetitem->getElementsByTagName("ContentComponent");
				   for ($k=0;$k<$ContentComponent->length;$k++)
				   {
				   $ContentComponentitem  =  $ContentComponent->item($k);
				   $ContentComponentset  =  $xml->createElement("ContentComponent");
				   $Adaptationsetlist->appendChild($ContentComponentset);
				   if($ContentComponentitem->hasAttribute("id"))
				$ContentComponentset->setAttribute("id",'true');
               
								
								if($ContentComponentitem->hasAttribute("lang"))
				$ContentComponentset->setAttribute("lang",'true');
				
			    if($ContentComponentitem->hasAttribute("contentType"))
				$ContentComponentset->setAttribute("contentType",'true');
				
				if($ContentComponentitem->hasAttribute("par"))
				$ContentComponentset->setAttribute("par",'true');
				
				
				
				 $Accessibility = $ContentComponentitem->getElementsByTagName("Accessibility");
						if($Accessibility->length>=1)
        
	$ContentComponentset->setAttribute("Accessibility",$Accessibility->length);
				
				 $Role = $ContentComponentitem->getElementsByTagName("Role");
						if($Role->length>=1)
        
	$ContentComponentset->setAttribute("Role",$Role->length);
        
				
								
				$Rating = $ContentComponentitem->getElementsByTagName("Rating");
						
						if($Rating->length>=1)
        
	$ContentComponentset->setAttribute("Rating",$Rating->length);

        
				
				
					$ViewPoint = $ContentComponentitem->getElementsByTagName("ViewPoint");
						if($ViewPoint->length>=1)
        
	$ContentComponentset->setAttribute("ViewPoint",$ViewPoint->length);
				   		   
				   }
				   
				   for ($k = 0 ;$k<$Representation->length;$k++)
				   {
				    
					$Representationitem = $Representation->item($k);
					$Representationlist = $xml->createElement("Representation");
					$Adaptationsetlist->appendChild($Representationlist);
					 
					 

					if($Representationitem->hasAttribute("qualityRanking"))
					$Representationlist->setAttribute("qualityRanking",'true');
					
					if($Representationitem->hasAttribute("dependencyId"))
					$Representationlist->setAttribute("dependencyId",'true');
				 	
					if($Representationitem->hasAttribute("mediaStreamStructureId"))
					$Representationlist->setAttribute("mediaStreamStructureId",'true');

                    if($Representationitem->hasAttribute("profiles"))
                     $Representationlist->setAttribute("profiles",'true');	
                     
                    if($Representationitem->hasAttribute("width"))
					                     $Representationlist->setAttribute("width",'true');	
					if($Representationitem->hasAttribute("height"))
		                  $Representationlist->setAttribute("height",'true');	
					if($Representationitem->hasAttribute("sar"))
							                  $Representationlist->setAttribute("sar",'true');
					if($Representationitem->hasAttribute("frameRate"))
											 $Representationlist->setAttribute("frameRate",'true');
					if($Representationitem->hasAttribute("audioSamplingRate"))
					                         $Representationlist->setAttribute("audioSamplingRate",'true');
					if($Representationitem->hasAttribute("mimeType"))
										      $Representationlist->setAttribute("mimeType",'true');
					if($Representationitem->hasAttribute("segmentProfiles"))
											 $Representationlist->setAttribute("segmentProfiles",'true');
					if($Representationitem->hasAttribute("codecs"))
										  $Representationlist->setAttribute("codecs",'true');
				    if($Representationitem->hasAttribute("maximumSAPPeriod"))
										  $Representationlist->setAttribute("maximumSAPPeriod",'true');
				    if($Representationitem->hasAttribute("startWithSAP"))
						                  $Representationlist->setAttribute("startWithSAP",'true');
				    if($Representationitem->hasAttribute("maxPlayoutRate"))
										 $Representationlist->setAttribute("maxPlayoutRate",'true');
					if($Representationitem->hasAttribute("codingDependency"))
					$Representationlist->setAttribute("codingDependency",'true');
										 
					if($Representationitem->hasAttribute("scanType"))
					$Representationlist->setAttribute("scanType",'true');
					
					if($Representationitem->hasAttribute("FramePacking"))
					$Representationlist->setAttribute("FramePacking",'true');
					
					if($Representationitem->hasAttribute("AudioChannelConfiguration"))
					$Representationlist->setAttribute("AudioChannelConfiguration",'true');
					
					if($Representationitem->hasAttribute("ContentProtection"))
					$Representationlist->setAttribute("ContentProtection",'true');
					
					if($Representationitem->hasAttribute("EssentialProperty"))
					$Representationlist->setAttribute("EssentialProperty",'true');
					
					if($Representationitem->hasAttribute("SupplementalProperty"))
					$Representationlist->setAttribute("SupplementalProperty",'true');
					
					if($Representationitem->hasAttribute("InbandEventStream"))
					$Representationlist->setAttribute("InbandEventStream",'true');
										 
				
					$mpdbase = $Representationitem->getElementsByTagName("BaseURL");
					
					
    
	if($mpdbase->length>0){
        $base = $mpdbase->item(0);
        $par = $base->parentNode;
        $name = $par->tagName;
        if($name === 'Representation')
        {
             if	($mpdbase->length>=1)
			 $Representationlist->setAttribute("BaseUrl",$mpdbase->length);
			 checkbaseurl($mpdbase->item(0),$xml,$Representationlist);
			 
			 }
			 }
			 
			
			 $mpdbase = $Representationitem->getElementsByTagName("SubRepresentation");
   
	if($mpdbase->length>0){
	
	
        $base = $mpdbase->item(0);
        $par = $base->parentNode;
        $name = $par->tagName;
        if($name === 'Representation')
        {
             if	($mpdbase->length===1)
			 $Representationlist->setAttribute("SubRepresentation",$mpdbase->length);
			
			 }
			 }
			 
			  for($subi = 0 ;$subi<$mpdbase->length;$subi++)
	{
	
	$subrepitem = $mpdbase->item($subi);
		$SubRepresentationlist = $xml->createElement("SubRepresentation");
		$Representationlist->appendChild($SubRepresentationlist);
         if($subrepitem->hasAttribute("level"))
		 $SubRepresentationlist->setAttribute("level","true");
		 
		 if($subrepitem->hasAttribute("dependencyLevel"))
		 $SubRepresentationlist->setAttribute("dependencyLevel","true");
		 
		  if($subrepitem->hasAttribute("bandwidth"))
		 $SubRepresentationlist->setAttribute("bandwidth","true");
		 
		  if($subrepitem->hasAttribute("contentComponent"))
		 $SubRepresentationlist->setAttribute("contentComponent","true");
		 
		  if($subrepitem->hasAttribute("contains"))
		 $SubRepresentationlist->setAttribute("contains","true");
		 
		  if($subrepitem->hasAttribute("id"))
		 $SubRepresentationlist->setAttribute("id","true");
		 
	}
		
			 
				   $segmentbase = $Representationitem->getElementsByTagName("SegmentBase");
				   if($segmentbase->length>0){
				   $Representationlist->setAttribute("SegmentBase",'true');
							 checksegmentbase($segmentbase->item(0),$xml,$Representationlist);

					
					}
	 
				   $segmentList = $Representationitem->getElementsByTagName("SegmentList");
				   if($segmentList->length>0){
				   $Representationlist->setAttribute("SegmentList",'true');
				   							 checksegmentbase($segmentList->item(0),$xml,$Representationlist);

				   }
				 			   
								  $segmentTemplate = $Representationitem->getElementsByTagName("SegmentTemplate");
				   if($segmentTemplate->length>0){
				   $Representationlist->setAttribute("SegmentTemplate",'true');
				   checksegtemp($segmentTemplate->item(0),$xml,$Representationlist);
				   }
				  
				   }
			}
						
}				
		 $xml->save($locate.'/hello.xml');

	
	}
	
	function checksegmentbase($child,$grand,$parent)
	{
	
	$segbase = $grand->createElement('SegmentBase');
	
	$parent->appendChild($segbase);
	
	if($child->hasAttribute('timescale'))
	$segbase->setAttribute('timescale','true');
	
	if($child->hasAttribute('presentationTimeOffset'))
	$segbase->setAttribute('presentationTimeOffset','true');
	
	if($child->hasAttribute('timeShiftBufferDepth'))
	$segbase->setAttribute('timeShiftBufferDepth','true');
	
	if($child->hasAttribute('indexRange'))
	$segbase->setAttribute('indexRange','true');
	
	if($child->hasAttribute('indexRangeExact'))
	$segbase->setAttribute('indexRangeExact','true');
	
	if($child->hasAttribute('availabilityTimeOffset'))
	$segbase->setAttribute('availabilityTimeOffset','true');
	
	if($child->hasAttribute('availabilityTimeComplete'))
	$segbase->setAttribute('availabilityTimeComplete','true');
	
	$Initialization = $child->getElementsByTagName('Initialization');
	
	if($Initialization->length >0)
      	$segbase->setAttribute('Initialization','true');
	
	$RepresentationIndex = $child->getElementsByTagName('RepresentationIndex');
	
	if($RepresentationIndex->length >0)
      	$segbase->setAttribute('RepresentationIndex','true');
	
	}
	
	function checkseglist ($child,$grand,$parent)
	{
	$seglist = $grand->createElement('SegmentList');
	
	$parent->appendChild($seglist);
	
	if ($child->hasAttribute('xlink:href'))
				  {
				  if($child->hasAttribute('xlink:actuate'))
				  $seglist->setAttribute("Xlink",$child->getAttribute('xlink:actuate'));
				
				  }
				 $SegmentURL = $child->getElementsByTagName('SegmentURL');
	
	if($SegmentURL->length >0)
      	$seglist->setAttribute('SegmentURL',$SegmentURL->length);
		
	
	}
	
	function checksegtemp($child,$grand,$parent)
	{
$segtemp = $grand->createElement('SegmentTemplate');

$parent->appendChild($segtemp);
if($child->hasAttribute('media'))
$segtemp->setAttribute('media','true');

if($child->hasAttribute('index'))
$segtemp->setAttribute('index','true');

if($child->hasAttribute('initialization'))
$segtemp->setAttribute('initialization','true');

if($child->hasAttribute('bitstreamSwitching'))
$segtemp->setAttribute('bitstreamSwitching','true');

$segtimeline = $child->getElementsByTagName('SegmentTimeline');

  for($segcount=0;$segcount<$segtimeline->length;$segcount++)
  {
  $segtimelineitem = $grand->createElement('SegmentTimeline');
  $segtemp->appendChild($segtimelineitem);
  $timelineitem=$segtimeline->item($segcount);
  $S = $timelineitem->getElementsByTagName('S');

  
  if($S->length>0){
      $segtimelineitem->setAttribute('S',$S->length);
  for($Scount=0;$Scount<$S->length;$Scount++){
  $Sitem = $grand->createElement('S');
  $segtimelineitem->appendChild($Sitem);
  $Slist = $S->item($Scount);
  if($Slist->hasAttribute('t'))
  $Sitem->setAttribute('t','true');
  
  if($Slist->hasAttribute('d'))
  $Sitem->setAttribute('d','true');
  
  if($Slist->hasAttribute('r'))
  $Sitem->setAttribute('r','true');
  }
  }
  
  }
	}
	
	function checkbaseurl ($child,$grand,$parent)
	{
$BaseURL = $grand->createElement('BaseURL');

$parent->appendChild($BaseURL);	
if($child->hasAttribute('serviceLocation'))
$BaseURL->setAttribute('serviceLocation','true');

if($child->hasAttribute('byteRange'))
$BaseURL->setAttribute('byteRange','true');

if($child->hasAttribute('availabilityTimeOffset'))
$BaseURL->setAttribute('availabilityTimeOffset','true');

if($child->hasAttribute('availabilityTimeComplete'))
$BaseURL->setAttribute('availabilityTimeComplete','true');


	}
	
	function checkproginfo ($child,$grand,$parent)
	{
	$proginfo = $grand->createElement('ProgramInformation');
	
	$parent->appendChild($proginfo);
	
    if($child->hasAttribute('lang'))
	$proginfo->setAttribute('lang','true');
	
	if($child->hasAttribute('moreInformationURL'))
	$proginfo->setAttribute('moreInformationURL','true');
	
	$Title=$child->getElementsByTagName('Title');
	
	if($Title->length>1)
	$proginfo->setAttribute('Title',$Title->length);
	
	$Source=$child->getElementsByTagName('Source');
	if($Source->length>1)
	$proginfo->setAttribute('Source',$Source->length);
	
	$Copyright=$child->getElementsByTagName('Copyright');
	if($Copyright->length>1)
	$proginfo->setAttribute('Copyright',$Copyright->length);
	}

	function Metricsinfo($child,$grand,$parent)
	{
	$Metricsinfo = $grand->createElement('Metrics');
	
	$parent->appendChild($Metricsinfo);
	
	if($child->hasAttribute('metrics'))
	$Metricsinfo->setAttribute('metrics','true');
	
	$Range=$child->getElementsByTagName('Range');
	if($Range->length>0)
	$Metricsinfo->setAttribute('Range',$Range->length);
	
		if($child->hasAttribute('starttime'))
	$Metricsinfo->setAttribute('starttime','true');
	
	
		if($child->hasAttribute('duration'))
	$Metricsinfo->setAttribute('duration','true');
	
	$Reporting=$child->getElementsByTagName('Reporting');
	if($Reporting->length>0)
	$Metricsinfo->setAttribute('Reporting',$Reporting->length);
	
	}
	
	function checkeventStream($child,$grand,$parent)
	{
	$EventStream = $grand->createElement('EventStream');
	
	$parent->appendChild($EventStream);
	
	if($child->hasAttribute('xlink:href'))
	$EventStream->setAttribute('xlink:href','true');
	
	if($child->hasAttribute('xlink:actuate'))
	$EventStream->setAttribute('xlink:actuate','true');
		
		if($child->hasAttribute('schemeIdUri'))
	$EventStream->setAttribute('schemeIdUri','true');	
	
	if($child->hasAttribute('value'))
	$EventStream->setAttribute('value','true');	
	
		if($child->hasAttribute('timescale'))
	$EventStream->setAttribute('timescale','true');	
	
		if($child->hasAttribute('presentationTime'))
	$EventStream->setAttribute('presentationTime','true');
	
if($child->hasAttribute('duration'))
	$EventStream->setAttribute('duration','true');	

if($child->hasAttribute('id'))
	$EventStream->setAttribute('id','true');	
	
	
	}
	?>