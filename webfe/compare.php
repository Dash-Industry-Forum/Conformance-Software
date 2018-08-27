<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// All the boxes and related attributes to be checked for CMAF Table 11
$array = array("ftyp" => array("majorbrand", "version", "compatible_brands"),
               "mvhd" => array("version", "flags", "timeScale", "duration", "nextTrackID"),
               "tkhd" => array("version", "flags", "trackID", "duration", "volume"),
               "elst" => array("version", "flags", "entryCount"),
               "mdhd" => array("version", "flags", "timescale", "duration", "language"),
               "hdlr" => array("version", "flags", "handler_type", "name"),
               "vmhd" => array("version", "flags"),
               "smhd" => array("version", "flags"),
               "dref" => array("version", "flags", "entryCount"),
               "vide_sampledescription" => array("sdType"),
               "soun_sampledescription" => array("sdType"),
               "hint_sampledescription" => array("sdType"),
               "sdsm_sampledescription" => array("sdType"),
               "odsm_sampledescription" => array("sdType"),
               "stts" => array("version", "flags", "entryCount"),
               "stsc" => array("version", "flags", "entryCount"),
               "stsz" => array("version", "flags", "sampleSize", "entryCount"),
               "stco" => array("version", "flags", "entryCount"),
               "sgpd" => array("version", "flags", "groupingType", "entryCount"),
               "mehd" => array("version", "flags", "fragmentDuration"),
               "trex" => array("version", "flags", "trackID", "sampleDescriptionIndex", "sampleDuration", "sampleSize", "sampleFlags"),
               "pssh" => array("version", "flags", "systemID", "dataSize"),
               "tenc" => array("version", "flags", "default_IsEncrypted", "default_IV_size", "default_KID"),
               "cprt" => array("version", "flags", "language", "notice"),
               "kind" => array("schemeURI", "value"),
               "elng" => array("extended_languages"),
               "sinf" => array(),
               "schi" => array("comment"),
               "schm" => array("scheme", "version", "location"),
               "frma" => array("original_format"));
               
$cfhd_SwSetFound=0;
$caac_SwSetFound=0;
$encryptedSwSetFound=0;

function loadFile($filename){
    $xml = new XMLReader;
    $xml->open($filename);
    return $xml;
}

function createString(){
    global $array;
    $keys = array_keys($array);
    $cnt = count($keys);
    
    $str = '<compInfo>';
    for($i=0; $i<$cnt; $i++)
        $str .= '<' . $keys[$i] . '></' . $keys[$i] . '>';
    $str .= '</compInfo>';
    
    return $str;
}

function getIds($xml_atom){
    $str = $xml_atom->getAttribute("comparedIds");
    $part = explode(" ", $str);
    $firstId = explode("=", $part[0]);
    $secondId = explode("=", substr($part[1], 0, strlen($part[1])-1));
    
    return array($firstId[1], $secondId[1]);
}

function checkRepresentationsConformance(){
    global $locate, $Period_arr, $string_info;
    
    $found = false;
    for($i=0; $i<sizeof($Period_arr); $i++){
        $loc = $locate . '/Adapt' . $i . '/comparisonResults/';
        
        $filecount = 0;
        $files = glob($loc . "*.xml");
        if($files)
            $filecount = count($files);
        
        if(!($opfile = fopen($locate."/Adapt".$i."_compInfo.txt", 'a'))){
            echo "Error opening/creating compared representations' conformance check file: "."./Adapt".$i."_compInfo.txt";
            return;
        }
        
        if(!file_exists($loc))
            fprintf ($opfile, "Tried to retrieve data from a location that does not exist. \n (Possible cause: Representations are not valid and no file/directory for box info is created.)");
        else if($filecount == 0)                     //if no file exists in the directory, nothing to check
            fprintf($opfile, "**No identical box checks possible between Tracks in Adaptationset/Switching Set ".($i+1)."\n");
        else{                                   //if file(s) do(es) exist, then start checking
            for($j=0; $j<$filecount; $j++){         //iterate over all files
                $info_str = file_get_contents($locate."/Adapt".$i."/infofile".$j.".txt");
                
                $filename = $files[$j];
                $first = true;
                
                $xml = loadFile($filename);
                while($xml->read()){                //if any attribute in the xml file contains "No", then this will be considered as an error
                    if($first){                     //obtain the rep ids in the xml file. (info for $opfile)
                        $ids = getIds($xml);
                        $first = false;
                    }
                    $atom_name = $xml->name;
                    $att_count = ($xml->hasAttributes) ? $xml->attributeCount : 0;
                
                    if($att_count > 0){
                        $xml->moveToFirstAttribute();
                        for($k=0; $k<$att_count; $k++){
                            if(in_array("No", explode(" ", $xml->value))){
                                if($atom_name == "elst" && strpos($info_str, 'elst: do not care') !== FALSE)
                                    continue;
                                if($atom_name == "mdhd" && strpos($info_str, 'mdhd: do not care') !== FALSE)
                                    continue;
                                if($atom_name == "ftyp" && strpos($info_str, 'ftyp: do not care') !== FALSE)
                                    continue;
                                else{
                                    fprintf ($opfile, "**'CMAF check violated: Section 7.3.4- CMAF header parameters SHALL NOT differ between CMAF tracks, except as allowed in Table 11', but ".$xml->name.' in the box: '.$atom_name." is different between Rep. $ids[0] and Rep. $ids[1] in Switching Set " . (string) ($i+1) . " \n\n");
                                    $found = true;
                                }
                            }
                            $xml->moveToNextAttribute();
                        }
                        $xml->moveToElement();
                    }
                }
            
                if(!$found){                        //otherwise this comparison conforms to specifications 
                    $found = false;
                }
            }
        }
        
        //fprintf($opfile,"\nChecks completed.\n");
        fclose($opfile);
        $temp_string = str_replace (array('$Template$'),array("Adapt".$i."_compInfo"),$string_info);
        file_put_contents($locate.'/'."Adapt".$i."_compInfo.html",$temp_string);
    }
}

function validAtomNameAndType($atom){
    $atom_name = $atom->name;
    $atom_type = $atom->nodeType;
    if(strpos($atom_name, '#') === false && !empty($atom_name) && $atom_type == XMLReader::ELEMENT)
            return true;
    return false;
}
 function validateFileBrands($xml_att_value,$xml_comp_att_value,$infofile)
 {
     $brands1=(string)$xml_att_value;
     $brands2=(string)$xml_comp_att_value;
     $videoCmaf1=strpos($brands1,"cfsd") || strpos($brands1,"cfhd") || strpos($brands1,"chdf");
     $videoCmaf2=strpos($brands2,"cfsd") || strpos($brands2,"cfhd") || strpos($brands2,"chdf");
     $audioCmaf1=strpos($brands1,"caac") || strpos($brands1,"caaa");
     $audioCmaf2=strpos($brands2,"caac") || strpos($brands2,"caaa");
     
     if($audioCmaf1 == FALSE && (($videoCmaf1!==FALSE && $videoCmaf2 == FALSE ) || ($videoCmaf2!==FALSE && $videoCmaf1 == FALSE )))
         fprintf ($infofile, "ftyp: do care\n");//When media profile brands are not subset of one another.
     else
         fprintf ($infofile, "ftyp: do not care\n");
     
 }

function compare($xml, $xml_comp, $compXML, $ind){
    global $array, $locate, $count1;
    
    if(!($infofile = fopen($locate."/Adapt".$count1."/infofile".$ind.".txt", 'w'))){
            echo "Error opening/creating file containing important info to be checked";
            return;
    }
    
    $tkhd_cnt = 0; $att_val1; $att_val2;
    while($xml->read()){        //read both files and compare the desired attribute values
        $xml_comp->read();
        if(validAtomNameAndType($xml) && validAtomNameAndType($xml_comp)){
            $xml_atom_name = $xml->name;
            $xml_comp_atom_name = $xml_comp->name;
            
            if($xml_atom_name == $xml_comp_atom_name){
                
                if(array_key_exists($xml_atom_name, $array)){
                    
                    // for checkRepresentationsConformance (implementation acc. to CMAF Table 11)
                    if($xml_atom_name == 'tkhd'){
                        $tkhd_cnt++;
                    }
                    
                    $cnt = count($array[$xml_atom_name]);
                    
                    for($i=0; $i<$cnt; $i++){
                        $att_name = $array[$xml_atom_name][$i];
                        $xml_att_value = $xml->getAttribute($att_name);
                        $xml_comp_att_value = $xml_comp->getAttribute($att_name);
                        
                        // for checkRepresentationsConformance (implementation acc. to CMAF Table 6)
                        if($xml_atom_name == 'mdhd' && $att_name == 'timescale'){
                            $att_val1 = $xml_att_value;
                            $att_val2 = $xml_comp_att_value;
                        }
                        if($xml_atom_name == "hdlr" && $att_name == "handler_type"){
                            if($xml_att_value == "soun" && $xml_comp_att_value == "soun"){
                                if(doubleval($att_val1) % 2 == 0 && doubleval($att_val2) % 2 == 0)
                                    fprintf($infofile, "mdhd: do not care\n");
                                else
                                    fprintf($infofile, "mdhd: do care\n");
                            }
                            else
                                fprintf($infofile, "mdhd: do care\n");
                        }
                        //For comparing file brands with media profile brands
                        if($xml_atom_name == 'ftyp' && $att_name == 'compatible_brands'){
                            validateFileBrands($xml_att_value,$xml_comp_att_value,$infofile);
                        }
                        
                        if($xml_att_value == $xml_comp_att_value){//$xml_att_value != NULL && $xml_comp_att_value != NULL && $xml_att_value == $xml_comp_att_value){
                            if(isset($compXML->$xml_atom_name->attributes()[$att_name]))
                                $compXML->$xml_atom_name->attributes()->$att_name = ((string) $compXML->$xml_atom_name->attributes()->$att_name) . ' Yes';
                            else
                                $compXML->$xml_atom_name->addAttribute($att_name, 'Yes');
                            
                            // Check for 'sinf' box
                            if($xml_atom_name == 'frma'){
                                $box = 'sinf'; $box_att = 'frma';
                                if(isset($compXML->$box->attributes()[$box_att]))
                                    $compXML->$box->attributes()->$box_att = ((string) $compXML->$box->attributes()->$box_att) . ' Yes';
                                else
                                    $compXML->$box->addAttribute($box_att, 'Yes');
                            }
                            else if($xml_atom_name == 'schm'){
                                $box = 'sinf'; $box_att = 'schm';
                                if(isset($compXML->$box->attributes()[$box_att]))
                                    $compXML->$box->attributes()->$box_att = ((string) $compXML->$box->attributes()->$box_att) . ' Yes';
                                else
                                    $compXML->$box->addAttribute($box_att, 'Yes');
                            }
                            else if($xml_atom_name == 'schi'){
                                $box = 'sinf'; $box_att = 'schi';
                                if(isset($compXML->$box->attributes()[$box_att]))
                                    $compXML->$box->attributes()->$box_att = ((string) $compXML->$box->attributes()->$box_att) . ' Yes';
                                else
                                    $compXML->$box->addAttribute($box_att, 'Yes');
                            }
                        }
                        else{
                            if(isset($compXML->$xml_atom_name->attributes()[$att_name]))
                                $compXML->$xml_atom_name->attributes()->$att_name = ((string) $compXML->$xml_atom_name->attributes()->$att_name) . ' No';
                            else
                                $compXML->$xml_atom_name->addAttribute($att_name, 'No');
                            
                            // Check for 'sinf' box
                            if($xml_atom_name == 'frma'){
                                $box = 'sinf'; $box_att = 'frma';
                                if(isset($compXML->$box->attributes()[$box_att]))
                                    $compXML->$box->attributes()->$box_att = ((string) $compXML->$box->attributes()->$box_att) . ' No';
                                else
                                    $compXML->$box->addAttribute($box_att, 'No');
                            }
                            else if($xml_atom_name == 'schm'){
                                $box = 'sinf'; $box_att = 'schm';
                                if(isset($compXML->$box->attributes()[$box_att]))
                                    $compXML->$box->attributes()->$box_att = ((string) $compXML->$box->attributes()->$box_att) . ' No';
                                else
                                    $compXML->$box->addAttribute($box_att, 'No');
                            }
                            else if($xml_atom_name == 'schi'){
                                $box = 'sinf'; $box_att = 'schi';
                                if(isset($compXML->$box->attributes()[$box_att]))
                                    $compXML->$box->attributes()->$box_att = ((string) $compXML->$box->attributes()->$box_att) . ' No';
                                else
                                    $compXML->$box->addAttribute($box_att, 'No');
                            }
                        }
                    }
                }
            }
        }
    }
    // for checkRepresentationsConformance (implementation acc. to CMAF Table 11)
    if ($tkhd_cnt > 1)
        fprintf($infofile, "elst: do not care\n");
    else 
        fprintf($infofile, "elst: do care\n");
    
    fclose($infofile);
}

