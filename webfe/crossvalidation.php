<?php

/*This program is free software: you can redistribute it and/or modify
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

function loadLeafInfoFile($fileName,$PresTimeOffset)
{
	global $Period_arr, $foldername,$locate,$string_info,$foldername;

    $info=array(); //array contains information about given info
    


    $leafInfoFile = fopen($locate.$fileName,"rt"); // open infofile for specific presentation
    if($leafInfoFile == FALSE) // if file doesn't exist
    {
        echo "Error: Leaf info file".$fileName."not found, alignment wont be checked!";//throw error
		error_log( "Error: Leaf info file ".$locate.$fileName." not found, alignment wont be checked!" );		
        return;
    }
    
    fscanf($leafInfoFile,"%lu\n",$accessUnitDurationNonIndexedTrack); 
    fscanf($leafInfoFile,"%u\n",$info['numTracks']); //get NumTracks
    
    $info['leafInfo'] = array(); //leafInfo subarray
    $info['numLeafs'] = array();//number of leafs within representation 
    $info['trackTypeInfo'] = array();
    
    for($i = 0 ; $i < $info['numTracks'] ; $i++)  // passing by all tracks 
    {
        fscanf($leafInfoFile,"%lu %lu\n",$info['trackTypeInfo'][$i]['track_ID'],$info['trackTypeInfo'][$i]['componentSubType']); // save data to $info Multidimensional array
    }
    
    for($i = 0 ; $i < $info['numTracks'] ; $i++)
    {
        fscanf($leafInfoFile,"%u\n",$info['numLeafs'][$i]); // get leaf numbers index

        $info['leafInfo'][$i] = array();
        
        for($j = 0 ; $j < $info['numLeafs'][$i] ; $j++){
		
	
            fscanf($leafInfoFile,"%d %f %f\n",$info['leafInfo'][$i][$j]['firstInSegment'],$info['leafInfo'][$i][$j]['earliestPresentationTime'],$info['leafInfo'][$i][$j]['lastPresentationTime']); // Get earliest presentationtime and last presentationtime for each segment  
         $info['leafInfo'][$i][$j]['earliestPresentationTime']= $info['leafInfo'][$i][$j]['earliestPresentationTime']-$PresTimeOffset;// subtract relative presentationtimeoffset (if exist) from EPT
		  $info['leafInfo'][$i][$j]['lastPresentationTime'] = $info['leafInfo'][$i][$j]['lastPresentationTime']-$PresTimeOffset;//subtract relative presentationtimeoffset (if exist) from LPT
		  }   

   }

    fclose($leafInfoFile);
    
    return $info; 

}

function checkAlignment($leafInfoA,$leafInfoB,$opfile,$segmentAlignment,$subsegmentAlignment,$bitstreamSwitching)
{
    if($leafInfoA['numTracks'] != $leafInfoB['numTracks']) //Check alignment of 2 presentations with it's relative segment
    {
	//if both presentations doesn't have same number of segments(tracks) then it fail in cross validation
        fprintf($opfile,"Error: Number of tracks logged %d for representation with id \"%s\" not equal to the number of indexed tracks %d for representation id \"%s\"\n",$leafInfoA['numTracks'],$leafInfoA['id'],$leafInfoB['numTracks'],$leafInfoB['id']);
        if($bitstreamSwitching=="true") // if set to true user can switch between segments but in case of misalignment it will not be able
            fprintf($opfile,"Bitstream switching not possible, validation failed for bitstreamSwitching\n");
        return;
    }

    if($bitstreamSwitching=="true") 
    {
        for($i = 0 ; $i < $leafInfoA['numTracks'] ; $i++) // check componenetSubType exist in all tracks
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
            if($subsegmentAlignment=="true" || ($leafInfoA['leafInfo'][$i][$j+1]['firstInSegment'] > 0)) //check 1st segment start after 0 time
            {
                if($leafInfoA['leafInfo'][$i][$j+1]['earliestPresentationTime'] <= $leafInfoB['leafInfo'][$i][$j]['lastPresentationTime']) //check if segment $i in presentation A LPT is > EPT of segment $i+1 in presentation B
                {
                    if($leafInfoA['leafInfo'][$i][$j+1]['firstInSegment'] > 0)
                        fprintf($opfile,"Error: Overlapping segment: EPT of leaf %f for leaf number %d for representation id \"%s\" is <= the latest presentation time %f corresponding leaf for representation id \"%s\"\n",$leafInfoA['leafInfo'][$i][$j+1]['earliestPresentationTime'],$j+2,$leafInfoA['id'],$leafInfoB['leafInfo'][$i][$j]['lastPresentationTime'],$leafInfoB['id']);
                    else
                        fprintf($opfile,"Error: Overlapping sub-segment: EPT of leaf %f for leaf number %d for representation id \"%s\" is <= the latest presentation time %f corresponding leaf for representation id \"%s\"\n",$leafInfoA['leafInfo'][$i][$j+1]['earliestPresentationTime'],$j+2,$leafInfoA['id'],$leafInfoB['leafInfo'][$i][$j]['lastPresentationTime'],$leafInfoB['id']);
                }
                
                if($leafInfoB['leafInfo'][$i][$j+1]['earliestPresentationTime'] <= $leafInfoA['leafInfo'][$i][$j]['lastPresentationTime'])
                {
                    if($leafInfoB['leafInfo'][$i][$j+1]['firstInSegment'] > 0)
                        fprintf($opfile,"Error: Overlapping segment: EPT of leaf %f for leaf number %d for representation id \"%s\" is <= the latest presentation time %f corresponding leaf for representation id \"%s\"\n",$leafInfoB['leafInfo'][$i][$j+1]['earliestPresentationTime'],$j+2,$leafInfoB['id'],$leafInfoA['leafInfo'][$i][$j]['lastPresentationTime'],$leafInfoA['id']);
                    else
                        fprintf($opfile,"Error: Overlapping sub-segment: EPT of leaf %f for leaf number %d for representation id \"%s\" is <= the latest presentation time %f corresponding leaf for representation id \"%s\"\n",$leafInfoB['leafInfo'][$i][$j+1]['earliestPresentationTime'],$j+2,$leafInfoB['id'],$leafInfoA['leafInfo'][$i][$j]['lastPresentationTime'],$leafInfoA['id']);
                }                
            }
        }
    }
}


function crossRepresentationProcess()
{
    global $Period_arr, $foldername,$locate,$string_info,$foldername;
    




    for($i = 0; $i<sizeof($Period_arr); $i++)
    {
	$timeoffset = 0;
	$timescale = 1;
        $AdaptationSetAttr = $Period_arr[$i];
       
	   
        if(!empty($AdaptationSetAttr['segmentAlignment'])) //check is segment alignment supported within mpd
            $segmentAlignment = $AdaptationSetAttr['segmentAlignment'];
        else
            $segmentAlignment = "false"; 
            
        if(!empty($AdaptationSetAttr['subsegmentAlignment']))//check sub-segment alignment
            $subsegmentAlignment = $AdaptationSetAttr['subsegmentAlignment'];
        else
            $subsegmentAlignment = "false";
            
        if(!empty($AdaptationSetAttr['bitstreamSwitching'])) // check if bit-stream switching is supported
            $bitstreamSwitching = $AdaptationSetAttr['bitstreamSwitching']; 
        else
            $bitstreamSwitching = "false";
        
        if (!($opfile = fopen("./temp/".$foldername."/Adapt".$i."_infofile.txt", 'w')))// Create a file to contain cross presentation results
        {
            echo "Error opening cross-representation checks file"."./temp/".$foldername."/Adapt".$i."_infofile.txt";


            return;
        }
        
        fprintf($opfile,"Cross representation checks for adaptation set with id \"%s\":\n",$AdaptationSetAttr['id']);

        if($segmentAlignment == "true" || $subsegmentAlignment == "true" || $bitstreamSwitching == "true" ) // in case all arguments supported cross presentation is checked
        {
            $leafInfo=array();
            
            for ($j = 0;$j<sizeof($AdaptationSetAttr['Representation']['bandwidth']);$j++) // looping on all presentations
            { 
                        $timescale = 1;  //set timescale to default value
			$timeoffset = 0; // set offset to default value
                        
			if( !empty($AdaptationSetAttr['SegmentTemplate']['timescale']))// check if timescale exist in mpd in adaptationset level
                            $timescale = $AdaptationSetAttr['SegmentTemplate']['timescale']; 
                        
                        if(!empty($AdaptationSetAttr['SegmentTemplate']['presentationTimeOffset']))// check if presentation time offset exist in adapatationset level 
                            $timeoffset = $AdaptationSetAttr['SegmentTemplate']['presentationTimeOffset'];
			
                        if(!empty($AdaptationSetAttr['Representation']['SegmentTemplate'][$j]['timescale']))//check time scale in presentation level
                            $timescale = $AdaptationSetAttr['Representation']['SegmentTemplate'][$j]['timescale'];
									   									   
			if(!empty($AdaptationSetAttr['Representation']['SegmentTemplate'][$j]['presentationTimeOffset']))//check in segment template presentationtimeoffset in presentation level
                            $timeoffset = $AdaptationSetAttr['Representation']['SegmentTemplate'][$j]['presentationTimeOffset'];
									   									   
                        if(!empty($AdaptationSetAttr['Representation']['presentationTimeOffset'][$j])) //check presentationtimeoffset in representationlevel
                            $timeoffset = $AdaptationSetAttr['Representation']['presentationTimeOffset'][$j];
					  
			$offsetmod = $timeoffset/$timescale; // calculate presentationtimeoffset relative to timescale (in seconds)
									   
			$leafInfo[$j] = loadLeafInfoFile("/Adapt".$i."rep".$j."_infofile.txt",$offsetmod); // load values within infofile

			$leafInfo[$j]['id'] = $AdaptationSetAttr['Representation']['id'][$j]; //get representation ID
            }
            
            for ($j = 0;$j<sizeof($AdaptationSetAttr['Representation']['bandwidth'])-1;$j++) // runs check on every adapationset and representation
            {
                for ($k = $j+1;$k<sizeof($AdaptationSetAttr['Representation']['bandwidth']);$k++)
                {
                    checkAlignment($leafInfo[$j],$leafInfo[$k],$opfile,$segmentAlignment,$subsegmentAlignment,$bitstreamSwitching);// check alignment 
                }              
            }            
        }
        
        fprintf($opfile,"Checks completed.\n");
        fclose($opfile);
	$temp_string = str_replace (array('$Template$'),array("Adapt".$i."_infofile"),$string_info); // place infofile data within HTML string
        file_put_contents($locate.'/'."Adapt".$i."_infofile.html",$temp_string); // convert HTML string to HTML file
			
    }
}



?>
