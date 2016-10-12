<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// All the boxes and related attributes to be checked for CMAF Table 6
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
               "pssh" => array("version", "flags", "systemID", "dataSize"));

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
        
        if(!($opfile = fopen($locate."/Adapt".$i."_compInfo.txt", 'w'))){
            echo "Error opening/creating compared representations' conformance check file: "."./Adapt".$i."_compInfo.txt";
            return;
        }
        
        if(!file_exists($loc))
            fprintf ($opfile, "**Error: Tried to retrieve data from a location that does not exist. \n (Possible cause: Representations are not valid and no file/directory for box info is created.)");
        else if($filecount == 0)                     //if no file exists in the directory, nothing to check
            fprintf($opfile, "**Nothing to check for in Adaptationset ".($i+1)."\n");
        else{                                   //if file(s) do(es) exist, then start checking
            fprintf($opfile, "**Compared representations' conformance check for: Adaptationset ".($i+1).":\n\n");
            for($j=0; $j<$filecount; $j++){         //iterate over all files
                $info_str = file_get_contents($locate."/Adapt".$i."/infofile".$j.".txt");
                
                $filename = $files[$j];
                $first = true;
                
                $xml = loadFile($filename);
                while($xml->read()){                //if any attribute in the xml file contains "No", then this will be considered as an error
                    if($first){                     //obtain the rep ids in the xml file. (info for $opfile)
                        $ids = getIds($xml);
                        fprintf($opfile, "-Representation with id: ".$ids[0]." vs Representation with id: ".$ids[1]."\n");
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
                                else{
                                    fprintf ($opfile, ' Error: Different value for the attribute: '.$xml->name.' in the atom: '.$atom_name."\n\n");
                                    $found = true;
                                }
                            }
                            $xml->moveToNextAttribute();
                        }
                        $xml->moveToElement();
                    }
                }
            
                if(!$found){                        //otherwise this comparison conforms to specifications 
                    fprintf ($opfile, " Everything is fine with the represenations\n\n");
                    $found = false;
                }
            }
        }
        
        fprintf($opfile,"\nChecks completed.\n");
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
                    
                    // for checkRepresentationsConformance (implementation acc. to CMAF Table 6)
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
                        
                        if($xml_att_value != NULL && $xml_comp_att_value != NULL && $xml_att_value == $xml_comp_att_value){
                            if(isset($compXML->$xml_atom_name->attributes()[$att_name]))
                                $compXML->$xml_atom_name->attributes()->$att_name = ((string) $compXML->$xml_atom_name->attributes()->$att_name) . ' Yes';
                            else
                                $compXML->$xml_atom_name->addAttribute($att_name, 'Yes');
                        }
                        else{
                            if(isset($compXML->$xml_atom_name->attributes()[$att_name]))
                                $compXML->$xml_atom_name->attributes()->$att_name = ((string) $compXML->$xml_atom_name->attributes()->$att_name) . ' No';
                            else
                                $compXML->$xml_atom_name->addAttribute($att_name, 'No');
                        }
                    }
                }
            }
        }
    }
    // for checkRepresentationsConformance (implementation acc. to CMAF Table 6)
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
            
            $compXML->asXml($path);                 //save changes
            $ind++;
        }
    }
}
?>