function compareRepresentations(){
    global $locate, $Period_arr, $count1;
    
    $AdaptationSetAttr = $Period_arr[$count1];
    
    $locate1 = $locate . '/Adapt' . $count1 .'/'; 
    $filecount = 0;
    $files = glob($locate1 . "*.xml");
    if($files)
        $filecount = count($files);
    
    $ind = 0;
    for($i=0; $i<$filecount-1; $i++){               //iterate over files
        if($i >= $filecount-1)
            break;
        
        for($j=$i+1; $j<$filecount; $j++){          //iterate over remaining files
            $filename = $files[$i];                 //load file
            $xml = loadFile($filename);
            $id = $AdaptationSetAttr['Representation']['id'][$i];
        
            $filename_comp = $files[$j];            //load file to be compared
            $xml_comp = loadFile($filename_comp);
            $id_comp = $AdaptationSetAttr['Representation']['id'][$j];
            
            $name = explode(".", $filename);        //naming the comparison result xml file 
            $name_comp = explode(".", $filename_comp);
            $parts = explode('/', $name[0]);
            $name_part = $parts[sizeof($parts)-1];
            $parts_comp = explode('/', $name_comp[0]);
            $name_comp_part = $parts_comp[sizeof($parts_comp)-1];
            $path = $locate1 . "comparisonResults/" . $name_part . "_vs_" . $name_comp_part . ".xml";
            
            $str = createString();                  //load the comparison result xml structure
            $compXML = simplexml_load_string($str);
            $compXML->addAttribute('comparedIds', "[rep=".$id." rep=".$id_comp."]");
            
            compare($xml, $xml_comp, $compXML, $ind);     //start comparing
            compareHevc($filename, $filename_comp, $id, $id_comp);
            
            $compXML->asXml($path);                 //save changes
            $ind++;
        }
    }
}

function xmlFileLoad($filename)
{
    $load = simplexml_load_file($filename); // load mpd from url 
    if($load !== FALSE){
        $dom_abs = dom_import_simplexml($load);
        $abs = new DOMDocument('1.0');
        $dom_abs = $abs->importNode($dom_abs, true); //create dom element to contain mpd 
            
        $dom_abs = $abs->appendChild($dom_abs);
        
        $xml_atomlist = $abs->getElementsByTagName('atomlist')->item(0);
        return $xml_atomlist;
    }
    return 0;
}

function getNALArray($hvcC, $type){
    $hvcC_nals = $hvcC->childNodes;
    $nal_len = $hvcC_nals->length;
    
    for($i=0; $i<$nal_len; $i++){
        $nal_unit_arr = $hvcC_nals->item($i);
        if(strpos($nal_unit_arr->nodeName, 'NAL_Unit_Array') !== FALSE){
            $nal_unit_type = $nal_unit_arr->getAttribute('nalUnitType');
            if($nal_unit_type == $type){
                return $nal_unit_arr;
            }
        }
    }
    return NULL;
}

function getNALUnit($nal_array){
    $nodes = $nal_array->childNodes;
    $nal_array_len = $nodes->length;
    
    for($i=0; $i<$nal_array_len; $i++){
        $nal_unit = $nodes->item($i);
        if(strpos($nal_unit->nodeName, 'NALUnit') !== FALSE){
            return $nal_unit;
        }
    }
    return NULL;
}

function compareHevc($filename, $filename_comp, $id, $id_comp){
    global $locate, $count1;
    
    $att_names_sps = array('vui_parameters_present_flag', 'video_signal_type_present_flag', 'colour_description_present_flag',
        'colour_primaries', 'transfer_characteristics', 'matrix_coeffs', 'chroma_loc_info_present_flag',
        'chroma_sample_loc_type_top_field', 'chroma_sample_loc_type_bottom_field', 'neutral_chroma_indication_flag', 
        'sps_extension_present_flag', 'sps_range_extension_flag', 'extended_precision_processing_flag');
    $att_names_sei = array('length', 'zero-bit', 'nuh_layer_id', 'nuh_temporal_id_plus1');
    
    
    $opfile = fopen($locate."/Adapt".$count1."_compInfo.txt", 'a');
    
    $xml = xmlFileLoad($filename);
    $xml_comp = xmlFileLoad($filename_comp);
    
    $xml_hvcC=$xml->getElementsByTagName('hvcC')->item(0);
    $xml_comp_hvcC = $xml_comp->getElementsByTagName('hvcC')->item(0);
    
    $xml_SPS = getNALArray($xml_hvcC, '33');
    $xml_comp_SPS = getNALArray($xml_comp_hvcC, '33');
    if($xml_SPS != NULL && $xml_comp_SPS != NULL){
        $sps_unit = getNALUnit($xml_SPS);
        $sps_unit_comp = getNALUnit($xml_comp_SPS);
        
        foreach ($att_names_sps as $att_name) {
            $nal_unit_att = $sps_unit->getAttribute($att_name);
            $comp_nal_unit_att = $sps_unit_comp->getAttribute($att_name);
            
            if($nal_unit_att != $comp_nal_unit_att)
                fprintf($opfile, "**'CMAF check violated: Section B.2.4- CMAF Switching Sets SHALL be constrained to include identical SPS VUI color mastering and dynamic range information in the first sample entry of every CMAF header in the CMAF switching set to provide consistent initialization and calibration', but $att_name is $nal_unit_att for Rep. $id and $comp_nal_unit_att for Rep. $id_comp.\n");
        }
    }
    elseif(($xml_SPS != NULL && $xml_comp_SPS == NULL) || ($xml_SPS == NULL && $xml_comp_SPS != NULL)){
        fprintf($opfile, "**'CMAF check violated: Section B.2.4- CMAF Switching Sets SHALL be constrained to include identical SPS VUI color mastering and dynamic range information in the first sample entry of every CMAF header in the CMAF switching set to provide consistent initialization and calibration', but Rep. $id and Rep. $id_comp are not symmetric in SPS NAL presence.\n");
    }
    
    $xml_PRESEI = getNALArray($xml_hvcC, '39');
    $xml_comp_PRESEI = getNALArray($xml_comp_hvcC, '39');
    if($xml_PRESEI != NULL && $xml_comp_PRESEI != NULL){
        $presei_unit = getNALUnit($xml_PRESEI);
        $presei_unit_comp = getNALUnit($xml_comp_PRESEI);
        
        foreach ($att_names_sei as $att_name) {
            $nal_unit_att = $presei_unit->getAttribute($att_name);
            $comp_nal_unit_att = $presei_unit_comp->getAttribute($att_name);
            
            if($nal_unit_att != $comp_nal_unit_att)
                fprintf($opfile, "**'CMAF check violated: Section B.2.4- CMAF Switching Sets SHALL be constrained to include identical SEI NALS in the first sample entry of every CMAF header in the CMAF switching set to provide consistent initialization and calibration', but $att_name is $nal_unit_att for Rep. $id and $comp_nal_unit_att for Rep. $id_comp. \n");
            }
    }
    elseif(($xml_PRESEI != NULL && $xml_comp_PRESEI == NULL) || ($xml_PRESEI == NULL && $xml_comp_PRESEI != NULL)){
        fprintf($opfile, "**'CMAF check violated: Section B.2.4- CMAF Switching Sets SHALL be constrained to include identical SPS VUI color mastering and dynamic range information in the first sample entry of every CMAF header in the CMAF switching set to provide consistent initialization and calibration', but Rep. $id and Rep. $id_comp are not symmetric in SEI NAL presence.\n");
    }
    
    $xml_SUFSEI = getNALArray($xml_hvcC, '40');
    $xml_comp_SUFSEI = getNALArray($xml_comp_hvcC, '40');
    if($xml_SUFSEI != NULL && $xml_comp_SUFSEI != NULL){
        $sufsei_unit = getNALUnit($xml_SUFSEI);
        $sufsei_unit_comp = getNALUnit($xml_comp_SUFSEI);
        
        foreach ($att_names_sei as $att_name) {
            $nal_unit_att = $sufsei_unit->getAttribute($att_name);
            $comp_nal_unit_att = $sufsei_unit_comp->getAttribute($att_name);
            
            if($nal_unit_att != $comp_nal_unit_att)
                fprintf($opfile, "**'CMAF check violated: Section B.2.4- CMAF Switching Sets SHALL be constrained to include identical SEI NALS in the first sample entry of every CMAF header in the CMAF switching set to provide consistent initialization and calibration', but $att_name is $nal_unit_att for Rep. $id and $comp_nal_unit_att for Rep. $id_comp. \n");
            }
    }
    elseif(($xml_SUFSEI != NULL && $xml_comp_SUFSEI == NULL) || ($xml_SUFSEI == NULL && $xml_comp_SUFSEI != NULL)){
        fprintf($opfile, "**'CMAF check violated: Section B.2.4- CMAF Switching Sets SHALL be constrained to include identical SPS VUI color mastering and dynamic range information in the first sample entry of every CMAF header in the CMAF switching set to provide consistent initialization and calibration', but Rep. $id and Rep. $id_comp are not symmetric in SEI NAL presence.\n");
    }
    
    fclose($opfile);
}

function checkSwitchingSets(){
    global $locate, $Period_arr;
    
    for($adapt_count=0; $adapt_count<sizeof($Period_arr); $adapt_count++){
        $loc = $locate . '/Adapt' . $adapt_count.'/';
        
        $Adapt=$Period_arr[$adapt_count];
        $filecount = 0;
        $files = glob($loc . "*.xml");
        if($files)
            $filecount = count($files);
        
        if(!($opfile = fopen($locate."/Adapt".$adapt_count."_compInfo.txt", 'a'))){////$locate."/SwitchingSet".$adapt_count."_infofile", 'w'
            //echo "Error opening/creating SwitchingSet conformance check file: "."./SwitchingSet".$adapt_count."_infofile.txt";
            echo "Error opening/creating SwitchingSet conformance check file: "."./Adapt".$adapt_count."_compInfo.txt";
            return;
        }
        
        //if(!file_exists($loc))
        //    fprintf ($opfile, "Tried to retrieve data from a location that does not exist. \n (Possible cause: Representations are not valid and no file/directory for box info is created.)");
        if(file_exists($loc)){///else{                                   //if file(s) do(es) exist, then start checking
            //fprintf($opfile, "**SwitchingSet conformance check for: SwitchingSet (Adaptationset) ".($adapt_count+1).":\n\n");
            
            if($filecount == 0)                     //if no file exists in the directory, nothing to check
                fprintf($opfile, "**'CMAF check violated: Section 7.3.4.1- CMAF Switching Set SHALL contain one or more CMAF Tracks', but found".($filecount)."\n");
       
     
            for($i=0; $i<$filecount-1; $i++){               //iterate over files
        
                for($j=$i+1; $j<$filecount; $j++){          //iterate over remaining files
                    $filename = $files[$i];                 //load file
                    $xml = xmlFileLoad($filename);
        
                    $filename_comp = $files[$j];            //load file to be compared
                    $xml_comp = xmlFileLoad($filename_comp);
                    //Check all Tracks are of same media type.
                    $xml_hdlr=$xml->getElementsByTagName('hdlr')->item(0);
                   
                    $xml_handlerType=$xml_hdlr->getAttribute('handler_type');
                    $id = $Adapt['Representation']['id'][$i];
                    $xml_comp_hdlr=$xml_comp->getElementsByTagName('hdlr')->item(0);
                    $xml_comp_handlerType=$xml_comp_hdlr->getAttribute('handler_type');
                    $id_comp = $Adapt['Representation']['id'][$j];
                    
                    if($xml_handlerType!=$xml_comp_handlerType)
                        fprintf($opfile, "**'CMAF check violated: Section 7.3.4.1- A CMAF Switching Set SHALL contain CMAF Tracks of only one media type', but not matching between Rep". $id." (".$xml_handlerType.") and Rep".$id_comp." (".$xml_comp_handlerType.") \n");
                      
                    //Check Tracks have same number of moofs.
                    $xml_num_moofs=$xml->getElementsByTagName('moof')->length;
                    $xml_comp_num_moofs=$xml_comp->getElementsByTagName('moof')->length;
                    
                    if($xml_num_moofs!=$xml_comp_num_moofs)
                        fprintf($opfile, "**'CMAF check violated: Section 7.3.4.1- All CMAF Tracks in a CMAF Switching Set SHALL contain the same number of CMAF Fragments', but not matching between Rep". $id." (fragments=".$xml_num_moofs.") and Rep".$id_comp." (fragments=".$xml_comp_num_moofs.") \n");
                        
                    //Check all Tracks have same ISOBMFF defined duration.
                    if($xml->getElementsByTagName('mehd')->length >0 && $xml_comp->getElementsByTagName('mehd')->length >0 ){
                        $xml_mehd=$xml->getElementsByTagName('mehd')->item(0);
                        $xml_mehdDuration=$xml_mehd->getAttribute('fragmentDuration');

                        $xml_comp_mehd=$xml_comp->getElementsByTagName('mehd')->item(0);
                        $xml_comp_mehdDuration=$xml_comp_mehd->getAttribute('fragmentDuration');


                        if($xml_mehdDuration!=$xml_comp_mehdDuration)
                            fprintf($opfile, "**'CMAF check violated: Section 7.3.4.1- All CMAF Tracks in a CMAF Switching Set SHALL have the same duration', but not matching between Rep". $id." (duration=".$xml_mehdDuration.") and Rep".$id_comp." (duration=".$xml_comp_mehdDuration.") \n");
                    }
                    else{ //added according to change in FDIS.
                        $xml_lasttfdt=$xml->getElementsByTagName('tfdt')->item($xml_num_moofs-1);
                        $xml_comp_lasttfdt=$xml_comp->getElementsByTagName('tfdt')->item($xml_comp_num_moofs-1);
                        
                        $xml_lastDecodeTime=$xml_lasttfdt->getAttribute('baseMediaDecodeTime');
                        $xml_comp_lastDecodeTime=$xml_comp_lasttfdt->getAttribute('baseMediaDecodeTime');
                        
                        $xml_lasttrun=$xml->getElementsByTagName('trun')->item($xml_num_moofs-1);
                        $xml_comp_lasttrun=$xml_comp->getElementsByTagName('trun')->item($xml_comp_num_moofs-1);
                        
                        $xml_cumSampleDur=$xml_lasttrun->getAttribute('cummulatedSampleDuration');
                        $xml_comp_cumSampleDur=$xml_comp_lasttrun->getAttribute('cummulatedSampleDuration');
                        
                        if($xml_lastDecodeTime+$xml_cumSampleDur != $xml_comp_lastDecodeTime+$xml_comp_cumSampleDur)
                            fprintf($opfile, "**'CMAF check violated: Section 7.3.4.1- All CMAF Tracks in a CMAF Switching Set SHALL have the same duration', but not matching between Rep". $id." (duration=".$xml_lastDecodeTime+$xml_cumSampleDur.") and Rep".$id_comp." (duration=".$xml_comp_lastDecodeTime+$xml_comp_cumSampleDur.") \n");
                    }
                    
                        
                    //Check base decode time of Tracks.
                    $xml_tfdt=$xml->getElementsByTagName('tfdt');    
                    $xml_baseDecodeTime=$xml_tfdt->item(0)->getAttribute('baseMediaDecodeTime');
                    $xml_comp_tfdt=$xml_comp->getElementsByTagName('tfdt');    
                    $xml_comp_baseDecodeTime=$xml_comp_tfdt->item(0)->getAttribute('baseMediaDecodeTime');
                    
                    if($xml_baseDecodeTime!=$xml_comp_baseDecodeTime)
                         fprintf($opfile, "**'CMAF check violated: Section 7.3.4.1- All CMAF tracks in a CMAF Switching Set SHALL have the same value of baseMediaDecodeTime in the 1st CMAF fragment's tfdt box, measured from the same timeline origin', but not matching between Rep". $id." (decode time=".$xml_baseDecodeTime.") and Rep".$id_comp." (decode time=".$xml_comp_baseDecodeTime.") \n");
                         
                   //Check for Fragments with same decode time.
                   for($y=0; $y<$xml_num_moofs;$y++){
                    $xml_baseDecodeTime=$xml_tfdt->item($y)->getAttribute('baseMediaDecodeTime');
                    for($z=0;$z<$xml_comp_num_moofs; $z++){
                        
                        $xml_comp_baseDecodeTime=$xml_comp_tfdt->item($z)->getAttribute('baseMediaDecodeTime');
                        if($xml_baseDecodeTime==$xml_comp_baseDecodeTime)
                            break;
                        elseif($z==$xml_comp_num_moofs-1)
                            fprintf($opfile, "**'CMAF check violated: Section 7.3.4.1- For any CMAF Fragment in one CMAF Track in a CMAF Switching Set there SHALL be a CMAF Fragment with same decode time in all other CMAF Tracks', but not found for Rep ".$id." Fragment ".($y+1)." in Rep ".$id_comp."\n");
                    }
                   }
                   
                   //Check tenc encryption parameters.
                   /*if($xml->getElementsByTagName('tenc')->length >0 && $xml_comp->getElementsByTagName('tenc')->length >0){
                    $xml_tenc=$xml->getElementsByTagName('tenc');    
                    $xml_KID=$xml_tenc->item(0)->getAttribute('default_KID');
                    $xml_comp_tenc=$xml_comp->getElementsByTagName('tenc');    
                    $xml_comp_KID=$xml_comp_tenc->item(0)->getAttribute('default_KID');
                    
                    $xml_IVSize=$xml_tenc->item(0)->getAttribute('default_IV_size');
                    $xml_comp_IVSize=$xml_comp_tenc->item(0)->getAttribute('default_IV_size');
                    
                    if($xml_KID!=$xml_comp_KID)
                        fprintf($opfile, "**'CMAF check violated: Section 7.3.3- CMAF Header contained default_KID SHALL be identical for all CMAF Tracks in a Switching Set', but not found for Rep ".$id." (KID=".$xml_KID.") and Rep ".$id_comp." (KID=".$xml_comp_KID.") \n");
                        
                    if($xml_IVSize!=$xml_comp_IVSize)
                        fprintf($opfile, "**'CMAF check violated: Section 7.3.3- CMAF Header contained default_IV_size SHALL be identical for all CMAF Tracks in a Switching Set', but not found for Rep ".$id." (IV_size=".$xml_IVSize.") and Rep ".$id_comp." (IV_size=".$xml_comp_IVSize.") \n");
                   }*/
                   
                   //Check new presentation time check from FDIS on SwSet
                   $xml_hdlr=$xml->getElementsByTagName('hdlr')->item(0);
                   $xml_handlerType=$xml_hdlr->getAttribute('handler_type');
                   $xml_trun=$xml->getElementsByTagName('trun')->item(0);
                   $xml_comp_trun=$xml_comp->getElementsByTagName('trun')->item(0);
                   $xml_earlyCompTime=$xml_trun->getAttribute('earliestCompositionTime');
                   $xml_comp_earlyCompTime=$xml_comp_trun->getAttribute('earliestCompositionTime');
                     
                   if($xml_handlerType=='vide') 
                   { 
                     if($xml_earlyCompTime!=$xml_comp_earlyCompTime)
                        fprintf($opfile, "**'CMAF check violated: Section 7.3.4.1- The presentation time of earliest media sample of the earliest CMAF fragment in each CMAF track shall be equal', but unequal presentation-times found between Rep ".$id." and Rep ".$id_comp." \n");
                   }
                   else if($xml_handlerType=='soun')
                   {
                        $xml_elst=$xml->getElementsByTagName('elstEntry');
                        $xml_comp_elst=$xml_comp->getElementsByTagName('elstEntry');
                        $mediaTime=0;
                        if($xml_elst->length>0 ){
                        $mediaTime=$xml_elst->item(0)->getAttribute('mediaTime');
                        }
                        $mediaTime_comp=0;
                        if($xml_comp_elst->length>0 ){
                        $mediaTime_comp=$xml_comp_elst->item(0)->getAttribute('mediaTime');
                        }
                        if($xml_earlyCompTime+$mediaTime != $xml_comp_earlyCompTime+$mediaTime_comp)
                            fprintf($opfile, "**'CMAF check violated: Section 7.3.4.1- The presentation time of earliest media sample of the earliest CMAF fragment in each CMAF track shall be equal', but unequal presentation-times found between Rep ".$id." and Rep ".$id_comp." \n");
                        
                    }
                   //
                   
                   $mediaProfileError=checkMediaProfiles($xml, $xml_comp,$xml_handlerType,$xml_comp_handlerType);//Check media profile conformance of Tracks in a Switching Set.
                   if($mediaProfileError)
                        fprintf($opfile, "**'CMAF check violated: Section 7.3.4.1- All CMAF Tracks in a CMAF Switching Set SHALL conform to one CMAF Media Profile', but not conforming for Rep ".$id." and Rep ".$id_comp." \n");
                    
                }
            }
            //Check General constraints on CMAF Tracks . Section 7.3.1.2
            checkCMAFTracks($files,$filecount,$opfile,$Adapt);
            
        }
        fprintf($opfile, "\n-----Conformance checks completed----- ");
        fclose($opfile);
    }
    //Check CMAF Presentation profile conformance
    checkCMAFPresentation();
    //Check CMAF Selection Set conformance
    checkSelectionSet();
    checkAlignedSwitchingSets();
}

function checkCMAFTracks($files,$filecount,$opfile,$Adapt){
    global $profiles;
    
    for($i=0; $i<$filecount; $i++){     
        $errorInTrack=0;
        $filename = $files[$i];                 //load file
        $xml = xmlFileLoad($filename);
        $id = $Adapt['Representation']['id'][$i];
        $xml_moof=$xml->getElementsByTagName('moof');
        $xml_num_moofs=$xml_moof->length;
        $xml_tfhd=$xml->getElementsByTagName('tfhd');
        $xml_trun=$xml->getElementsByTagName('trun');
        $xml_tfdt=$xml->getElementsByTagName('tfdt');
        
        
       /* if($xml_trun[0]->getAttribute('version') ==1)
        {
            $firstSampleCompTime=$xml_trun[0]->getAttribute('earliestCompositionTime');
            $firstSampleDecTime=$xml_tfdt[0]->getAttribute('baseMediaDecodeTime');
            if($firstSampleCompTime!=$firstSampleDecTime)
                fprintf($opfile, "**'CMAF check violated: Section 7.5.16- For 'trun' version 1, the composition time of 1st presented sample in a CMAF Segment SHALL be same as 1st Sample decode time, but not found in Rep ".$id." \n");
        
        }*/
        
        // 'trun' version check for CMAF video tracks
        $adapt_mime_type = $Adapt['mimeType'];
        $rep_mime_type = $Adapt['Representation']['mimeType'][$i];
        if(strpos($rep_mime_type, 'video') !== FALSE || strpos($adapt_mime_type, 'video') !== FALSE){
            if(strpos($profiles, 'urn:mpeg:dash:profile:isoff-live:2011') !== FALSE){
                for($j=0;$j<$xml_num_moofs;$j++){
                    $trun_version = $xml_trun->item($j)->getAttribute('version');
                    if($trun_version != "1")
                        fprintf($opfile, "**'CMAF check violated: Section 7.5.17- Version 1 SHALL be used for video CMAF tracks, except in case of a video CMAF track file', but " . $trun_version . " found for Rep ".$id." Track ".($j+1)."\n");
                }
            }
        }
        
        // 'subs' presence check for TTML image subtitle track with media profile 'im1i'
        $rep_codec_type = $Adapt['Representation']['codec'][$i];
        if(strpos($rep_codec_type, 'im1i') !== FALSE){
            for($j=0;$j<$xml_num_moofs;$j++){
                $temp_moof = $xml_moof[$j];
                $xml_subs = $temp_moof->getElementsByTagName('subs');
                if($xml_subs->length == 0)
                    fprintf($opfile, "**'CMAF check violated: Section 7.5.20- Each CMAF fragment in a TTML image subtitle track of CMAF media profile 'im1i' SHALL contain a SubSampleInformationBox in the TrackFragmentBox, but " . $xml_subs->length . " found for Rep ".$id." Fragment ".($j+1)."\n");
            }
        }
        
        for($j=1;$j<$xml_num_moofs;$j++){
            //$sampleDurFragPrev=$xml_tfhd[$j-1]->getAttribute('defaultSampleDuration');
            //$sampleCountFragPrev=$xml_trun[$j-1]->getAttribute('sampleCount');
            $cummulatedSampleDurFragPrev=$xml_trun->item($j-1)->getAttribute('cummulatedSampleDuration');
            $decodeTimeFragPrev=$xml_tfdt->item($j-1)->getAttribute('baseMediaDecodeTime');
            $decodeTimeFragCurr=$xml_tfdt->item($j)->getAttribute('baseMediaDecodeTime');
            
            if($decodeTimeFragCurr!=$decodeTimeFragPrev+$cummulatedSampleDurFragPrev){//($sampleDurFragPrev*$sampleCountFragPrev)){
                fprintf($opfile, "**'CMAF check violated: Section 7.3.2.2- Each CMAF Fragment in a CMAF Track SHALL have baseMediaDecodeTime equal to the sum of all prior Fragment durations added to the first Fragment's baseMediaDecodeTime', but not found for Rep ".$id." Fragment ".($j+1)."\n");
                $errorInTrack=1;
            }
        }
        for($j=0;$j<$xml_num_moofs;$j++){
            if($xml_trun->item($j)->getAttribute('version') ==1)
            {
                $firstSampleCompTime=$xml_trun->item($j)->getAttribute('earliestCompositionTime');
                $firstDecTime=$xml_tfdt->item(0)->getAttribute('baseMediaDecodeTime');
                if($firstSampleCompTime!=$firstDecTime)
                    fprintf($opfile, "**'CMAF check violated: Section 7.5.17- For 'trun' version 1, the composition time of 1st presented sample in a CMAF Segment SHALL be same as 1st Sample decode time (baseMediaDecodeTime), but not found in Rep ".$id." \n");
        
            }
            $moofSize=$xml_moof->item($j)->getAttribute('size');
            $dataOffset=$xml_trun->item($j)->getAttribute('data_offset');
            if($dataOffset != $moofSize + 8)
                fprintf($opfile, "**'CMAF check violated: Section 7.3.2.3- All media samples in a CMAF Chunk SHALL be addressed by byte offsets in the TrackRunBox relative to first byte of the MovieFragmentBox', but not found for Rep ".$id." Chunk ".($j+1)."\n");
            
        }
        if($errorInTrack)
            fprintf($opfile, "**'CMAF check violated: Section 7.3.2.2- The concatenation of a CMAF Header and all CMAF Fragments in the CMAF Track in consecutive decode order SHALL be a valid fragmented ISOBMFF file', but not found for Rep ".$id."\n");
            
        $xml_hdlr=$xml->getElementsByTagName('hdlr')->item(0);
        $xml_handlerType=$xml_hdlr->getAttribute('handler_type');
         
        $xml_elst=$xml->getElementsByTagName('elstEntry');
        if($xml_elst->length>0 && $xml_handlerType=='vide'){
            $firstSampleCompTime=$xml_trun->item(0)->getAttribute('earliestCompositionTime');
            $mediaTime=$xml_elst->item(0)->getAttribute('mediaTime');
            if($mediaTime != $firstSampleCompTime)
                fprintf($opfile, "**'CMAF check violated: Section 7.5.13- In video CMAF track, an EditListBox shall be used to adjust the earliest video sample to movie presentation time zero, i.e., media-time equal to composition-time of earliest presented sample in the 1st Fragment', but media-time is not equal to composition-time for Rep ".$id."\n");
        }
        
        /*$ParamSetPresent=0;
        $xml_videSample=$xml->getElementsByTagName('vide_sampledescription');
        if($xml_videSample->length>0){
            $sdType=$xml_videSample->item(0)->getAttribute('sdType');
            if($sdType == "hvc1"){
                $xml_NALUnit=$xml->getElementsByTagName('NALUnit');
                if($xml_NALUnit->length==0)
                     fprintf($opfile, "**'CMAF check violated: Section B.2.1.2. - For a Visual Sample Entry with codingname 'hvc1', SHALL contain one or more decoding parameter sets(Containing VPS,SPS and PPS NALs for HEVC Video), but NALs not found in the Rep/Track ".$id."\n");
                else{ 
                    for($k=0; $k< ($xml_NALUnit->length); $k++){
                        $ParamSet=$xml_NALUnit->item($k)->getAttribute('nal_unit_type');
                        if($ParamSet ==32 || $ParamSet ==33|| $ParamSet ==34)
                            $ParamSetPresent=1;
                        }
                        if($ParamSetPresent==0)
                            fprintf($opfile, "**'CMAF check violated: Section B.2.1.2. - For a Visual Sample Entry with codingname 'hvc1', SHALL contain one or more decoding parameter sets(Containing VPS,SPS and PPS NALs for HEVC Video), but found none in the Rep/Track ".$id."\n");
                }
            }
        }*/
        $xml_videSample=$xml->getElementsByTagName('vide_sampledescription');
        if($xml_videSample->length>0){
            $sdType=$xml_videSample->item(0)->getAttribute('sdType');
            if($sdType == "hvc1" || $sdType =="hev1"){
                $xml_hvcc=$xml_videSample->item(0)->getElementsByTagName('hvcC');
                if($xml_hvcc->length!=1)
                    fprintf($opfile, "**'CMAF check violated: Section B.2.3. - The HEVCSampleEntry SHALL contain an HEVCConfigurationBox (hvcC) containing an HEVCDecoderConfigurationRecord, but found ".$xml_hvcc->length." box in the Rep/Track ".$id."\n");
            }
            if( $sdType =="hev1"){
                $vui_flag=0;
                $xml_NALUnit=$xml->getElementsByTagName('NALUnit');
                for($k=0; $k< ($xml_NALUnit->length); $k++){    
                    $ParamSet=$xml_NALUnit->item($k)->getAttribute('nal_unit_type');
                        if($ParamSet ==33)
                            $vui_flag=$xml_NALUnit->item($k)->getAttribute('vui_parameters_present_flag');    
                }
                if($vui_flag==0){
                    $colr=$xml_videSample->item(0)->getElementsByTagName('colr');
                    $pasp=$xml_videSample->item(0)->getElementsByTagName('pasp');
                    if($pasp->length==0)
                        fprintf($opfile, "**'CMAF check violated: Section B.2.3. - The HEVCSampleEntry SHALL contain PixelAspectRatioBox (pasp), but not found in the Rep/Track ".$id."\n");
                    if($colr->length==0)
                        fprintf($opfile, "**'CMAF check violated: Section B.2.3. - The HEVCSampleEntry SHALL contain ColorInformationBox (colr), but not found in the Rep/Track ".$id."\n");
                    else{
                        if($colr->item(0)->getAttribute('colrtype') !='nclx')
                            fprintf($opfile, "**'CMAF check violated: Section B.2.3. - The HEVCSampleEntry SHALL contain ColorInformationBox (colr) with colour_type 'nclx', but this colour_type ".$colr->item(0)->getAttribute('colrtype')." found in the Rep/Track ".$id."\n");

                    }
                }
            }
            
        }
        //Check for metadata required to decode, decrypt, display in CMAF Header.
        // $xml_hdlr=$xml->getElementsByTagName('hdlr')[0];
        // $xml_handlerType=$xml_hdlr->getAttribute('handler_type');
         if($xml_handlerType=='vide' ){
             if($sdType =='avc1' || $sdType== 'avc3'){
             
                $width=$xml_videSample->item(0)->getAttribute('width');
                $height=$xml_videSample->item(0)->getAttribute('height');
                $xml_NALUnit=$xml->getElementsByTagName('NALUnit');
                if($xml_NALUnit->length>0){
                    $xml_NALComment=$xml_NALUnit->item(0)->getElementsByTagName('comment');
                    $num_ticks=$xml_NALComment->item(0)->getAttribute('num_units_in_tick');
                    $time_scale=$xml_NALComment->item(0)->getAttribute('time_scale');
                    $profile_idc=$xml_NALUnit->item(0)->getAttribute('profile_idc');
                    $level_idc=$xml_NALComment->item(0)->getAttribute('level_idc');
                }
                if($width== NULL )
                     fprintf($opfile, "**'CMAF check violated: Section 7.3.2.4. - Each CMAF Fragment in combination with its associated Header SHALL contain sufficient metadata to be decoded and displayed when independently accessed, but 'width' missing in the Header of Rep/Track ".$id."\n");
                if($height==NULL)
                     fprintf($opfile, "**'CMAF check violated: Section 7.3.2.4. - Each CMAF Fragment in combination with its associated Header SHALL contain sufficient metadata to be decoded and displayed when independently accessed, but 'height' missing in the Header of Rep/Track ".$id."\n");
                if($profile_idc ==NULL)
                     fprintf($opfile, "**'CMAF check violated: Section 7.3.2.4. - Each CMAF Fragment in combination with its associated Header SHALL contain sufficient metadata to be decoded and displayed when independently accessed, but 'profile_idc' missing in the Header of Rep/Track ".$id."\n");
                if($level_idc==NULL)
                     fprintf($opfile, "**'CMAF check violated: Section 7.3.2.4. - Each CMAF Fragment in combination with its associated Header SHALL contain sufficient metadata to be decoded and displayed when independently accessed, but 'level_idc' missing in the Header of Rep/Track ".$id."\n");
                if(($num_ticks==NULL || $time_scale==NULL))
                     fprintf($opfile, "**'CMAF check violated: Section 7.3.2.4. - Each CMAF Fragment in combination with its associated Header SHALL contain sufficient metadata to be decoded and displayed when independently accessed, but FPS info (num_ticks & time_scale) missing in the Header of Rep/Track ".$id."\n");
             }
            
         }
         if($xml_handlerType=='soun'){
             $xml_sounSample=$xml->getElementsByTagName('soun_sampledescription');
             $sdType=$xml_sounSample->item(0)->getAttribute('sdType');
             $samplingRate=$xml_sounSample->item(0)->getAttribute('sampleRate');    
             $xml_audioDec=$xml->getElementsByTagName('DecoderSpecificInfo');
             if($xml_audioDec->length>0)
                $channelConfig=$xml_audioDec->item(0)->getAttribute('channelConfig');
             if($sdType==NULL  )
                 fprintf($opfile, "**'CMAF check violated: Section 7.3.2.4. - Each CMAF Fragment in combination with its associated Header SHALL contain sufficient metadata to be decoded and displayed when independently accessed, but audio 'sdTtype' missing in the Header of Rep/Track ".$id."\n");
             if($samplingRate==NULL)
                 fprintf($opfile, "**'CMAF check violated: Section 7.3.2.4. - Each CMAF Fragment in combination with its associated Header SHALL contain sufficient metadata to be decoded and displayed when independently accessed, but audio 'samplingRate' missing in the Header of Rep/Track ".$id."\n");
             if($channelConfig==NULL)
                 fprintf($opfile, "**'CMAF check violated: Section 7.3.2.4. - Each CMAF Fragment in combination with its associated Header SHALL contain sufficient metadata to be decoded and displayed when independently accessed, but audio 'channelConfig' missing in the Header of Rep/Track ".$id."\n");

         }
         $dash264 = false;
         if (strpos($Adapt['Representation']['profiles'][$i], "http://dashif.org/guidelines/dash264") !== false) {
                    $dash264 = true;
         }         
         if ($Adapt['Representation']['ContentProtectionElementCount'][$i] > 0 && $dash264 == true) {
              if($xml->getElementsByTagName('tenc')->length ==0)
                 fprintf($opfile, "**'CMAF check violated: Section 7.3.2.4. - Each CMAF Fragment in combination with its associated Header SHALL contain sufficient metadata to be decrypted when independently accessed, but missing in the Header of Rep/Track ".$id."\n");
              else{
                  $xml_tenc=$xml->getElementsByTagName('tenc');
                  $AuxInfoPresent=($xml_tenc->item(0)->getAttribute('default_IV_size')!=0);
                  if($AuxInfoPresent){
                      for($j=0;$j<$xml_num_moofs;$j++){
                          $xml_traf=$xml_moof->item($j)->getElementsByTagName('traf');
                          $xml_senc=$xml_traf->item(0)->getElementsByTagName('senc');
                          if($xml_senc->length==0){
                             fprintf($opfile, "**'CMAF check violated: Section 7.4.2. - When Sample Encryption Sample Auxiliary Info is used, 'senc' SHALL be present in each CMAF Fragment, but not found in Rep/Track ".$id." Fragment ".($j+1)."\n");
                             fprintf($opfile, "**'CMAF check violated: Section 7.3.2.4. - Each CMAF Fragment in combination with its associated Header SHALL contain sufficient metadata to be decrypted when independently accessed, but missing in the Fragment ".($j+1)." of Rep/Track ".$id."\n");
                          }
                      }
                  }
              }

         }
         //
         //Segment Index box check.
         $sidx=$xml->getElementsByTagName('sidx');
         if($sidx->length>0)
         {
                for($j=0; $j < $sidx->length; $j++){
                    $ref_count=$sidx->item($j)->getAttribute('referenceCount');
                    $syncSampleError=0;
                    for($z=0; $z<$ref_count; $z++){
                        $ref_type=$sidx->item($j)->getAttribute('reference_type_'.($z+1));
                        if($ref_type!=0)
                            fprintf($opfile, "**'CMAF check violated: Section 7.3.3.3. - If SegmentIndexBoxes exist, each subsegment referenced in the SegmentIndexBox SHALL be a single CMAF Fragment contained in the CMAF Track File, but reference to Fragment not found in Rep/Track ".$id.", Segment ".($z+1)."\n");
                    //Check on non_sync_sample
                     /*   if($xml_handlerType=='vide'){
                        $sap_type=intval($sidx[$j]->getAttribute('SAP_type_'.($z+1)));
                        $sample_count=$xml_trun[max($z,$j)]->getAttribute('sampleCount');
                        for($a=0;$a<$sample_count;$a++){
                            $sample_flag=intval($xml_trun[$z]->getAttribute('sample_flags_'.($a+1)));
                            // non_sync_sample is the 16th bit from MSB in 32-bit.
                            $sample_flag=$sample_flag & hexdec("00010000");//0x00010000; 
                            if($sap_type ==1 || $sap_type==2){ 
                               if($sample_flag !=0)
                                   $syncSampleError=1;
                                 //fprintf($opfile, "**'CMAF check violated: Section 7.5.16. - Within a video CMAF Track, TrackRunBox SHALL identify non-sync pictures with sample_is_non_sync_sample as 0 for SAP type 1 or 2, but not found in Rep/Track ".$id.", Fragment ".($z+1)."\n");
                            }else if(sample_flag!=hexdec("10000")){//0x10000
                                $syncSampleError=1;
                                //fprintf($opfile, "**'CMAF check violated: Section 7.5.16. - Within a video CMAF Track, TrackRunBox SHALL identify non-sync pictures with sample_is_non_sync_sample as 1 for SAP type other than 1 or 2, but not found in Rep/Track ".$id.", Fragment ".($z+1)."\n");
                            }
                        }  //This is to avoid printing for each sample in trun- it makes output log huge.
                            if($syncSampleError)
                                fprintf($opfile, "**'CMAF check violated: Section 7.5.16. - Within a video CMAF Track, TrackRunBox SHALL identify non-sync pictures with sample_is_non_sync_sample as 0 for SAP type 1 or 2, and 1 if not, but not found in Rep/Track ".$id.", Fragment ".(max($z,$j)+1)."\n");

                       }*/
                    }
                }
         }
         //
    }
}


function checkAlignedSwitchingSets(){
    global $locate, $Period_arr;
    $index=array();
    //Todo:More generalized approach with many Aligned Sw Sets.
    //Here assumption is only two Sw Sets are aligned.
    for($z=0;$z<count($Period_arr);$z++)
    {
        if($Period_arr[$z]['alignedToSet']!=0)
            array_push ($index, $Period_arr[$z]['alignedToSet']);                
    }
    if(count($index)>=1) // 0 means no Aligned SwSet, 2 or more is fine, 1 means error should be raised.
    {
        if(!($opfile = fopen($locate."/AlignedSwitchingSet_infofile.txt", 'w'))){
            echo "Error opening/creating Aligned SwitchingSet conformance check file: "."./AlignedSwitchingSet_infofile.txt";
            return;
        }
    }
    else
        return;
    
    if(count($index)>=2) 
    {
        $loc1 = $locate . '/Adapt' . ($index[0]-1).'/';
        $filecount1 = 0;
        $files1 = glob($loc1. "*.xml");
        if($files1)
            $filecount1 = count($files1);
        
        
        if(!file_exists($loc1))
            fprintf ($opfile, "Tried to retrieve data from a location that does not exist. \n (Possible cause: Representations are not valid and no file/directory for box info is created.)");
        else{
            fprintf($opfile, "**Aligned SwitchingSet conformance check for: SwitchingSets (Adaptationsets) ".$index[1]." and ".$index[0].":\n\n");
            
            for($i=0;$i<$filecount1;$i++){
            
                $xml = xmlFileLoad($files1[$i]);
                $loc2 = $locate . '/Adapt' . ($index[1]-1).'/';
                $filecount2 = 0;
                $files2 = glob($loc2. "*.xml");
                if($files2)
                    $filecount2 = count($files2);
                    
                $id = $Period_arr[$index[0]-1]['Representation']['id'][$i];
                
                if(!file_exists($loc2))
                    fprintf ($opfile, "Tried to retrieve data from a location that does not exist. \n (Possible cause: Representations are not valid and no file/directory for box info is created.)");
                else{
                    for($j=0;$j<$filecount2;$j++){
                        $xml_comp = xmlFileLoad($files2[$j]);
                        $id_comp = $Period_arr[$index[1]-1]['Representation']['id'][$j];
                        $xml_num_moofs=$xml->getElementsByTagName('moof')->length;
                        $xml_comp_num_moofs=$xml_comp->getElementsByTagName('moof')->length;
                        
                        //Check Tracks have same ISOBMFF defined duration.
                        if($i==0 && $j==0) // As duration is checked between Sw Sets, checking only once is enough.
                        {
                            if($xml->getElementsByTagName('mehd')->length >0 && $xml_comp->getElementsByTagName('mehd')->length >0 ){
                                $xml_mehd=$xml->getElementsByTagName('mehd')->item(0);
                                $xml_mehdDuration=$xml_mehd->getAttribute('fragmentDuration');

                                $xml_comp_mehd=$xml_comp->getElementsByTagName('mehd')->item(0);
                                $xml_comp_mehdDuration=$xml_comp_mehd->getAttribute('fragmentDuration');


                                if($xml_mehdDuration!=$xml_comp_mehdDuration)
                                    fprintf($opfile, "**'CMAF check violated: Section 7.3.4.4- Aligned Switching Sets SHALL contain CMAF switching sets of equal duration', but not matching between Switching Set ".$index[0]." and Switching Set ".$index[1]." \n");
                            }
                            else
                            {
                                $xml_lasttfdt=$xml->getElementsByTagName('tfdt')->item($xml_num_moofs-1);
                                $xml_comp_lasttfdt=$xml_comp->getElementsByTagName('tfdt')->item($xml_comp_num_moofs-1);

                                $xml_lastDecodeTime=$xml_lasttfdt->getAttribute('baseMediaDecodeTime');
                                $xml_comp_lastDecodeTime=$xml_comp_lasttfdt->getAttribute('baseMediaDecodeTime');

                                $xml_lasttrun=$xml->getElementsByTagName('trun')->item($xml_num_moofs-1);
                                $xml_comp_lasttrun=$xml_comp->getElementsByTagName('trun')->item($xml_comp_num_moofs-1);

                                $xml_cumSampleDur=$xml_lasttrun->getAttribute('cummulatedSampleDuration');
                                $xml_comp_cumSampleDur=$xml_comp_lasttrun->getAttribute('cummulatedSampleDuration');

                                if($xml_lastDecodeTime+$xml_cumSampleDur != $xml_comp_lastDecodeTime+$xml_comp_cumSampleDur)
                                    fprintf($opfile, "**'CMAF check violated: Section 7.3.4.4- Aligned Switching Sets SHALL contain CMAF switching sets of equal duration', but not matching between Rep". $id." of Switching Set ".$index[0]." and Rep ".$id_comp." of Switching Set ".$index[1]." \n");
                            }
                        }
                        //Check Tracks have same number of moofs.
                        if($xml_num_moofs!=$xml_comp_num_moofs){
                            fprintf($opfile, "**'CMAF check violated: Section 7.3.4.4- Aligned Switching Sets SHALL contain the same number of CMAF Fragments in every CMAF Track', but not matching between Rep ". $id." of Switching Set ".$index[0]." and Rep ".$id_comp." of Switching Set ".$index[1]." \n");
                            break;
                            }
                        //This check only if previous check is not failed.
                        $xml_tfhd=$xml->getElementsByTagName('tfhd');
                        $xml_trun=$xml->getElementsByTagName('trun');
                        $xml_tfdt=$xml->getElementsByTagName('tfdt');
                        $xml_comp_tfhd=$xml_comp->getElementsByTagName('tfhd');
                        $xml_comp_trun=$xml_comp->getElementsByTagName('trun');
                        $xml_comp_tfdt=$xml_comp->getElementsByTagName('tfdt');
                        
                        for($y=0; $y<$xml_num_moofs;$y++){
                             //$sampleDur1=$xml_tfhd[$y]->getAttribute('defaultSampleDuration');
                             //$sampleCount1=$xml_trun[$y]->getAttribute('sampleCount');
                             $cummulatedSampleDur1=$xml_trun->item($y)->getAttribute('cummulatedSampleDuration');
                             $decodeTime1=$xml_tfdt->item($y)->getAttribute('baseMediaDecodeTime');
                             
                             //$sampleDur2=$xml_comp_tfhd[$y]->getAttribute('defaultSampleDuration');
                             //$sampleCount2=$xml_comp_trun[$y]->getAttribute('sampleCount');
                             $cummulatedSampleDur2=$xml_comp_trun->item($y)->getAttribute('cummulatedSampleDuration');
                             $decodeTime2=$xml_comp_tfdt->item($y)->getAttribute('baseMediaDecodeTime');
                             
                             if($cummulatedSampleDur1!= $cummulatedSampleDur2 || $decodeTime1!=$decodeTime2){
                                fprintf($opfile, "**'CMAF check violated: Section 7.3.4.4- Aligned Switching Sets SHALL contain CMAF Fragments in every CMAF Track with matching baseMediaDecodeTime and duration', but not matching between Rep ". $id." of Switching Set ".$index[0]." and Rep ".$id_comp." of Switching Set ".$index[1]." \n");
                                break;
                             }
                        }
                    }
                }
            }
        }
        
    }
    else
    {
        fprintf($opfile, "**Aligned SwitchingSet conformance check :\n\n");
        fprintf($opfile, "**'CMAF check violated: Section 7.3.4.4- Aligned Switching Sets SHALL contain two or more CMAF switching sets', but only one found. \n");

    }
    fprintf($opfile, "\n-----Conformance checks completed----- ");
    fclose($opfile);
}

function checkMediaProfiles($xml, $xml_comp,$xml_handlerType,$xml_comp_handlerType)
{
    $xml_ftyp=$xml->getElementsByTagName('ftyp')->item(0);
    $brands1=(string)$xml_ftyp->getAttribute('compatible_brands');

    $xml_comp_ftyp=$xml_comp->getElementsByTagName('ftyp')->item(0);
    $brands2=(string)$xml_comp_ftyp->getAttribute('compatible_brands');
    
    $err=0;
    if($xml_handlerType==$xml_comp_handlerType && $xml_handlerType='vide')
    {
        $videoCmaf1=strpos($brands1,"cfsd") || strpos($brands1,"cfhd") || strpos($brands1,"chdf");
        $videoCmaf2=strpos($brands2,"cfsd") || strpos($brands2,"cfhd") || strpos($brands3,"chdf");
        if($videoCmaf1==false || $videoCmaf2==false)
            $err=1;
            
    }
    elseif($xml_handlerType==$xml_comp_handlerType && $xml_handlerType='soun')
    {
        if(strpos($brands1,"caac") && strpos($brands2,"caac")==false )
            $err=1;
        else if(strpos($brands1,"caaa") && strpos($brands2,"caaa")==false)
            $err=1;
        else
            $err=1;
        
    }
    //Todo : Subtitle media profile checks.
    //elseif($xml_handlerType==$xml_comp_handlerType && $xml_handlerType='subt')
    //{
    //}
    return $err;
}

function checkCMAFPresentation()
{   
    global $Period_arr,$locate,$profiles, $cfhd_SwSetFound,$caac_SwSetFound, $encryptedSwSetFound,$MPD;
    //Assuming one of the CMAF profiles will be present.
    $profile_cmfhd=strpos($profiles, 'urn:mpeg:cmaf:presentation_profile:cmfhd:2017');
    $profile_cmfhdc=strpos($profiles, 'urn:mpeg:cmaf:presentation_profile:cmfhdc:2017');
    $profile_cmfhds=strpos($profiles, 'urn:mpeg:cmaf:presentation_profile:cmfhds:2017');
    $videoFound=0;
    $audioFound=0;
    $firstEntryflag=1;
    $firstVideoflag=1;
    $firstNonVideoflag=1;
    $im1t_SwSetFound=0;
    $subtitle_array=array();
    $subtitleFound=0;
    $trackDurArray=array();
    $maxFragDur=0;
   // $lang_count=0;
    if(!($opfile = fopen($locate."/Presentation_infofile.txt", 'w'))){
            echo "Error opening/creating Presentation profile conformance check file: "."./Presentation_infofile.txt";
            return;
        }
    fprintf($opfile, "**Presentation (profile) conformance check: \n\n");
    
    $mediaPresentationDuration = $MPD->getAttribute('mediaPresentationDuration');
    $PresentationDur=timeparsing($mediaPresentationDuration);
    $videoFragDur=0;
    for($adapt_count=0; $adapt_count<sizeof($Period_arr); $adapt_count++){   
        $loc = $locate . '/Adapt' . $adapt_count.'/';
            
        $Adapt=$Period_arr[$adapt_count];
        $filecount = 0;
        $files = glob($loc . "*.xml");
        if($files)
            $filecount = count($files);
            
            $video_counter=0;
            $audio_counter=0;
            $enc_counter=0;
            if(!file_exists($loc))
                    fprintf ($opfile, "Switching Set ".$adapt_count."-Tried to retrieve data from a location that does not exist. \n (Possible cause: Representations are not valid and no file/directory for box info is created.)");
                else{
        for($i=0; $i<$filecount; $i++){     
        
            $filename = $files[$i];                 //load file
            $xml = xmlFileLoad($filename);
            $id = $Adapt['Representation']['id'][$i];
            //Check Section 7.3.4 conformance
            $xml_tfdt=$xml->getElementsByTagName('tfdt')->item(0);
            $xml_baseDecodeTime=$xml_tfdt->getAttribute('baseMediaDecodeTime');
            $xml_trun=$xml->getElementsByTagName('trun');
            $xml_earliestCompTime=$xml_trun->item(0)->getAttribute('earliestCompositionTime');
            $xml_hdlr=$xml->getElementsByTagName('hdlr')->item(0);
            $xml_handlerType=$xml_hdlr->getAttribute('handler_type');
            $xml_elst=$xml->getElementsByTagName('elstEntry');
            $xml_mdhd=$xml->getElementsByTagName('mdhd');
            $timescale=$xml_mdhd->item(0)->getAttribute('timescale');
            
            $mediaTime=0;
            if($xml_elst->length>0 ){
                $mediaTime=$xml_elst->item(0)->getAttribute('mediaTime');
            }
                        
            if($firstEntryflag)
            {
                $firstEntryflag=0;
                $firstTrackTime=$xml_baseDecodeTime/$timescale;
                //continue;
            }
            else{
                if($firstTrackTime!=$xml_baseDecodeTime/$timescale)
                    fprintf ($opfile,"**'CMAF check violated: Section 7.3.6-'All CMAF Tracks in a CMAF Presentation SHALL have the same timeline origin', but not matching between Switching Set 1 Track 1 and Switching Set ".($adapt_count+1)." Track ".$id." \n");
                
            }
            //Check alignment of presentation time for video and non video tracks separately. FDIS
            if($xml_handlerType=='vide')
            {
                if($firstVideoflag)
                {
                    $firstVideoflag=0;
                    $firstVideoTrackPT=$mediaTime-$xml_earliestCompTime;
                    $firstVideoAdaptcount=$adapt_count;
                    $firstVideoRepId=$id;
                }
                else
                {
                    if($firstVideoTrackPT!=$mediaTime-$xml_earliestCompTime)
                        fprintf ($opfile,"**'CMAF check violated: Section 7.3.6-'All CMAF Tracks in a CMAF Presentation containing video SHALL be start aligned with CMAF presentation time zero equal to the earliest video sample presentation start time in the earliest CMAF Fragment ', but not matching between Switching Set ".($firstVideoAdaptcount+1)." Track ".$firstVideoRepId." and Switching Set ".($adapt_count+1)." Track ".$id." \n");
                }
            }
            else
            {
                if($firstNonVideoflag)
                {
                    $firstNonVideoflag=0;
                    $firstNonVideoTrackPT=$mediaTime+$xml_earliestCompTime;
                    $firstNonVideoAdaptcount=$adapt_count;
                    $firstNonVideoRepId=$id;
                }
                else
                {
                    if($firstNonVideoTrackPT!=$mediaTime+$xml_earliestCompTime)
                        fprintf ($opfile,"**'CMAF check violated: Section 7.3.6-'All CMAF Tracks in a CMAF Presentation that does not contain video SHALL be start aligned with CMAF presentation time zero equal to the earliest audio sample presentation start time in the earliest CMAF Fragment ', but not matching between Switching Set ".($firstNonVideoAdaptcount+1)." Track ".$firstNonVideoRepId." and Switching Set ".($adapt_count+1)." Track ".$id." \n");
                }
            }
            
            //To find the longest CMAF track in the presentation
            $xml_mvhd=$xml->getElementsByTagName('mvhd');
            $xml_mehd=$xml->getElementsByTagName('mehd');
            $xml_num_moofs=$xml->getElementsByTagName('moof')->length;
            if($xml_mehd->length>0)
            {
                $mvhd_timescale=$xml_mvhd->item(0)->getAttribute('timeScale');
                $fragmentDur=$xml_mehd->item(0)->getAttribute('fragmentDuration');
                array_push($trackDurArray,$fragmentDur/$mvhd_timescale);
            }
            else
            { 
                $xml_lasttfdt=$xml->getElementsByTagName('tfdt')->item($xml_num_moofs-1);
                $xml_lastDecodeTime=$xml_lasttfdt->getAttribute('baseMediaDecodeTime');
                $xml_lasttrun=$xml->getElementsByTagName('trun')->item($xml_num_moofs-1);
                $xml_cumSampleDur=$xml_lasttrun->getAttribute('cummulatedSampleDuration');
                array_push($trackDurArray,($xml_lastDecodeTime+$xml_cumSampleDur)/$timescale);
            }
                        
            //Find max video fragment duration from all the tracks.
            if($xml_handlerType=='vide'){
                for($z=0;$z<$xml_num_moofs;$z++)
                {
                    $fragDur=($xml_trun->item($z)->getAttribute('cummulatedSampleDuration'))/$timescale;
                    if($fragDur>$maxFragDur)
                        $maxFragDur=$fragDur;
                }
            }
            //
            
            //Check profile conformance
            //$xml_ftyp=$xml->getElementsByTagName('ftyp')[0];
            //$brands=(string)$xml_ftyp->getAttribute('compatible_brands');
            if($profile_cmfhd)
            {
                if($xml_handlerType=='vide')
                {
                    $videoFound=1;
                    if(cfhd_MediaProfileConformance($xml)==false)
                        break;
                    else
                        $video_counter=$video_counter+1;
                    
                    if($cfhd_SwSetFound=0 && $video_counter ==$filecount)
                        $cfhd_SwSetFound=1;
                    
                }
                else if($xml_handlerType=='soun')
                {
                    $audioFound=1;
                    if(caac_mediaProfileConformance($xml)==false)
                        break;
                    else
                    $audio_counter=$audio_counter+1;
                    
                    if($caac_SwSetFound=0 && $audio_counter == $filecount)
                        $caac_SwSetFound=1;
                }
                
                if($xml->getElementsByTagName('tenc')->length >0)
                {
                    fprintf($opfile, "**'CMAF check violated: Section A.1.2 - 'All CMAF Tracks SHALL NOT contain encrypted Samples or a TrackEncryptionBox', but found in Switching Set ".$adapt_count." Rep ".$id." \n");
                }
                    

            }
            if($profile_cmfhdc)
            {
                if($xml_handlerType=='vide')
                {
                    $videoFound=1;
                    if(cfhd_MediaProfileConformance($xml)==false)
                        break;
                    else
                        $video_counter=$video_counter+1;
                    
                    if($cfhd_SwSetFound=0 && $video_counter ==$filecount)
                        $cfhd_SwSetFound=1;
                }
                else if($xml_handlerType=='soun')
                {
                    $audioFound=1;
                    if(caac_mediaProfileConformance($xml)==false)
                        break;
                    else
                    $audio_counter=$audio_counter+1;
                        
                    if($caac_SwSetFound=0 && $audio_counter == $filecount)
                        $caac_SwSetFound=1;
                }
                
                if($xml->getElementsByTagName('tenc')->length >0)
                {   
                    $enc_counter=$enc_counter+1;
                    $schm=$xml->getElementsByTagName('schm');
                    if($schm->length>0)
                        if($schm->item(0)->getAttribute('scheme')!='cenc')
                            fprintf($opfile, "**'CMAF check violated: Section A.1.3 - 'Any CMAF Switching Set that is encrypted SHALL be available in 'cenc' Common Encryption scheme', but found scheme ".$schm->item(0)->getAttribute('scheme')." \n");
                    if($encryptedSwSetFound=0 && $enc_counter == $filecount)
                        $encryptedSwSetFound=1;
                }

            }
            if($profile_cmfhds)
            {
                if($xml_handlerType=='vide')
                {
                    $videoFound=1;
                    if(cfhd_MediaProfileConformance($xml)==false)
                        break;
                    else
                        $video_counter=$video_counter+1;
                    
                    if($cfhd_SwSetFound=0 && $video_counter ==$filecount)
                        $cfhd_SwSetFound=1;
                }
                else if($xml_handlerType=='soun')
                {
                    $audioFound=1;
                    if(caac_mediaProfileConformance($xml)==false)
                        break;
                    else
                    $audio_counter=$audio_counter+1;
                        
                    if($caac_SwSetFound=0 && $audio_counter == $filecount)
                        $caac_SwSetFound=1;
                }
                
                 if($xml->getElementsByTagName('tenc')->length >0)
                {   
                    $enc_counter=$enc_counter+1;
                    $schm=$xml->getElementsByTagName('schm');
                    if($schm->length>0)
                        if($schm->item(0)->getAttribute('scheme')!='cbcs')
                            fprintf($opfile, "**'CMAF check violated: Section A.1.4 - 'Any CMAF Switching Set that is encrypted SHALL be available in 'cbcs' Common Encryption scheme', but found scheme ".$schm->item(0)->getAttribute('scheme')." \n");
                    if($encryptedSwSetFound=0 && $enc_counter == $filecount)
                        $encryptedSwSetFound=1;
                }

            }
        
          }
        }
        //Check for subtitle conformance of Section A.1
        if($profile_cmfhd || $profile_cmfhdc || $profile_cmfhds){
            if(strpos($Adapt['mimeType'],"application/ttml+xml"))
            {
                $subtitleFound=1;
                $codecs_Adapt=$Adapt['codecs'];
                $lang=$Adapt['language'];
                $role_scheme=$Adapt['Role']['scheme'];
                if($lang!=0){
                    if(empty($subtitle_array))
                        $subtitle_array=array($lang => 0);
                    else{
                        if(!array_key_exists($lang, $subtitle_array))
                            $subtitle_array[$lang]=0;//array_push($subtitle_array, $lang=>0);
                    }
                    //$lang_count++;
                    if(strpos($codecs_Adapt, "im1t"))
                        $subtitle_array[$lang]=1;
                }
                
            }
        }
        //
    }
    //Check if presentation duration is same as longest track duration.
    if(round($PresentationDur,1)!=round(max($trackDurArray),1))
        fprintf($opfile, "**'CMAF check violated: Section 7.3.6 - 'The duration of a CMAF presentation shall be the duration of its longest CMAF track', but not found (Presentation time= ".$PresentationDur." and longest Track= ".max($trackDurArray).") \n");
    //
    //
    for($y=0;$y<count($trackDurArray);$y++)
    {
        if(!((round($trackDurArray[$y],1)>=round($PresentationDur-$maxFragDur,1)) && (round($trackDurArray[$y],1)<=round($PresentationDur+$maxFragDur,1))))
            fprintf ($opfile,"**'CMAF check violated: Section 7.3.6-'CMAF Tracks in a CMAF Presentation SHALL equal the CMAF Presentation duration within a tolerance of the longest video CMAF Fragment duration ', but not found in the Track ".$y." \n");

    }
    //
    
    if(($profile_cmfhd || $profile_cmfhdc ||$profile_cmfhds) && $videoFound && $cfhd_SwSetFound!=1)
        fprintf($opfile, "**'CMAF check violated: Section A.1.2/A.1.3/A.1.4 - 'If containing video, SHALL include at least one Switching Set constrained to the 'cfhd' Media Profile', but found none \n");
    if(($profile_cmfhd || $profile_cmfhdc ||$profile_cmfhds) && $audioFound && $caac_SwSetFound!=1)
        fprintf($opfile, "**'CMAF check violated: Section A.1.2/A.1.3/A.1.4 - 'If containing audio, SHALL include at least one Switching Set constrained to the 'caac' Media Profile', but found none \n");
    if($profile_cmfhdc && $encryptedSwSetFound!=1)
        fprintf($opfile, "**'CMAF check violated: Section A.1.3 - 'At least one CMAF Switching Set SHALL be encrypted', but found none. \n");
    if($profile_cmfhds && $encryptedSwSetFound!=1)
        fprintf($opfile, "**'CMAF check violated: Section A.1.4 - 'At least one CMAF Switching Set SHALL be encrypted', but found none. \n");
    if(($profile_cmfhd || $profile_cmfhdc ||$profile_cmfhds) && $subtitleFound){
        $count_subtitleLang=count(subtitle_array);
        for($z=0;$z<$count;$z++)
        {
            if($subtitle_array[$z]!=1)
                fprintf($opfile, "**'CMAF check violated: Section A.1.2/A.1.3/A.1.4 - 'If containing subtitles, SHALL include at least one Switching Set for each language and role in the 'im1t' Media Profile', but found none \n");
        }
    }
     
    fprintf($opfile, "\n-----Conformance checks completed----- ");
    fclose($opfile);
}

function checkSelectionSet()
{
    global $locate, $Period_arr;
    $longFragDur=0;
    $firstEntryflag=1;
    $SwSetDurArray=array();
     if(!($opfile = fopen($locate."/SelectionSet_infofile.txt", 'w'))){
            echo "Error opening/creating SelectionSet_infofile conformance check file: "."SelectionSet_infofile.txt";
            return;
        }
    
    if(sizeof($Period_arr)<1)
        fprintf ($opfile,"**'CMAF check violated: Section 7.3.5-'A CMAF Selection Set SHALL contain one or more CMAF Switching Sets', but found none. \n");

    fprintf($opfile, "**Selection Set conformance check: \n\n");
    for($adapt_count=0; $adapt_count<sizeof($Period_arr); $adapt_count++){
        $loc = $locate . '/Adapt' . $adapt_count.'/';
        
        $Adapt=$Period_arr[$adapt_count];
        $filecount = 0;
        $files = glob($loc . "*.xml");
        if($files)
            $filecount = count($files);
        
       
        
        if(!file_exists($loc))
            fprintf ($opfile, "Tried to retrieve data from a location that does not exist. \n (Possible cause: Representations are not valid and no file/directory for box info is created.) for Switching Set ".$adapt_count."\n");
        else if($filecount>0){
                //for($i=0; $i<$filecount; $i++){     // Not required to check all Tracks, as this kind of check is done in SwitchingSet conformance.
                    
                    $filename = $files[0];                 //load file
                    $xml = xmlFileLoad($filename);
                    $xml_hdlr=$xml->getElementsByTagName('hdlr')->item(0);
                    $xml_handlerType=$xml_hdlr->getAttribute('handler_type');
                    if($firstEntryflag)
                    {
                        $firstEntryflag=0;
                        $firstSwSetType=$xml_handlerType;
                        continue;
                    }
                    else{
                        if($firstSwSetType!=$xml_handlerType)
                             fprintf ($opfile,"**'CMAF check violated: Section 7.3.5-'All CMAF Switching Sets within a CMAF Selection Set SHALL be of the same media type', but not matching between Switching Set 1 and ".($adapt_count+1)." \n");
                    }
               //}
               $xml_mehd=$xml->getElementsByTagName('mehd');
               if($xml_mehd->length>0){
                    $trackDur=$xml_mehd->item(0)->getAttribute('fragmentDuration');
                    $trackDur=$trackDur/1000; // Convert to seconds.
                    array_push($SwSetDurArray, $trackDur);
                       //Check that needs data from all Tracks
                       for($i=0; $i<$filecount; $i++){
                           $filename = $files[$i];                 //load file
                           $xml = xmlFileLoad($filename);
                           $xml_moof=$xml->getElementsByTagName('moof');
                           $xml_tfhd=$xml->getElementsByTagName('tfhd');
                           $xml_trun=$xml->getElementsByTagName('trun');
                           $xml_mdhd=$xml->getElementsByTagName('mdhd');
                           $timescale=$xml_mdhd->item(0)->getAttribute('timescale');
                           for($j=0;$j<$xml_moof->length;$j++){
                               //$sampleDur=$xml_tfhd[$j]->getAttribute('defaultSampleDuration');
                               //$sampleCount=$xml_trun[$j]->getAttribute('sampleCount');
                               $cummulatedSampleDur=$xml_trun->item($j)->getAttribute('cummulatedSampleDuration');
                               if($longFragDur< $cummulatedSampleDur/$timescale) // Process in seconds.
                                   $longFragDur= $cummulatedSampleDur/$timescale;
                           }
                       }
               }
            }
    }
    if(count($SwSetDurArray)>0){
        $min_dur=min($SwSetDurArray);
        for($k=0;$k<count($SwSetDurArray);$k++){
            $SwSetDurArray[$k]=$SwSetDurArray[$k]-$min_dur;
        }
        for($k=0;$k<count($SwSetDurArray);$k++){
            if($SwSetDurArray[$k]>$longFragDur)
               fprintf ($opfile,"**'CMAF check violated: Section 7.3.5-'All Switching Sets within a CMAF Selection Set SHALL be of the same duration, withing a tolerance of the longest CMAF Fragment duration of any Track in the Selection Set', but not found \n");
        }
    }
    
    fprintf($opfile, "\n-----Conformance checks completed----- ");
    fclose($opfile);
}

function cfhd_MediaProfileConformance($xml)
{
    $conform=true;
    $xml_videSample=$xml->getElementsByTagName('vide_sampledescription');
    if($xml_videSample->length>0){
        $sdType=$xml_videSample->item(0)->getAttribute('sdType');
        if($sdType != "avc1" && $sdType != "avc3")
            $conform=false;
            
        $width=$xml_videSample->item(0)->getAttribute('width');
        $height=$xml_videSample->item(0)->getAttribute('height');
        if($width > 1920 && $height > 1080)
            $conform=false;
    }
    else
        $conform=false;
            
    $xml_avcC=$xml->getElementsByTagName('avcC');
    $xml_avcProfile=$xml_avcC->item(0)->getAttribute('profile');
    if($xml_avcProfile !=100 && $xml_avcProfile !=110 && $xml_avcProfile !=122 && $xml_avcProfile !=144)
        $conform=false;
    
    $xml_avcComment=$xml_avcC->item(0)->getElementsByTagName('Comment');
    $xml_level=$xml_avcComment->item(0)->getAttribute('level');
    if($xml_level !=31 && $xml_level !=40)
        $conform=false;
        
    $xml_NALUnit=$xml->getElementsByTagName('NALUnit');
    if($xml_NALUnit->length>0){
       $xml_NALComment=$xml_NALUnit->item(0)->getElementsByTagName('comment');
       if($xml_NALComment->length>0){
        if($xml_NALComment->item(0)->getAttribute(video_signal_type_present_flag) !=0x0 && $xml_NALComment->item(0)->getAttribute('colour_description_present_flag') !=0x0)
        {
            $colorPrimaries=$xml_NALComment->item(0)->getAttribute('colour_primaries');
            if($colorPrimaries !=0x1 && $colorPrimaries !=0x5 && $colorPrimaries !=0x6)
                $conform=false;
                
            $tranferChar=$xml_NALComment->item(0)->getAttribute('transfer_characteristics');
            if($tranferChar !=0x1 && $tranferChar!= 0x6)
                $conform=false;
                
            $matrixCoeff=$xml_NALComment->item(0)->getAttribute('matrix_coefficients');
            if($matrixCoeff !=0x1 && $matrixCoeff !=0x5 && $matrixCoeff !=0x6 )
                $conform=false;
        }
        
        $num_ticks=$xml_NALComment->item(0)->getAttribute('num_units_in_tick');
        $time_scale=$xml_NALComment->item(0)->getAttribute('time_scale');
        $max_FPS=ceil((int)time_scale /(2*(int)num_ticks));
        if($max_FPS >60)
         $conform=false;
        }
    }
    return $conform;
}

function caac_mediaProfileConformance($xml)
{
    $conform=true;
    $xml_audioSample=$xml->getElementsByTagName('soun_sampledescription');
    if($xml_audioSample->length>0){
        $samplingRate=$xml_audioSample->item(0)->getAttribute('sampleRate');
        if((float)$samplingRate>48000.0)
            $conform=false;
    }
    $xml_audioDec=$xml->getElementsByTagName('DecoderSpecificInfo');
    $channelConfig=$xml_audioDec->item(0)->getAttribute('channelConfig');
    if($channelConfig !=0x1 && $channelConfig!=0x2)
        $conform=false;
    
    return  $conform;
}


?>