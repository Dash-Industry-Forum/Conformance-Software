<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function CrossValidation_HbbTV_DVB($dom,$hbbtv,$dvb)
{
    common_crossValidation($dom,$hbbtv,$dvb);
}

function common_crossValidation($dom,$hbbtv,$dvb)
{
    global $locate, $Period_arr, $string_info, $cp_dom;
    content_protection_report($cp_dom);
    for($adapt_count=0; $adapt_count<sizeof($Period_arr); $adapt_count++){
        $loc = $locate . '/Adapt' . $adapt_count.'/';
        
        $Adapt=$Period_arr[$adapt_count];
        $filecount = 0;
        $files = glob($loc . "*.xml");
        if($files)
            $filecount = count($files);
        
        if(!($opfile = fopen($locate."/Adapt".$adapt_count."_hbbtv_dvb_compInfo.txt", 'a'))){
            echo "Error opening/creating HbbTV/DVB Cross representation validation file: "."./Adapt".$adapt_count."_hbbtv_dvb_compInfo.txt";
            return;
        }
        
        for($r=0; $r<$filecount; $r++){
            $xml_r = xmlFileLoad($files[$r]);
            
            for($d=$r+1; $d<$filecount; $d++){
                $xml_d = xmlFileLoad($files[$d]);
                
                if($xml_r != 0 && $xml_d != 0){
                    if($hbbtv){
                        crossValidation_HbbTV_Representations($dom, $opfile, $xml_r, $xml_d, $adapt_count, $r, $d);
                    }
                    if($dvb){
                        crossValidation_DVB_Representations($dom, $opfile, $xml_r, $xml_d, $adapt_count, $r, $d);
                    }
                }
            }
        }
        init_seg_commonCheck($files,$opfile);
        
        if($dvb)
            DVB_period_continuous_adaptation_sets_check($dom, $opfile);
        
        if(file_exists($loc)){
       
        }
        
        fclose($opfile);
        $temp_string = str_replace (array('$Template$'),array("Adapt".$adapt_count."_hbbtv_dvb_compInfo"),$string_info);
        file_put_contents($locate.'/'."Adapt".$adapt_count."_hbbtv_dvb_compInfo.html",$temp_string);   
    }
    //DVB_HbbTV_err_file_op();
}

function crossValidation_DVB_Representations($dom, $opfile, $xml_r, $xml_d, $i, $r, $d){
    ## Section 4.3 checks for sample entry type and track_ID
    $hdlr_r = $xml_r->getElementsByTagName('hdlr')->item(0);
    $hdlr_type_r = $hdlr_r->getAttribute('handler_type');
    $sdType_r = $xml_r->getElementsByTagName($hdlr_type_r.'_sampledescription')->item(0)->getAttribute('sdType');
    
    $hdlr_d = $xml_r->getElementsByTagName('hdlr')->item(0);
    $hdlr_type_d = $hdlr_d->getAttribute('handler_type');
    $sdType_d = $xml_d->getElementsByTagName($hdlr_type_d.'_sampledescription')->item(0)->getAttribute('sdType');
    
        ## Non-switchable audia representation reporting
    if($sdType_r != $sdType_d){
        fwrite($opfile, "###'DVB check violated: Section 4.3- All the initialization segments for Representations within an Adaptation Set SHALL have the same sample entry type', found $sdType_r in Adaptation Set " . ($i+1) . " Representation " . ($r+1) . " $sdType_d in Adaptation Set " . ($i+1) . " Representation " . ($d+1) . ".\n");
    
        if($hdlr_type_r == $hdlr_type_d && $hdlr_type_r == 'soun')
            fwrite($opfile, "Warning for HbbTV-DVB DASH Validation Requirements check for DVB: Section 'Adaptation Sets' - 'Non-switchable audio codecs SHOULD NOT be present within the same Adaptation Set for the presence of consistent Representations within an Adaptation Set ', found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
    }
        ##
    
    $tkhd_r = $xml_r->getElementsByTagName('tkhd')->item(0);
    $track_ID_r = $tkhd_r->getAttribute('trackID');
    $tfhds_r = $xml_r->getElementsByTagName('tfhd');
    
    $tkhd_d = $xml_d->getElementsByTagName('tkhd')->item(0);
    $track_ID_d = $tkhd_d->getAttribute('trackID');
    $tfhds_d = $xml_d->getElementsByTagName('tfhd');
    
    $tfhd_info = '';
    foreach($tfhds_r as $index => $tfhd_r){
        if($tfhd_r->getAttribute('trackID') != $tfhds_d->item($index)->getAttribute('trackID'))
            $tfhd_info .= ' error'; 
    }
    
    if($tfhd_info != '' || $track_ID_r != $track_ID_d)
        fwrite($opfile, "###'DVB check violated: Section 4.3- All Representations within an Adaptation Set SHALL have the same track_ID', not equal in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
    ##
    
    ## Section 5.1.2 check for initialization segment identicalness
    if($sdType_r == $sdType_d && ($sdType_r == 'avc1' || $sdType_r == 'avc2')){
        $stsd_r = $xml_r->getElementsByTagName('stsd')->item(0);
        $stsd_d = $xml_d->getElementsByTagName('stsd')->item(0);
        
        if(!nodes_equal($stsd_r, $stsd_d))
            fwrite($opfile, "###'DVB check violated: Section 5.1.2- In this case (content offered using either of the 'avc1' or 'avc2' sample entries), the Initialization Segment SHALL be common for all Representations within an Adaptation Set', not equal in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
    }
    ##
    
    ## Section 8.3 check for default_KID value
    $tenc_r = $xml_r->getElementsByTagName('tenc')->item(0);
    $tenc_d = $xml_d->getElementsByTagName('tenc')->item(0);
    
    if($tenc_r->length != 0 && $tenc_d->length != 0){
        if($tenc_r->getAttribute('default_KID') != $tenc_d->getAttribute('default_KID')){
            fwrite($opfile, "###'DVB check violated: Section 8.3- All Representations (in the same Adaptation Set) SHALL have the same value of 'default_KID' in their 'tenc' boxes in their Initialization Segments', not equal in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
            
            $vide_r = $xml_r->getElementsByTagName($hdlr_type_r.'_sampledescription')->item(0);
            $vide_d = $xml_d->getElementsByTagName($hdlr_type_r.'_sampledescription')->item(0);
            $width_r = $vide_r->getAttribute('width');
            $height_r = $vide_r->getAttribute('height');
            $width_d = $vide_r->getAttribute('width');
            $height_d = $vide_r->getAttribute('height');
            
            if(($width_r < 1280 && $height_r < 720 && $width_d >= 1280 && $height_d >= 720) || ($width_d < 1280 && $height_d < 720 && $width_r >= 1280 && $height_r >= 720))
                fwrite ($opfile, "###'DVB check violated: Section 8.3- In cases where HD and SD content are contained in one presentation and MPD, but different licence rights are given for each resolution, then they SHALL be contained in different HD and SD Adaptation Sets', but SD and HD contents are contained the same adaptation set: Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
        }
    }
    ##
    
    ## Section 10.4 check for audio switching
    if($hdlr_type_r == 'soun' && $hdlr_type_d == 'soun'){
        $MPD = $dom->getElementsByTagName('MPD')->item(0);
        $adapt = $MPD->getElementsByTagName('AdaptationSet')->item($i);
        $rep_r = $adapt->getElementsByTagName('Representation')->item($r);
        $rep_d = $adapt->getElementsByTagName('Representation')->item($d);
        
        $att_r = $rep_r->attributes;
        $att_d = $rep_d->attributes;
        if($att_r->length != $att_d->length)
            fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between audio Representations which only differ in bit rate', different attributes found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
        else{
            for($a=0; $a<$att_r->length; $a++){
                if($att_r->item($a)->name != $att_d->item($a)->name)
                    fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between audio Representations which only differ in bit rate', different attributes found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                else{
                    if($att_r->item($a)->name != 'bandwidth' && $att_r->item($a)->name != 'id'){
                        if($att_r->item($a)->value != $att_d->item($a)->value)
                            fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between audio Representations which only differ in bit rate', different attribute values found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                    }
                }
            }
        }
        
        ## Section 6.1.1 Table 3 cross-checks for audio representations
        // @mimeType
        $adapt_mime = $adapt->getAttribute('mimeType');
        $rep_mime_r = $rep_r->getAttribute('mimeType');
        $rep_mime_d = $rep_d->getAttribute('mimeType');
        if($adapt_mime == ''){
            if($rep_mime_r != $rep_mime_d)
                fwrite($opfile, "###'DVB check violated: Section 6.1.1- @mimeType attribute SHALL be common between all audio Representations in an Adaptation Set', not common in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
        }
        
        // @codecs
        $adapt_codecs = $adapt->getAttribute('codecs');
        $rep_codecs_r = $rep_r->getAttribute('codecs');
        $rep_codecs_d = $rep_d->getAttribute('codecs');
        if($adapt_codecs == ''){
            if(($rep_codecs_r != '' && $rep_codecs_d != '') && $rep_codecs_r != $rep_codecs_d)
                fwrite($opfile, "Warning for DVB check: Section 6.1.1- @codecs attribute SHOULD be common between all audio Representations in an Adaptation Set', not common in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
        }
        
        // @audioSamplingRate
        $adapt_audioSamplingRate = $adapt->getAttribute('audioSamplingRate');
        $rep_audioSamplingRate_r = $rep_r->getAttribute('audioSamplingRate');
        $rep_audioSamplingRate_d = $rep_d->getAttribute('audioSamplingRate');
        if($adapt_audioSamplingRate == ''){
            if(($rep_audioSamplingRate_r != '' && $rep_audioSamplingRate_d != '') && $rep_audioSamplingRate_r != $rep_audioSamplingRate_d)
                fwrite($opfile, "Warning for DVB check: Section 6.1.1- @audioSamplingRate attribute SHOULD be common between all audio Representations in an Adaptation Set', not common in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
        }
        
        // AudioChannelConfiguration and Role
        $adapt_audioChConf = array();
        $adapt_role = array();
        foreach($adapt->childNodes as $adapt_ch){
            if($adapt_ch->nodeName == 'AudioChannelConfiguration')
                $adapt_audioChConf[] = $adapt_ch;
            elseif($adapt_ch->nodeName == 'Role')
                $adapt_role[] = $adapt_ch;
        }
        
        if(empty($adapt_audioChConf)){
            $rep_audioChConf_r = array();
            $rep_audioChConf_d = array();
            foreach($rep_r->childNodes as $rep_r_ch){
                if($rep_r_ch->nodeName == 'AudioChannelConfiguration')
                    $rep_audioChConf_r[] = $rep_r_ch;
            }
            foreach($rep_d->childNodes as $rep_d_ch){
                if($rep_d_ch->nodeName == 'AudioChannelConfiguration')
                    $rep_audioChConf_d[] = $rep_d_ch;
            }
            
            if(!empty($rep_audioChConf_r) && !empty($rep_audioChConf_d)){
                $equal_info = '';
                if($rep_audioChConf_r->length != $rep_audioChConf_d->length)
                    fwrite($opfile, "Warning for DVB check: Section 6.1.1- AudioChannelConfiguration SHOULD be common between all audio Representations in an Adaptation Set', not common in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                else{
                    for($racc=0; $racc<$rep_audioChConf_r->length; $racc++){
                        $rep_audioChConf_r_i = $rep_audioChConf_r->item($racc);
                        $rep_audioChConf_d_i = $rep_audioChConf_d->item($racc);
                        
                        if(!nodes_equal($rep_audioChConf_r_i, $rep_audioChConf_d_i))
                            $equal_info .= 'no';
                    }
                }
                
                if($equal_info != '')
                    fwrite($opfile, "Warning for DVB check: Section 6.1.1- AudioChannelConfiguration SHOULD be common between all audio Representations in an Adaptation Set', not common in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
            }
        }
        
        if(empty($adapt_role)){
            $rep_role_r = array();
            $rep_role_d = array();
            foreach($rep_r->childNodes as $rep_r_ch){
                if($rep_r_ch->nodeName == 'Role')
                    $rep_role_r[] = $rep_r_ch;
            }
            foreach($rep_d->childNodes as $rep_d_ch){
                if($rep_d_ch->nodeName == 'Role')
                    $rep_role_d[] = $rep_d_ch;
            }
            
            if(!empty($rep_role_r) && !empty($rep_role_d)){
                $equal_info = '';
                if($rep_role_r->length != $rep_role_d->length)
                    fwrite($opfile, "###'DVB check violated: Section 6.1.1- Role element SHALL be common between all audio Representations in an Adaptation Set', not common in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                else{
                    for($rr=0; $rr<$rep_role_r->length; $rr++){
                        $rep_role_r_i = $rep_role_r->item($rr);
                        $rep_role_d_i = $rep_role_d->item($rr);
                        
                        if(!nodes_equal($rep_role_r_i, $rep_role_d_i))
                            $equal_info .= 'no';
                    }
                }
                
                if($equal_info != '')
                    fwrite($opfile, "###'DVB check violated: Section 6.1.1- Role element SHALL be common between all audio Representations in an Adaptation Set', not common in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
            }
        }
        ##
        
        ## Section 6.4 on DTS audio frame durations
        if(strpos($adapt_codecs, 'dtsc') !== FALSE || strpos($adapt_codecs, 'dtsh') !== FALSE || strpos($adapt_codecs, 'dtse') !== FALSE || strpos($adapt_codecs, 'dtsl') !== FALSE
           || ($adapt_codecs == '' && (strpos($rep_codecs_r, 'dtsc') !== FALSE || strpos($rep_codecs_r, 'dtsh') !== FALSE || strpos($rep_codecs_r, 'dtse') !== FALSE || strpos($rep_codecs_r, 'dtsl') !== FALSE) 
           && (strpos($rep_codecs_d, 'dtsc') !== FALSE || strpos($rep_codecs_d, 'dtsh') !== FALSE || strpos($rep_codecs_d, 'dtse') !== FALSE || strpos($rep_codecs_d, 'dtsl') !== FALSE))){
            $timescale_r = (int)($xml_r->getElementsByTagName('mdhd')->item(0)->getAttribute('timescale'));
            $timescale_d = (int)($xml_d->getElementsByTagName('mdhd')->item(0)->getAttribute('timescale'));
            $trun_boxes_r = $xml_r->getElementsByTagName('trun');
            $trun_boxes_d = $xml_d->getElementsByTagName('trun');
            
            if($trun_boxes_r->length == $trun_boxes_d->length){
                $trun_len = $trun_boxes_r->length;
                for($t=0; $t<$trun_len; $t++){
                    $cummulatedSampleDuration_r = (int)($trun_boxes_r->item($t)->getAttribute('cummulatedSampleDuration'));
                    $cummulatedSampleDuration_d = (int)($trun_boxes_d->item($t)->getAttribute('cummulatedSampleDuration'));
                    
                    if($cummulatedSampleDuration_r/$timescale_r != $cummulatedSampleDuration_d/$timescale_d)
                        fwrite($opfile, "###'DVB check violated: Section 6.4- the audio frame duration SHALL reamin constant for all streams within a given Adaptation Set', not common in Segment \"$t\" in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                }
            }
        }
        ##
        
        ## Adaptation Set check for consistent representations: Highlight 5.1 audio and 2.0 Audio in the same adaptation set
        $soun_r = $xml_r->getElementsByTagName('soun_sampledescription')->item(0);
        $conf_r = $soun_r->getElementsByTagName('DecoderSpecificInfo')->item(0);
        $conf_atts_r = $conf_r->attributes;
        $conf_aud_r = '';
        foreach($conf_atts_r as $conf_att_r){
            if(strpos($conf_att_r->value, 'config is') !== FALSE)
                $conf_aud_r = $conf_att_r->value;
        }
        
        $soun_d = $xml_d->getElementsByTagName('soun_sampledescription')->item(0);
        $conf_d = $soun_d->getElementsByTagName('DecoderSpecificInfo')->item(0);
        $conf_atts_d = $conf_d->attributes;
        $conf_aud_d = '';
        foreach($conf_atts_d as $conf_att_d){
            if(strpos($conf_att_d->value, 'config is') !== FALSE)
                $conf_aud_d = $conf_att_d->value;
        }
        
        if($conf_aud_r != '' && $conf_aud_d != ''){
            if(($conf_aud_r == 'config is 5+1' && $conf_aud_d == 'config is stereo') || ($conf_aud_d == 'config is 5+1' && $conf_aud_r == 'config is stereo'))
                fwrite($opfile, "Warning for DVB check: '5.1 Audio and 2.0 Audio SHOULD NOT be present within the same Adaptation Set for the presence of consistent Representations within an Adaptation Set ', found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
        }
        ##
    }
    ##
    
    ## Section 10.4 check for video switching
    if($hdlr_type_r == 'vide' && $hdlr_type_d == 'vide'){
        $MPD = $dom->getElementsByTagName('MPD')->item(0);
        $adapt = $MPD->getElementsByTagName('AdaptationSet')->item($i);
        $rep_r = $adapt->getElementsByTagName('Representation')->item($r);
        $rep_d = $adapt->getElementsByTagName('Representation')->item($d);
        
        $att_r = $rep_r->attributes;
        $att_d = $rep_d->attributes;
        if($att_r->length != $att_d->length)
            fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between video Representations', different number of attributes found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
        else{
            for($a=0; $a<$att_r->length; $a++){
                if($att_r->item($i)->name != $att_d->item($i)->name)
                    fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between video Representations', different order of attributes found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                else{
                    if($att_r->item($a)->name != 'bandwidth' && $att_r->item($a)->name != 'id' && $att_r->item($a)->name != 'frameRate' && $att_r->item($a)->name != 'width' && $att_r->item($a)->name != 'height' && $att_r->item($a)->name != 'codecs'){
                        if($att_r->item($a)->value != $att_d->item($a)->value)
                            fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between video Representations which differ only in frame rate, bit rate, profile and/or level, and resolution', different attribute values found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                    }
                }
            }
            
            // Frame rate
            $possible_fr1 = array('25', '25/1', '50', '50/1');
            $possible_fr2 = array('30/1001', '60/1001');
            $possible_fr3 = array('30', '30/1', '60', '60/1');
            $possible_fr4 = array('24', '24/1', '48', '48/1');
            $possible_fr5 = array('24/1001');
            $fr_r = $rep_r->getAttribute('frameRate');
            $fr_d = $rep_d->getAttribute('frameRate');
            if($fr_r != '' && $fr_d != ''){
                if((in_array($fr_r, $possible_fr1) && !in_array($fr_d, $possible_fr1)) || (!in_array($fr_r, $possible_fr1) && in_array($fr_d, $possible_fr1)))
                    fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between video Representations which can differ in framerate, providing the frame rate is within on of the specified families', different attribute values found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                if((in_array($fr_r, $possible_fr2) && !in_array($fr_d, $possible_fr2)) || (!in_array($fr_r, $possible_fr2) && in_array($fr_d, $possible_fr2)))
                    fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between video Representations which can differ in framerate, providing the frame rate is within on of the specified families', different attribute values found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                if((in_array($fr_r, $possible_fr3) && !in_array($fr_d, $possible_fr3)) || (!in_array($fr_r, $possible_fr3) && in_array($fr_d, $possible_fr3)))
                    fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between video Representations which can differ in framerate, providing the frame rate is within on of the specified families', different attribute values found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                if((in_array($fr_r, $possible_fr4) && !in_array($fr_d, $possible_fr4)) || (!in_array($fr_r, $possible_fr4) && in_array($fr_d, $possible_fr4)))
                    fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between video Representations which can differ in framerate, providing the frame rate is within on of the specified families', different attribute values found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                if((in_array($fr_r, $possible_fr5) && !in_array($fr_d, $possible_fr5)) || (!in_array($fr_r, $possible_fr5) && in_array($fr_d, $possible_fr5)))
                    fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between video Representations which can differ in framerate, providing the frame rate is within on of the specified families', different attribute values found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
            }
            
            // Resolution
            $width_r = $rep_r->getAttribute('width');
            $height_r = $rep_r->getAttribute('height');
            $width_d = $rep_d->getAttribute('width');
            $height_d = $rep_d->getAttribute('height');
            if($width_r != '' && $height_r != '' && $width_d != '' && $height_d != ''){
                if($adapt->getAttribute('par') != ''){
                    $par = $adapt->getAttribute('par');
                    if($width_r != $width_d || $height_r != $height_d){
                        $par_arr = explode(':', $par);
                        $par_ratio = (float)$par_arr[0] / (float)$par_arr[1];
                        
                        $par_r = $width_r/$height_r;
                        $par_d = $width_d/$height_d;
                        if($par_r != $par_d || $par_r != $par_ratio || $par_d != $par_ratio)
                            fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between video Representations which can differ in resolution, maintaining the same picture aspect ratio', different attribute values found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                    }
                }
                else{
                    $content_comps = $adapt->getElementsByTagName('ContentComponent');
                    foreach($content_comps as $content_comp){
                        $pars[] = $content_comp->getAttribute('par');
                    }
                    
                    if(count(array_unique($pars)) != 1 || count(array_unique($pars)) == 1 && in_array('', $pars))
                        fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between video Representations which can differ in resolution, maintaining the same picture aspect ratio', different attribute values found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                    
                    elseif(count(array_unique($pars)) == 1 && !in_array('', $pars)){
                        if($width_r != $width_d || $height_r != $height_d){
                            $par = $pars[0];
                            $par_arr = explode(':', $par);
                            $par_ratio = (float)$par_arr[0] / (float)$par_arr[1];
                            
                            $par_r = $width_r/$height_r;
                            $par_d = $width_d/$height_d;
                            if($par_r != $par_d || $par_r != $par_ratio || $par_d != $par_ratio)
                                fwrite($opfile, "Information on DVB conformance: Section 10.4- 'Players SHALL support seamless swicthing between video Representations which can differ in resolution, maintaining the same picture aspect ratio', different attribute values found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
                        }
                    }
                }
            }
        }
    }
    ##
}

function crossValidation_HbbTV_Representations($dom, $opfile, $xml_r, $xml_d, $i, $r, $d){
    $adapt = $dom->getElementsByTagName('AdaptationSet')->item($i);
    $rep_r = $adapt->getElementsByTagName('Representation')->item($r);
    $rep_d = $adapt->getElementsByTagName('Representation')->item($d);
    
    ## Section E.3.2 checks on Adaptation Sets
    // Second bullet on same trackID
    $tkhd_r = $xml_r->getElementsByTagName('tkhd')->item(0);
    $track_ID_r = $tkhd_r->getAttribute('trackID');
    $tfhds_r = $xml_r->getElementsByTagName('tfhd');
    
    $tkhd_d = $xml_d->getElementsByTagName('tkhd')->item(0);
    $track_ID_d = $tkhd_d->getAttribute('trackID');
    $tfhds_d = $xml_d->getElementsByTagName('tfhd');
    
    $tfhd_info = '';
    foreach($tfhds_r as $index => $tfhd_r){
        if($tfhd_r->getAttribute('trackID') != $tfhds_d->item($index)->getAttribute('trackID'))
            $tfhd_info .= ' error'; 
    }
    
    if($tfhd_info != '' || $track_ID_r != $track_ID_d)
        fwrite($opfile, "###'HbbTV check violated: Section E.3.2- All ISO BMFF Representations SHALL have the same track_ID in the track header box and track fragment header box', not equal in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
    
    // Third bullet on initialization segment identicalness
    $stsd_r = $xml_r->getElementsByTagName('stsd')->item(0);
    $stsd_d = $xml_d->getElementsByTagName('stsd')->item(0);
    
    if(!nodes_equal($stsd_r, $stsd_d))
        fwrite($opfile, "###'HbbTV check violated: Section E.3.2- Initialization Segment SHALL be common for all Representations', not equal in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
    ##
    
    $hdlr_r = $xml_r->getElementsByTagName('hdlr')->item(0);
    $hdlr_type_r = $hdlr_r->getAttribute('handler_type');
    $sdType_r = $xml_r->getElementsByTagName($hdlr_type_r.'_sampledescription')->item(0)->getAttribute('sdType');
    
    $hdlr_d = $xml_r->getElementsByTagName('hdlr')->item(0);
    $hdlr_type_d = $hdlr_d->getAttribute('handler_type');
    $sdType_d = $xml_d->getElementsByTagName($hdlr_type_d.'_sampledescription')->item(0)->getAttribute('sdType');
    
    ## Highlight HEVC and AVC for different representations in the same Adaptation Set
    if($hdlr_type_r == 'vide' && $hdlr_type_d == 'vide'){
        if((($sdType_r == 'hev1' || $sdType_r == 'hvc1') && strpos($sdType_d, 'avc')) || (($sdType_d == 'hev1' || $sdType_d == 'hvc1') && strpos($sdType_r, 'avc')))
            fwrite($opfile, "Warning for HbbTV-DVB DASH Validation Requirements check for HbbTV: Section 'Adaptation Sets' - 'Terminals cannot switch between HEVC and AVC video Represntations present in the same Adaptation Set', found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
    }
    ##
    
    ## Highlight 5.1 Audio and 2.0 Audio
    if($hdlr_type_r == 'soun' && $hdlr_type_d == 'soun'){
        $soun_r = $xml_r->getElementsByTagName('soun_sampledescription')->item(0);
        $conf_r = $soun_r->getElementsByTagName('DecoderSpecificInfo')->item(0);
        $conf_atts_r = $conf_r->attributes;
        $conf_aud_r = '';
        foreach($conf_atts_r as $conf_att_r){
            if(strpos($conf_att_r->value, 'config is') !== FALSE)
                $conf_aud_r = $conf_att_r->value;
        }
        
        $soun_d = $xml_d->getElementsByTagName('soun_sampledescription')->item(0);
        $conf_d = $soun_d->getElementsByTagName('DecoderSpecificInfo')->item(0);
        $conf_atts_d = $conf_d->attributes;
        $conf_aud_d = '';
        foreach($conf_atts_d as $conf_att_d){
            if(strpos($conf_att_d->value, 'config is') !== FALSE)
                $conf_aud_d = $conf_att_d->value;
        }
        
        if($conf_aud_r != '' && $conf_aud_d != ''){
            if(($conf_aud_r == 'config is 5+1' && $conf_aud_d == 'config is stereo') || ($conf_aud_d == 'config is 5+1' && $conf_aud_r == 'config is stereo'))
                fwrite($opfile, "Warning for HbbTV-DVB DASH Validation Requirements check for HbbTV: Section 'Adaptation Sets' - '5.1 Audio and 2.0 Audio SHOULD NOT be present within the same Adaptation Set for the presence of consistent Representations within an Adaptation Set ', found in Adaptation Set " . ($i+1) . ": Representation " . ($r+1) . " and Representation " . ($d+1) . ".\n");
        }
    }
    ##
}

// Check if the nodes and their descendandts are the same
function nodes_equal($node_1, $node_2){
    $equal = true;
    foreach($node_1->childNodes as $index => $ch_1){
        $ch_2 = $node_2->childNodes->item($index);
        
        if($ch_1->nodeType == XML_ELEMENT_NODE && $ch_2->nodeType == XML_ELEMENT_NODE){
            if($ch_1->nodeName != $ch_2->nodeName){
                $equal = false;
                break;
            }
           
            $atts_1 = $ch_1->attributes;
            $atts_2 = $ch_2->attributes;
            if($atts_1->length != $atts_2->length){
                $equal = false;
                break;
            }
            for($i=0; $i<$atts_1->length; $i++){
                if($atts_1->item($i)->name != $atts_2->item($i)->name || $atts_1->item($i)->value != $atts_2->item($i)->value){
                    $equal = false;
                    break;
                }
            }
            
            $equal = nodes_equal($ch_1, $ch_2);
            if($equal == false)
                break;
        }
    }
    
    return $equal;
}

function common_validation($dom,$hbbtv,$dvb, $sizearray,$bandwidth, $pstart, $media_types){
    global $presentationduration, $locate, $count1, $count2, $Adapt_arr;

    if(!($opfile = fopen($locate."/Adapt".$count1."rep".$count2."log.txt", 'a'))){
        echo "Error opening/creating HbbTV/DVB codec validation file: "."/Adapt".$count1."rep".$count2."log.txt";
        return;
    }
    
    $xml_rep = xmlFileLoad($locate.'/Adapt'.$count1.'/Adapt'.$count1.'rep'.$count2.'.xml');
    if($xml_rep != 0){
        if($dvb){
            common_validation_DVB($opfile, $dom, $xml_rep, $count1, $count2, $sizearray, $media_types);
        }
        if($hbbtv){
            common_validation_HbbTV($opfile, $dom, $xml_rep, $count1, $count2);
        } 
        seg_timing_common($opfile, $xml_rep, $dom, $pstart);
        
        //seg_bitrate_common($opfile,$xml_rep);
        bitrate_report($opfile, $dom, $xml_rep, $count1, $count2, $sizearray,$bandwidth);
        seg_duration_checks($dom, $count1, $count2, $opfile);        
        if ($presentationduration !== "") {
            $checks = segmentToPeriodDurationCheck($xml_rep);
            if(!$checks[0]){
                fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated: Section 'Periods' - The accumulated duration of the segments [".$checks[1]. "seconds] in the representation does not match the period duration[".$checks[2]."seconds].\n'");
            }
        }
        //DVB_HbbTV_err_file_op();
    }
}

function common_validation_DVB($opfile, $dom, $xml_rep, $adapt_count, $rep_count, $sizearray, $media_types){
    global $profiles, $locate, $c;
    
    $adapt = $dom->getElementsByTagName('AdaptationSet')->item($adapt_count);
    $rep = $adapt->getElementsByTagName('Representation')->item($rep_count);
    
    ## Report on any resolutions used that are not in the tables of resoultions in 10.3 of the DVB DASH specification
    $res_result = resolutionCheck($opfile, $adapt, $rep);
    if($res_result[0] == false)
        fwrite ($opfile, "Information on HbbTV-DVB DASH Validation Requirements conformance for DVB: Section 'Codec information' - Resolution value \"" . $res_result[1] . 'x' . $res_result[2] . "\" is not in the table of resolutions in 10.3 of the DVB DASH specification.\n");
    ##
    
    ## Check on the support of the provided codec
    // MPD part
    $codecs = $adapt->getAttribute('codecs');
    if($codecs == ''){
        $codecs = $rep->getAttribute('codecs');
    }
    
    if($codecs != ''){
        $codecs_arr = explode(',', $codecs);
        
        $str_info = '';
        foreach($codecs_arr as $codec){
            if(strpos($codec, 'avc') === FALSE && strpos($codec, 'hev1') === FALSE && strpos($codec, 'hvc1') === FALSE && 
                strpos($codec, 'mp4a') === FALSE && strpos($codec, 'ec-3') === FALSE && strpos($codec, 'ac-4') === FALSE &&
                strpos($codec, 'dtsc') === FALSE && strpos($codec, 'dtsh') === FALSE && strpos($codec, 'dtse') === FALSE && strpos($codec, 'dtsl') === FALSE &&
                strpos($codec, 'stpp') === FALSE){
                
                $str_info .= "$codec "; 
            }
        }
        
        if($str_info != '')
            fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Codec information' - @codecs in the MPD is not supported by the specification', found $str_info.\n");
    }
    
    // Segment part
    $hdlr_type = $xml_rep->getElementsByTagName('hdlr')->item(0)->getAttribute('handler_type');
    $sdType = $xml_rep->getElementsByTagName("$hdlr_type".'_sampledescription')->item(0)->getAttribute('sdType');
    
    if(strpos($sdType, 'avc') === FALSE && strpos($sdType, 'hev1') === FALSE && strpos($sdType, 'hvc1') === FALSE && 
       strpos($sdType, 'mp4a') === FALSE && strpos($sdType, 'ec-3') === FALSE && strpos($sdType, 'ac-4') === FALSE &&
       strpos($sdType, 'dtsc') === FALSE && strpos($sdType, 'dtsh') === FALSE && strpos($sdType, 'dtse') === FALSE && strpos($sdType, 'dtsl') === FALSE &&
       strpos($sdType, 'stpp') === FALSE &&
       strpos($sdType, 'enc') === FALSE)
        fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Codec information' - codec in the Segment is not supported by the specification', found $sdType.\n");
    
    $original_format = '';
    if(strpos($sdType, 'enc') !== FALSE){
        $sinf_boxes = $xml_rep->getElementsByTagName('sinf');
        if($sinf_boxes->length != 0){
            $original_format = $sinf_boxes->item(0)->getElementsByTagName('frma')->item(0)->getAttribute('original_format');
        }
    }
    
    if(strpos($sdType, 'avc') !== FALSE || strpos($original_format, 'avc') !== FALSE){
        $nal_units = $xml_rep->getElementsByTagName('NALUnit');
        foreach($nal_units as $nal_unit){
            if($nal_unit->getAttribute('nal_type') == '0x07'){
                if($nal_unit->getAttribute('profile_idc') != 100)
                    fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Codec information' - profile used for the AVC codec in Segment is not supported by the specification Section 5.1.1', found " . $nal_unit->getAttribute('profile_idc') . ".\n");
            
                $level_idc = $nal_unit->getElementsByTagName('comment')->item(0)->getAttribute('level_idc');
                if($level_idc != 30 && $level_idc != 31 && $level_idc != 32 && $level_idc != 40)
                    fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Codec information' - level used for the AVC codec in Segment is not supported by the specification Section 5.1.1', found $level_idc.\n");
            }
        }
    }
    elseif(strpos($sdType, 'hev1') !== FALSE || strpos($sdType, 'hvc1') !== FALSE || strpos($original_format, 'hev1') !== FALSE || strpos($original_format, 'hvc1') !== FALSE){
        $width = $xml_rep->getElementsByTagName("$hdlr_type".'_sampledescription')->item(0)->getAttribute('width');
        $height = $xml_rep->getElementsByTagName("$hdlr_type".'_sampledescription')->item(0)->getAttribute('height');
        $nal_units = $xml_rep->getElementsByTagName('NALUnit');
        foreach($nal_units as $nal_unit){
            $nalUnitType = $nal_unit->parentNode->getAttribute('nalUnitType');
            if($nalUnitType == '33'){
                if($nal_unit->getAttribute('gen_tier_flag') != '0')
                    fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Codec information' - tier used for the HEVC codec in Segment is not supported by the specification Section 5.2.3', found " . $nal_unit->getAttribute('gen_tier_flag') . ".\n");
                if($nal_unit->getAttribute('bit_depth_luma_minus8') != 0 && $nal_unit->getAttribute('bit_depth_luma_minus8') != 2)
                    fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Codec information' - bit depth used for the HEVC codec in Segment is not supported by the specification Section 5.2.3', found " . $nal_unit->getAttribute('bit_depth_luma_minus8') . ".\n");
                
                if((int)$width <= 1920 && (int)$height <= 1080){
                    if($nal_unit->getAttribute('gen_profile_idc') != '1' && $nal_unit->getAttribute('gen_profile_idc') != '2')
                        fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Codec information' - profile used for the HEVC codec in Segment is not supported by the specification Section 5.2.3', found " . $nal_unit->getAttribute('gen_profile_idc') . ".\n");
                    if((int)($nal_unit->getAttribute('sps_max_sub_layers_minus1')) == 0 && (int)($nal_unit->getAttribute('gen_level_idc')) > 123)
                        fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Codec information' - level used for the HEVC codec in Segment is not supported by the specification Section 5.2.3', found " . $nal_unit->getAttribute('gen_level_idc') . ".\n");
                }
                elseif((int)$width > 1920 && (int)$height > 1080){
                    if($nal_unit->getAttribute('gen_profile_idc') != '2')
                        fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Codec information' - profile used for the HEVC codec in Segment is not supported by the specification Section 5.2.3', found " . $nal_unit->getAttribute('gen_profile_idc') . ".\n");
                    if((int)($nal_unit->getAttribute('sps_max_sub_layers_minus1')) == 0 && (int)($nal_unit->getAttribute('gen_level_idc')) > 153)
                        fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Codec information' - level used for the HEVC codec in Segment is not supported by the specification Section 5.2.3', found " . $nal_unit->getAttribute('gen_level_idc') . ".\n");
                }
            }
        }
    }
    ##
    
    ## Subtitle checks
    if($adapt->getAttribute('mimeType') == 'application/mp4' || $rep->getAttribute('mimeType') == 'application/mp4'){
        if($adapt->getAttribute('codecs') == 'stpp' || $rep->getAttribute('codecs') == 'stpp'){
            if($hdlr_type != 'subt')
                fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Subtitles' - For subtitle media, handler type in the Initialization Segment SHALL be \"subt\"', found \"$hdlr_type\".\n");
            
            $stpp = $xml_rep->getElementsByTagName('stpp');
            if($stpp->length == 0)
                fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Subtitles' - For subtitle media, sample entry type SHALL be \"stpp (XMLSubtitleSampleEntry)\"', stpp not found.\n");
            else{
                if($stpp->item(0)->getAttribute('namespace') == '')
                    fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Subtitles' - For subtitle media, namespaces SHALL be listed in the sample entry', namespace not found.\n");
            }
            
            ## EBU TECH 3381 - Section 5 - Layout check
            if(in_array('video', $media_types)){
                $tkhd = $xml_rep->getElementsByTagName('tkhd')->item(0);
                if((int)($tkhd->getAttribute('width')) != 0 || (int)($tkhd->getAttribute('height')) != 0)
                    fwrite($opfile, "Warning for HbbTV-DVB DASH Validation Requirements check for DVB: Section 'Subtitles' - EBU TECH 3381 Section 5- When the subtitle track is associated with a video object the width and height of the subtitle track SHOULD NOT be set', found width and/or height value set.\n");
            }
            ##
            
            ## Check the timing of segments and the EBU-TT-D files
            // EBU-TT-D
            $meta_str = '';
            $subt_times = array();
            $files = glob($locate.'/Adapt'.$adapt_count.'rep'.$rep_count.'/Subtitles/*');
            natsort($files);
            foreach($files as $file){
                $file_loaded = simplexml_load_file($file);
                if($file_loaded){
                    $dom_abs = dom_import_simplexml($file_loaded);
                    $abs = new DOMDocument('1.0');
                    $dom_abs = $abs->importNode($dom_abs, true);
                    $dom_abs = $abs->appendChild($dom_abs);
                    $abs = $abs->getElementsByTagName('subtitle')->item(0);
                    
                    $tts = $abs = $abs->getElementsByTagName('tt');
                    $begin = '';
                    foreach($tts as $tt){
                        ##Check if metadata present; if yes, check if the profile is other than EBU-TT-D
                        if($tt->getElementsByTagName('metadata')->length != 0){
                            $metadata_children = $tt->getElementsByTagName('metadata')->item(0)->childNodes;
                            foreach($metadata_children as $metadata_child){
                                if($metadata_child->nodeType == XML_ELEMENT_NODE){
                                    if(strpos($metadata_child->nodeName, 'ebutt') === FALSE)
                                        $meta_str .= 'no '; 
                                }
                            }
                        }
                        ##
                        
                        $body = $tt->getElementsByTagName('body')->item(0);
                        $divs = $body->getElementsByTagName('div');
                        foreach($divs as $div){
                            $ps = $div->getElementsByTagName('p');
                            foreach($ps as $p){
                                $h_m_s_begin = $p->getAttribute('begin');
                                $h_m_s = explode(':', $h_m_s_begin);
                                $begin .= ' ' . (string)($h_m_s[0]*60*60 + $h_m_s[1]*60 + $h_m_s[2]);
                            }
                        }
                    }
                    
                    $subt_times[] = $begin;
                }
            }
            
            if($meta_str != '')
                fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Subtitles' - Subtitle segments do not contain ISO-BMFF packaged EBU-TT-D but some other profile.\n");
            
            
            // Segments
            $type = $dom->getElementsByTagName('MPD')->item(0)->getAttribute('type');
            $xml_num_moofs=$xml_rep->getElementsByTagName('moof')->length;
            $xml_trun=$xml_rep->getElementsByTagName('trun');
            $xml_tfdt=$xml_rep->getElementsByTagName('tfdt');
            
            $sidx_boxes = $xml_rep->getElementsByTagName('sidx');
            $subsegment_signaling = array();
            if($sidx_boxes->length != 0){
                foreach($sidx_boxes as $sidx_box){
                    $subsegment_signaling[] = (int)($sidx_box->getAttribute('referenceCount'));
                }
            }
            
            $xml_elst = $xml_rep->getElementsByTagName('elst');
            if($xml_elst->length == 0)
                $mediaTime = 0;
            else
                $mediaTime = (int)($xml_elst->item(0)->getElementsByTagName('elstEntry')->item(0)->getAttribute('mediaTime'));
            
            if($type != 'dynamic'){
                $timescale=$xml_rep->getElementsByTagName('mdhd')->item(0)->getAttribute('timescale');
                $sidx_index = 0;
                $cum_subsegDur=0;
                $s=0;
                for($j=0;$j<$xml_num_moofs;$j++){
                    if(empty($subsegment_signaling)){
                        $cum_subsegDur += (($xml_trun->item($j)->getAttribute('cummulatedSampleDuration'))/$timescale);
                        
                        $subt_begin = explode(' ', $subt_times[$j]);
                        for($be=1; $be<sizeof($subt_begin); $be++){
                            if($subt_begin[$be] > $cum_subsegDur)
                                fwrite($opfile, "Warning for HbbTV-DVB DASH Validation Requirements check for DVB: Section 'Subtitles' - 'For subtitle media, timing of subtitle $be with start \"" . $subt_begin[$be] . "\" lies completely outside the segment time period of the segment $j.\n");
                        }
                    }
                    else{
                        $ref_count = 1;
                        if($sidx_index < sizeof($subsegment_signaling))
                            $ref_count = $subsegment_signaling[$sidx_index];
                        
                        $cum_subsegDur += (($xml_trun->item($j)->getAttribute('cummulatedSampleDuration'))/$timescale);
                        $subsegment_signaling[$sidx_index] = $ref_count - 1;
                        if($subsegment_signaling[$sidx_index] == 0){
                            while($s <= $j){
                                $subt_begin = explode(' ', $subt_times[$s]);
                                for($be=1; $be<sizeof($subt_begin); $be++){
                                    if($subt_begin[$be] > $cum_subsegDur)
                                        fwrite($opfile, "Warning for HbbTV-DVB DASH Validation Requirements check for DVB: Section 'Subtitles' - 'For subtitle media, timing of subtitle $s with start \"" . $subt_begin[$be] . "\" lies completely outside the segment time period of the segment.\n");
                                }
                                $s++;
                            }
                            
                            $sidx_index++;
                        }
                    }
                }
            }
            ##
        }
    }
    
    ## Segment checks
    $moof_boxes = $xml_rep->getElementsByTagName('moof');
    // Section 4.3 on on-demand profile periods containing sidx boxes
    if(strpos($profiles, 'urn:mpeg:dash:profile:isoff-on-demand:2011') !== FALSE || strpos($profiles, 'urn:dvb:dash:profile:dvb-dash:isoff-ext-on-demand:2014') !== FALSE){
        if($xml_rep->getElementsByTagName('sidx')->length != 1)
            fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for DVB: Section 'Segments' - 'Segment includes features that are not required by the profile being validated against', found ". $xml_rep->getElementsByTagName('sidx')->length ." sidx boxes while according to Section 4.3 \"(For On Demand profile) The segment SHALL contain only one single Segment Index box ('sidx) for the entire segment\"'.\n");
        if(count(glob($locate.'/Adapt'.$adapt_count.'rep'.$rep_count.'/*')) - count(glob($locate.'/Adapt'.$adapt_count.'rep'.$rep_count.'/*', GLOB_ONLYDIR)) != 1)
            fwrite($opfile, "###'DVB check violated: Section 4.3- (For On Demand profile) Each Representation SHALL have only one Segment', found more.\n");
    }
    
    // Section 4.3 on traf box count in moof boxes
    foreach($moof_boxes as $moof_box){
        if($moof_box->getElementsByTagName('traf')->length != 1)
            fwrite($opfile, "###'DVB check violated: Section 4.3- The movie fragment box ('moof') SHALL contain only one track fragment box ('traf')', found more than one.\n");
    }
    
    // Section 4.5 on segment and subsegment durations
    $sidx_boxes = $xml_rep->getElementsByTagName('sidx');
    $subsegment_signaling = array();
    if($sidx_boxes->length != 0){
        foreach($sidx_boxes as $sidx_box){
            $subsegment_signaling[] = (int)($sidx_box->getAttribute('referenceCount'));
        }
    }
    
    $timescale=$xml_rep->getElementsByTagName('mdhd')->item(0)->getAttribute('timescale');
    $num_moofs=$moof_boxes->length;
    $sidx_index = 0;
    $cum_subsegDur=0;
    for($j=0;$j<$num_moofs-1;$j++){
        $cummulatedSampleDuration=$xml_rep->getElementsByTagName('trun')->item($j)->getAttribute('cummulatedSampleDuration');
        $segDur=$cummulatedSampleDuration/$timescale;
        
        if(empty($subsegment_signaling) || (!empty($subsegment_signaling) && sizeof(array_unique($subsegment_signaling)) == 1 && in_array(0, $subsegment_signaling))){
            if($hdlr_type =='vide' && $segDur>15)
                fwrite($opfile, "###'DVB check violated Section 4.5: Where subsegments are not signalled, each video segment SHALL have a duration of not more than 15 seconds', segment ".($j+1)." found with duration ".$segDur." \n");
            if($hdlr_type =='soun' && $segDur>15)
                fwrite($opfile, "###'DVB check violated Section 4.5: Where subsegments are not signalled, each audio segment SHALL have a duration of not more than 15 seconds', segment ".($j+1)." found with duration ".$segDur." \n");
            
            if($segDur <1)
                fwrite($opfile, "###'DVB check violated Section 4.5: Segment duration SHALL be at least 1 second except for the last segment of a Period', segment ".($j+1)." found with duration ".$segDur." \n");
        }
        elseif(!empty($subsegment_signaling) && !in_array(0, $subsegment_signaling)){
            $ref_count = $subsegment_signaling[$sidx_index];
            $cum_subsegDur += $segDur;
            if($hdlr_type =='vide' && $segDur>15)
                fwrite($opfile, "###'DVB check violated Section 4.5: Each video subsegment SHALL have a duration of not more than 15 seconds', subsegment ".($j+1)." found with duration ".$segDur." \n");
            if($hdlr_type =='soun' && $segDur>15)
                fwrite($opfile, "###'DVB check violated Section 4.5: Each audio subsegment SHALL have a duration of not more than 15 seconds', subsegment ".($j+1)." found with duration ".$segDur." \n");
            
            $subsegment_signaling[$sidx_index] = $ref_count - 1;
            if($subsegment_signaling[$sidx_index] == 0){
                if($cum_subsegDur < 1)
                    fwrite($opfile, "###'DVB check violated Section 4.5: Segment duration SHALL be at least 1 second except for the last segment of a Period', segment ".($j+1)." found with duration ".$segDur." \n");
                
                $sidx_index++;
                $cum_subsegDur = 0;
            }
            
            // Section 5.1.2 on AVC content's SAP type
            if($hdlr_type == 'vide' && strpos($sdType, 'avc') !== FALSE){
                if($sidx_boxes->length != 0){
                    $subseg = $sidx_boxes[$sidx_index]->getElementsByTagName('subsegment')->item(0);
                    if($subseg != NULL && $subseg->getAttribute('starts_with_SAP') == '1'){
                        $sap_type = $subseg->getAttribute('SAP_type');
                        if($sap_type != '1' && $sap_type != '2')
                            fwrite($opfile, "###'DVB check violated: Section 5.1.2- Segments SHALL start with SAP types of 1 or 2', found $sap_type.\n");
                    }
                }
            }
            //
        }
        else{
            $ref_count = $subsegment_signaling[$sidx_index];
            if($ref_count == 0){
                if($hdlr_type =='vide' && $segDur>15)
                    fwrite($opfile, "###'DVB check violated Section 4.5: Where subsegments are not signalled, each video segment SHALL have a duration of not more than 15 seconds', segment ".($j+1)." found with duration ".$segDur." \n");
                if($hdlr_type =='soun' && $segDur>15)
                    fwrite($opfile, "###'DVB check violated Section 4.5: Where subsegments are not signalled, each audio segment SHALL have a duration of not more than 15 seconds', segment ".($j+1)." found with duration ".$segDur." \n");
                
                if($segDur <1)
                    fwrite($opfile, "###'DVB check violated Section 4.5: Segment duration SHALL be at least 1 second except for the last segment of a Period', segment ".($j+1)." found with duration ".$segDur." \n");
                
                $sidx_index++;
            }
            else{
                $subsegment_signaling[$sidx_index] = $ref_count - 1;
                $cum_subsegDur += $segDur;
                if($hdlr_type =='vide' && $segDur>15)
                    fwrite($opfile, "###'DVB check violated Section 4.5: Each video subsegment SHALL have a duration of not more than 15 seconds', subsegment ".($j+1)." found with duration ".$segDur." \n");
                if($hdlr_type =='soun' && $segDur>15)
                    fwrite($opfile, "###'DVB check violated Section 4.5: Each audio subsegment SHALL have a duration of not more than 15 seconds', subsegment ".($j+1)." found with duration ".$segDur." \n");
                
                if($subsegment_signaling[$sidx_index] == 0){
                    $sidx_index++;
                    if($cum_subsegDur < 1)
                        fwrite($opfile, "###'DVB check violated Section 4.5: Segment duration SHALL be at least 1 second except for the last segment of a Period', segment ".($j+1)." found with duration ".$segDur." \n");
                    
                    $cum_subsegDur = 0;
                }
                
                // Section 5.1.2 on AVC content's SAP type
                if($hdlr_type == 'vide' && strpos($sdType, 'avc') !== FALSE){
                    if($sidx_boxes->length != 0){
                        $subseg = $sidx_boxes[$sidx_index]->getElementsByTagName('subsegment')->item(0);
                        if($subseg != NULL && $subseg->getAttribute('starts_with_SAP') == '1'){
                            $sap_type = $subseg->getAttribute('SAP_type');
                            if($sap_type != '1' && $sap_type != '2')
                                fwrite($opfile, "###'DVB check violated: Section 5.1.2- Segments SHALL start with SAP types of 1 or 2', found $sap_type.\n");
                        }
                    }
                }
                //
            }
        }
        
        // Section 6.2 on HE_AACv2 and 6.5 on MPEG Surround audio content's SAP type
        if($hdlr_type == 'soun' && strpos($sdType, 'mp4a') !== FALSE){
            if($sidx_boxes->length != 0){
                $subsegments = $sidx_boxes[$sidx_index]->getElementsByTagName('subsegment');
                if($subsegments->length != 0){
                    foreach($subsegments as $subsegment){
                        if($subsegment->getAttribute('starts_with_SAP') == '1'){
                            $sap_type = $subsegment->getAttribute('SAP_type');
                            if($sap_type != '1')
                                fwrite($opfile, "###'DVB check violated: Section 6.2/6.5- The content preparation SHALL ensure that each (Sub)Segment starts with a SAP type 1', found $sap_type.\n");
                        }
                    }
                }
            }
        }
        //
    }
    ##
    
    // Section 5.1.2 on AVC content's sample entry type
    if($hdlr_type == 'vide' && strpos($sdType, 'avc') !== FALSE){
        if($sdType != 'avc3' && $sdType != 'avc4')
            fwrite($opfile, "Warning for DVB check: Section 5.1.2- 'Content SHOULD be offered using Inband storage for SPS/PPS i.e. sample entries 'avc3' and 'avc4'.', found $sdType.\n");
        
        $vide_sd = $xml_rep->getElementsByTagName("$hdlr_type".'_sampledescription')->item(0);
        $nal_units = $vide_sd->getElementsByTagName('NALUnit');
        $sps_found = false;
        $pps_found = false;
        foreach($nal_units as $nal_unit){
            if($nal_unit->getAttribute('nal_type') == '0x07')
                $sps_found = true;
            if($nal_unit->getAttribute('nal_type') == '0x08')
                $pps_found = true;
        }
        
        if($sdType != 'avc3'){ // in AVC3 this data goes in the first sample of every fragment (i.e. the first sample in each mdat box).
            if(!$sps_found)
                fwrite($opfile, "###'DVB check violated: Section 5.1.2- All information necessary to decode any Segment chosen from the Representation SHALL be provided in the initialization Segment', SPS not found.\n");
            if(!$pps_found)
                fwrite($opfile, "###'DVB check violated: Section 5.1.2- All information necessary to decode any Segment chosen from the Representation SHALL be provided in the initialization Segment', PPS not found.\n");
        }
    }
    
    // Section 4.5 on subtitle segment sizes
    if($hdlr_type == 'subt'){
        $segsize_info = '';
        foreach($sizearray as $segsize){
            if($segsize > 512*1024)
                $segsize_info .= 'large ';
        }
        if($segsize_info != '')
            fwrite($opfile, "###'DVB check violated: Section 4.5- Subtitle segments SHALL have a maximum segment size of 512KB', found larger segment size.\n");
    }
}

function common_validation_HbbTV($opfile, $dom, $xml_rep, $adapt_count, $rep_count){
    $adapt = $dom->getElementsByTagName('AdaptationSet')->item($adapt_count);
    $rep = $adapt->getElementsByTagName('Representation')->item($rep_count);
    
    ## Check on the support of the provided codec
    // MPD part
    $codecs = $adapt->getAttribute('codecs');
    if($codecs == ''){
        $codecs = $rep->getAttribute('codecs');
    }
    
    if($codecs != ''){
        $codecs_arr = explode(',', $codecs);
        
        $str_info = '';
        foreach($codecs_arr as $codec){
            if(strpos($codec, 'avc') === FALSE &&
                strpos($codec, 'mp4a') === FALSE && strpos($codec, 'ec-3')){
                
                $str_info .= "$codec "; 
            }
        }
        
        if($str_info != '')
            fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for HbbTV: Section 'Codec information' - @codecs in the MPD is not supported by the specification', found $str_info.\n");
    }
    
    // Segment part
    $hdlr_type = $xml_rep->getElementsByTagName('hdlr')->item(0)->getAttribute('handler_type');
    $sdType = $xml_rep->getElementsByTagName("$hdlr_type".'_sampledescription')->item(0)->getAttribute('sdType');
    
    if($hdlr_type=='vide' || $hdlr_type=='soun'){
    if(strpos($sdType, 'avc') === FALSE && 
       strpos($sdType, 'mp4a') === FALSE && strpos($sdType, 'ec-3') === FALSE &&
       strpos($sdType, 'enc') === FALSE)
        fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for HbbTV: Section 'Codec information' - codec in Segment is not supported by the specification', found $sdType.\n");
    }
    
    $original_format = '';
    if(strpos($sdType, 'enc') !== FALSE){
        $sinf_boxes = $xml_rep->getElementsByTagName('sinf');
        if($sinf_boxes->length != 0){
            $original_format = $sinf_boxes->item(0)->getElementsByTagName('frma')->item(0)->getAttribute('original_format');
        }
    }
    
    if(strpos($sdType, 'avc') !== FALSE || strpos($original_format, 'avc') !== FALSE){
        $width = $xml_rep->getElementsByTagName("$hdlr_type".'_sampledescription')->item(0)->getAttribute('width');
        $height = $xml_rep->getElementsByTagName("$hdlr_type".'_sampledescription')->item(0)->getAttribute('height');
        $nal_units = $xml_rep->getElementsByTagName('NALUnit');
        foreach($nal_units as $nal_unit){
            if($nal_unit->getAttribute('nal_type') == '0x07'){
                if((int)$width <= 720 && (int)$height <= 576){
                    if($nal_unit->getAttribute('profile_idc') != 77 && $nal_unit->getAttribute('profile_idc') != 100)
                        fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for HbbTV: Section 'Codec information' - profile used for the codec in Segment is not supported by the specification Section 7.3.1', found " . $nal_unit->getAttribute('profile_idc') . ".\n");
                    
                    $level_idc = $nal_unit->getElementsByTagName('comment')->item(0)->getAttribute('level_idc');
                    if($level_idc != 30)
                        fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for HbbTV: Section 'Codec information' - level used for the codec in Segment is not supported by the specification Section 7.3.1', found $level_idc.\n");
                }
                elseif((int)$width >= 720 && (int)$height >= 640){
                    if($nal_unit->getAttribute('profile_idc') != 100)
                        fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for HbbTV: Section 'Codec information' - profile used for the codec in Segment is not supported by the specification Section 7.3.1', found " . $nal_unit->getAttribute('profile_idc') . ".\n");
                    
                    $level_idc = $nal_unit->getElementsByTagName('comment')->item(0)->getAttribute('level_idc');
                    if($level_idc != 30 && $level_idc != 31 && $level_idc != 32 && $level_idc != 40)
                        fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for HbbTV: Section 'Codec information' - level used for the codec in Segment is not supported by the specification Section 7.3.1', found $level_idc.\n");
                }
            }
        }
    }
    ##
    ##Segment checks.
    $stsd = $xml_rep->getElementsByTagName('stsd')->item(0);
    $vide_sample=$stsd->getElementsByTagName('vide_sampledescription');
    $soun_sample=$stsd->getElementsByTagName('soun_sampledescription');
    if($vide_sample->length>0 && $soun_sample->length>0)
        fwrite($opfile, "###'HbbTV check violated Section E.2.3: Each Representation shall contain only one media component', found both video and audio samples\n");

    if($hdlr_type =='vide')
    {
        $avcC = $xml_rep->getElementsByTagName('avcC');
        if($avcC->length>0)
        {
            $nals=$xml_rep->getElementsByTagName('NALUnit');
            foreach($nals as $nal_unit)
            {
                if($nal_unit->getAttribute('nal_type') =='0x07')
                    $sps_found=1;
                if($nal_unit->getAttribute('nal_type') =='0x08')
                    $pps_found=1;
            }
            if($sdType != 'avc3'){
                if($sps_found!=1)
                    fwrite($opfile, "###'HbbTV check violated Section E.2.3: All info necessary to decode any Segment shall be provided in Initialization Segment', for AVC video, Sequence parameter set not found\n");
                if($pps_found!=1)
                    fwrite($opfile, "###'HbbTV check violated Section E.2.3: All info necessary to decode any Segment shall be provided in Initialization Segment', for AVC video, Picture parameter set not found \n");
            }
        }
        else{
            if($sdType != 'avc3')
                fwrite($opfile, "###'HbbTV check violated Section E.2.3: All info necessary to decode any Segment shall be provided in Initialization Segment', for video, AVC decoder config record not found \n");
        }
    }
    else if($hdlr_type =='soun'){
        $soun_sample=$xml_rep->getElementsByTagName('soun_sampledescription');
        $sdType=$soun_sample->item(0)->getAttribute('sdType');
        $samplingRate=$soun_sample->item(0)->getAttribute('sampleRate');    
        $xml_audioDec=$xml_rep->getElementsByTagName('DecoderSpecificInfo');
        if($xml_audioDec->length>0)
           $channelConfig=$xml_audioDec->item(0)->getAttribute('channelConfig');
        if($sdType==NULL  )
            fwrite($opfile, "###'HbbTV check violated Section E.2.3: All info necessary to decode any Segment shall be provided in Initialization Segment', for audio, sample description type not found \n");
        if($samplingRate==NULL)
            fwrite($opfile, "###'HbbTV check violated Section E.2.3: All info necessary to decode any Segment shall be provided in Initialization Segment', for audio, sampling rate not found \n");
        if($channelConfig==NULL)
            fwrite($opfile, "###'HbbTV check violated Section E.2.3: All info necessary to decode any Segment shall be provided in Initialization Segment', for audio, channel config in decoder specific info not found \n");

    }
    
    // Segment duration except the last one shall be at least one second
    $sidx_boxes = $xml_rep->getElementsByTagName('sidx');
    $subsegment_signaling = array();
    if($sidx_boxes->length != 0){
        foreach($sidx_boxes as $sidx_box){
            $subsegment_signaling[] = (int)($sidx_box->getAttribute('referenceCount'));
        }
    }
    
    $timescale=$xml_rep->getElementsByTagName('mdhd')->item(0)->getAttribute('timescale');
    $moof_boxes = $xml_rep->getElementsByTagName('moof');
    $num_moofs=$moof_boxes->length;
    $sidx_index = 0;
    $cum_subsegDur=0;
    for($j=0;$j<$num_moofs-1;$j++){
        $cummulatedSampleDuration=$xml_rep->getElementsByTagName('trun')->item($j)->getAttribute('cummulatedSampleDuration');
        $segDur=$cummulatedSampleDuration/$timescale;
        
        if(empty($subsegment_signaling) || (!empty($subsegment_signaling) && sizeof(array_unique($subsegment_signaling)) == 1 && in_array(0, $subsegment_signaling))){
            if($hdlr_type =='vide' && $segDur>15)
                fwrite($opfile, "###'HbbTV check violated Section E.2.3: Each video segment shall have a duration of not more than 15s', segment ".($j+1)." found with duration ".$segDur." \n");
            if($hdlr_type =='soun' && $segDur>15)
                fwrite($opfile, "###'HbbTV check violated Section E.2.3: Each audio segment shall have a duration of not more than 15s', segment ".($j+1)." found with duration ".$segDur." \n");
            
            if($segDur <1)
                fwrite($opfile, "###'HbbTV check violated Section E.2.3: Segments shall be at least 1s long except last segment of Period', segment ".($j+1)." found with duration ".$segDur." \n");
        }
        elseif(!empty($subsegment_signaling) && !in_array(0, $subsegment_signaling)){
            $ref_count = $subsegment_signaling[$sidx_index];
            $cum_subsegDur += $segDur;
            if($hdlr_type =='vide' && $segDur>15)
                fwrite($opfile, "###'HbbTV check violated Section E.2.3: Each video segment shall have a duration of not more than 15s', segment ".($j+1)." found with duration ".$segDur." \n");
            if($hdlr_type =='soun' && $segDur>15)
                fwrite($opfile, "###'HbbTV check violated Section E.2.3: Each audio segment shall have a duration of not more than 15s', segment ".($j+1)." found with duration ".$segDur." \n");
            
            $subsegment_signaling[$sidx_index] = $ref_count - 1;
            if($subsegment_signaling[$sidx_index] == 0){
                if($cum_subsegDur < 1)
                    fwrite($opfile, "###'HbbTV check violated Section E.2.3: Segments shall be at least 1s long except last segment of Period', segment ".($j+1)." found with duration ".$segDur." \n");
                
                $sidx_index++;
                $cum_subsegDur = 0;
            }
        }
        else{
            $ref_count = $subsegment_signaling[$sidx_index];
            if($ref_count == 0){
                if($hdlr_type =='vide' && $segDur>15)
                    fwrite($opfile, "###'HbbTV check violated Section E.2.3: Each video segment shall have a duration of not more than 15s', segment ".($j+1)." found with duration ".$segDur." \n");
                if($hdlr_type =='soun' && $segDur>15)
                    fwrite($opfile, "###'HbbTV check violated Section E.2.3: Each audio segment shall have a duration of not more than 15s', segment ".($j+1)." found with duration ".$segDur." \n");
                
                if($segDur <1)
                    fwrite($opfile, "###'HbbTV check violated Section E.2.3: Segments shall be at least 1s long except last segment of Period', segment ".($j+1)." found with duration ".$segDur." \n");
                
                $sidx_index++;
            }
            else{
                $subsegment_signaling[$sidx_index] = $ref_count - 1;
                $cum_subsegDur += $segDur;
                if($hdlr_type =='vide' && $segDur>15)
                    fwrite($opfile, "###'HbbTV check violated Section E.2.3: Each video segment shall have a duration of not more than 15s', segment ".($j+1)." found with duration ".$segDur." \n");
                if($hdlr_type =='soun' && $segDur>15)
                    fwrite($opfile, "###'HbbTV check violated Section E.2.3: Each audio segment shall have a duration of not more than 15s', segment ".($j+1)." found with duration ".$segDur." \n");
                
                if($subsegment_signaling[$sidx_index] == 0){
                    $sidx_index++;
                    if($cum_subsegDur < 1)
                        fwrite($opfile, "###'HbbTV check violated Section E.2.3: Segments shall be at least 1s long except last segment of Period', segment ".($j+1)." found with duration ".$segDur." \n");
                    
                    $cum_subsegDur = 0;
                }
            }
        }
    }
    //   
    
    if($dom->getElementsByTagName('MPD')->item(0)->getAttribute('type') == 'dynamic' && count(glob($locate.'/Adapt'.$adapt_count.'rep'.$rep_count.'/*mp4')) == 1)
        fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated for HbbTV: Section 'Segments' - 'Segment includes features that are not required by the profile being validated against', found only segment in the representation while MPD@type is dynamic.\n");
}

function segmentToPeriodDurationCheck($xml_rep) {
    global $presentationduration;
    $mdhd=$xml_rep->getElementsByTagName('mdhd')->item(0);
    $timescale=$mdhd->getAttribute('timescale');
    $num_moofs=$xml_rep->getElementsByTagName('moof')->length;
    $totalSegmentDuration = 0;
    for ( $j = 0; $j < $num_moofs ; $j++ )
    {
        $trun = $xml_rep->getElementsByTagName('trun')->item($j);
        $cummulatedSampleDuration = $trun->getAttribute('cummulatedSampleDuration');
        $segDur = ( $cummulatedSampleDuration * 1.00 ) / $timescale;      
        $totalSegmentDuration += $segDur;
    }
    
    $period_duration = (float)$presentationduration;
    if(abs((round($totalSegmentDuration, 2) - round($period_duration, 2)) / round($period_duration, 2)) > 0.00001)
        return [false, round($totalSegmentDuration, 2), round($period_duration, 2)];
    else
        return [true, round($totalSegmentDuration, 2), round($period_duration, 2)];
}

// Report on any resolutions used that are not in the tables of resoultions in 10.3 of the DVB DASH specification
function resolutionCheck($opfile, $adapt, $rep){
    $conformant = true;
    
    $progressive_width  = array('1920', '1600', '1280', '1024', '960', '852', '768', '720', '704', '640', '512', '480', '384', '320', '192', '3840', '3200', '2560');
    $progressive_height = array('1080', '900',  '720',  '576',  '540', '480', '432', '404', '396', '360', '288', '270', '216', '180', '108', '2160', '1800', '1440');
    
    $interlaced_width  = array('1920', '704', '544', '352');
    $interlaced_height = array('1080', '576', '576', '288');
    
    $scanType = $adapt->getAttribute('scanType');
    if($scanType == ''){
        $scanType = $rep->getAttribute('scanType');
        
        if($scanType == '')
            $scanType = 'progressive';
    }
    
    $width = $adapt->getAttribute('width');
    $height = $adapt->getAttribute('height');
    if($width == '' && $height == ''){
        $width = $rep->getAttribute('width');
        $height = $rep->getAttribute('height');
        
        if($width != '' && $height != ''){
            if($scanType == 'progressive'){
                $ind1 = array_search($width, $progressive_width);
                if($ind1 !== FALSE){
                    if($height != $progressive_height[$ind1])
                        $conformant = false;
                }
            }
            elseif($scanType == 'interlaced'){
                $ind1 = array_search($width, $interlaced_width);
                if($ind1 !== FALSE){
                    if($height != $interlaced_height[$ind1])
                        $conformant = false;
                }
            }
        }
    }
    
    return array($conformant, $width, $height);
}

function float2int($value) {
    return value | 0;
}
function init_seg_commonCheck($files,$opfile)
{
    $rep_count=count($files);
    fwrite($opfile, "Information on HbbTV-DVB DASH Validation Requirements: Section 'Init Segment(s)' - There are ".$rep_count." Representation in the AdaptationSet with \n");
    for($i=0;$i<$rep_count;$i++)
    {
        $xml = xmlFileLoad($files[$i]);
        if($xml != 0){
            $avcC_count=$xml->getElementsByTagName('avcC')->length;
            fwrite($opfile, ", ".$avcC_count." 'avcC' in Representation ".($i+1)." \n");
        }
    }
}

function seg_timing_common($opfile,$xml_rep, $dom, $pstart)
{   
    $xml_num_moofs=$xml_rep->getElementsByTagName('moof')->length;
    $xml_trun=$xml_rep->getElementsByTagName('trun');
    $xml_tfdt=$xml_rep->getElementsByTagName('tfdt');
    
    ## Consistency check of the start times within the segments with the timing indicated by the MPD
    // MPD information
    $mpd_timing = mdp_timing_info($dom, $pstart);
    
    
    // Segment information
    $type = $dom->getElementsByTagName('MPD')->item(0)->getAttribute('type');
    
    $sidx_boxes = $xml_rep->getElementsByTagName('sidx');
    $subsegment_signaling = array();
    if($sidx_boxes->length != 0){
        foreach($sidx_boxes as $sidx_box){
            $subsegment_signaling[] = (int)($sidx_box->getAttribute('referenceCount'));
        }
    }
    
    $xml_elst = $xml_rep->getElementsByTagName('elst');
    if($xml_elst->length == 0){
        $mediaTime = 0;
    }
    else{
        $mediaTime = (int)($xml_elst->item(0)->getElementsByTagName('elstEntry')->item(0)->getAttribute('mediaTime'));
    }
    
    $timescale=$xml_rep->getElementsByTagName('mdhd')->item(0)->getAttribute('timescale');
    $sidx_index = 0;
    $cum_subsegDur=0;
    for($j=0;$j<$xml_num_moofs;$j++){
        ## Checking for gaps
        if($j > 0){
            $cummulatedSampleDurFragPrev=$xml_trun->item($j-1)->getAttribute('cummulatedSampleDuration');
            $decodeTimeFragPrev=$xml_tfdt->item($j-1)->getAttribute('baseMediaDecodeTime');
            $decodeTimeFragCurr=$xml_tfdt->item($j)->getAttribute('baseMediaDecodeTime');
            
            if($decodeTimeFragCurr!=$decodeTimeFragPrev+$cummulatedSampleDurFragPrev){
                fprintf($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated: Section 'Segments' - A gap in the timing within the segments of the Representation found at segment number ".($j+1)."\n");
            }
        }
        ##
        
        if(!empty($mpd_timing) && $type != 'dynamic'){ //Empty means that there is no mediaPresentationDuration attribute in which case the media presentation duration is unknown.
            $decodeTime = $xml_tfdt->item($j)->getAttribute('baseMediaDecodeTime');
            $compTime = $xml_trun->item($j)->getAttribute('earliestCompositionTime');
            
            $segmentTime = ($decodeTime + $compTime - $mediaTime)/$timescale;
            if(empty($subsegment_signaling)){
                if($j < sizeof($mpd_timing)){
                    if(abs(($segmentTime - $mpd_timing[$j])/$mpd_timing[$j]) > 0.00001)
                        fprintf($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated: Section 'Segments' - Start time \"$segmentTime\" within the segment " . ($j+1) . " is not consistent with the timing indicated by the MPD \"$mpd_timing[$j]\".\n");
                }
            }
            else{
                $ref_count = 1;
                if($sidx_index < sizeof($subsegment_signaling))
                    $ref_count = $subsegment_signaling[$sidx_index];
                
                if($cum_subsegDur == 0 && $sidx_index < sizeof($mpd_timing)){
                    if(abs(($segmentTime - $mpd_timing[$sidx_index])/$mpd_timing[$sidx_index]) > 0.00001)
                        fprintf($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated: Section 'Segments' - Start time \"$segmentTime\" within the segment " . ($sidx_index+1) . " is not consistent with the timing indicated by the MPD \"$mpd_timing[$sidx_index]\".\n");
                }
            
                $cummulatedSampleDuration=$xml_trun->item($j)->getAttribute('cummulatedSampleDuration');
                $segDur=$cummulatedSampleDuration/$timescale;
                $cum_subsegDur += $segDur;
                $subsegment_signaling[$sidx_index] = $ref_count - 1;
                if($subsegment_signaling[$sidx_index] == 0){
                    $sidx_index++;
                    $cum_subsegDur = 0;
                }
            }
        }
    }
    ##
}

function mdp_timing_info($dom, $pstart){
    global $count1, $count2, $presentationduration;
    
    $mpd_timing = array();
    $MPD = $dom->getElementsByTagName('MPD')->item(0);
    $period = ($MPD->getAttribute('type') != 'dynamic') ? $dom->getElementsByTagName('Period')->item(0) : $dom->getElementsByTagName('Period')->item($dom->getElementsByTagName('Period')->length -1);
    $adapt = $dom->getElementsByTagName('AdaptationSet')->item($count1);
    $rep = $adapt->getElementsByTagName('Representation')->item($count2);
    
    // Get the segment-related elements
    $segbase = array();
    if($period->getElementsByTagName('SegmentBase')->length != 0){
        if($period->getElementsByTagName('SegmentBase')->item(0)->parentNode->nodeName == 'Period')
            $segbase[] = $period->getElementsByTagName('SegmentBase')->item(0);
    }
    if($adapt->getElementsByTagName('SegmentBase')->length != 0){
        if($adapt->getElementsByTagName('SegmentBase')->item(0)->parentNode->nodeName == 'AdaptationSet')
            $segbase[] = $adapt->getElementsByTagName('SegmentBase')->item(0);
    }
    if($rep->getElementsByTagName('SegmentBase')->length != 0)
        $segbase[] = $rep->getElementsByTagName('SegmentBase')->item(0);
    
    $segtemplate = array();
    if($period->getElementsByTagName('SegmentTemplate')->length != 0){
        if($period->getElementsByTagName('SegmentTemplate')->item(0)->parentNode->nodeName == 'Period')
            $segtemplate[] = $period->getElementsByTagName('SegmentTemplate')->item(0);
    }
    if($adapt->getElementsByTagName('SegmentTemplate')->length != 0){
        if($adapt->getElementsByTagName('SegmentTemplate')->item(0)->parentNode->nodeName == 'AdaptationSet')
            $segtemplate[] = $adapt->getElementsByTagName('SegmentTemplate')->item(0);
    }
    if($rep->getElementsByTagName('SegmentTemplate')->length != 0)
        $segtemplate[] = $rep->getElementsByTagName('SegmentTemplate')->item(0);
    
    $seglist = array();
    if($period->getElementsByTagName('SegmentList')->length != 0){
        if($period->getElementsByTagName('SegmentList')->item(0)->parentNode->nodeName == 'Period')
            $seglist[] =  $period->getElementsByTagName('SegmentList')->item(0);
    }
    if($adapt->getElementsByTagName('SegmentList')->length != 0){
        if($adapt->getElementsByTagName('SegmentList')->item(0)->parentNode->nodeName == 'AdaptationSet')
            $seglist[] =  $adapt->getElementsByTagName('SegmentList')->item(0);
    }
    if($rep->getElementsByTagName('SegmentList')->length != 0)
        $seglist[] =  $rep->getElementsByTagName('SegmentList')->item(0);
    
    $segtimeline = array();
    if($period->getElementsByTagName('SegmentTimeline')->length != 0){
        if($period->getElementsByTagName('SegmentTimeline')->item(0)->parentNode->parentNode->nodeName == 'Period')
            $segtimeline[] = $period->getElementsByTagName('SegmentTimeline')->item(0);
    }
    if($adapt->getElementsByTagName('SegmentTimeline')->length != 0){
        if($adapt->getElementsByTagName('SegmentTimeline')->item(0)->parentNode->parentNode->nodeName == 'AdaptationSet')
            $segtimeline[] = $adapt->getElementsByTagName('SegmentTimeline')->item(0);
    }
    if($rep->getElementsByTagName('SegmentTimeline')->length != 0)
        $segtimeline[] = $rep->getElementsByTagName('SegmentTimeline')->item(0);
    
    // Calculate segment timing information
    #$mediapresdur = timeparsing($MPD->getAttribute('mediaPresentationDuration'));
    
    if(!empty($segbase)){
        foreach($segbase as $segb)
            $pto = ($segb->getAttribute('presentationTimeOffset') != '') ? (int)($segb->getAttribute('presentationTimeOffset')) : 0;
        $pres_start = $pstart + $pto;
        
        $mpd_timing[] = $pres_start;
    }
    elseif(!empty($segtemplate) || !empty($seglist)){
        $timescale = 0;
        $duration = 0;
        $startNumber = 1;
        if(!empty($segtemplate)){
            foreach($segtemplate as $segtemp){
                $pto = ($segtemp->getAttribute('presentationTimeOffset') != '') ? (int)($segtemp->getAttribute('presentationTimeOffset')) : 0;
                
                if($segtemp->getAttribute('duration') != '')
                    $duration = (int)($segtemp->getAttribute('duration'));
                if($segtemp->getAttribute('timescale') != '')
                    $timescale = (int)$segtemp->getAttribute('timescale');
                if($segtemp->getAttribute('startNumber') != '')
                    $startNumber = (int)$segtemp->getAttribute('startNumber');
            }
        }
        elseif(!empty($seglist)){
            foreach($seglist as $segl){
                $pto = ($segl->getAttribute('presentationTimeOffset') != '') ? (int)($segl->getAttribute('presentationTimeOffset')) : 0;
                
                if($segl->getAttribute('duration') != '')
                    $duration = (int)($segl->getAttribute('duration'));
                if($segl->getAttribute('timescale') != '')
                    $timescale = (int)$segl->getAttribute('timescale');
                if($segl->getAttribute('startNumber') != '')
                    $startNumber = (int)$segl->getAttribute('startNumber');
            }
        }
        
        if($timescale == 0)
            $timescale = 1;
        
        $pres_start = $pstart + $pto/$timescale;
        
        if(!empty($segtimeline)){
            $stags = $segtimeline[sizeof($segtimeline)-1]->getElementsByTagName('S');
            for($s=0; $s<$stags->length; $s++){
                $duration = (int)($stags->item($s)->getAttribute('d'));
                $repeat = ($stags->item($s)->getAttribute('r') != '') ? (int)($stags->item($s)->getAttribute('r')) : 0;
                $time = ($stags->item($s)->getAttribute('t'));
                $time_next = ($stags->item($s+1) != NULL) ? ($stags->item($s+1)->getAttribute('t')) : '';
                
                $segmentDuration = $duration/$timescale;
                
                if($repeat == -1){
                    if($time_next != ''){
                        $time = (int)$time;
                        $time_next = (int)$time_next;
                        
                        $index = 0;
                        while($time_next-$time != 0){
                            $mpd_timing[] = $pres_start + $index*$segmentDuration;
                            
                            $time += $duration;
                            $index++;
                        }
                    }
                    else{
                        $segment_cnt = ceil($presentationduration/$segmentDuration);
                        
                        for($i=0; $i<$segment_cnt; $i++){
                            $mpd_timing[] = $pres_start + $i*$segmentDuration;
                        }
                    }
                }
                else{
                    for($r=0; $r<$repeat+1; $r++){
                        $mpd_timing[] = $pres_start + $r*$segmentDuration;
                    }
                }
            }
        }
        else{
            if($duration == 0){
                $mpd_timing[] = $pres_start;
            }
            else{
                $segmentDuration = $duration/$timescale;
                $segment_cnt = $presentationduration/$segmentDuration;
                
                for($i=0; $i<$segment_cnt; $i++){
                    $mpd_timing[] = $pres_start + $i*$segmentDuration;
                }
            }
        }
        
    }
    
    return $mpd_timing;
}

function bitrate_report($opfile, $dom, $xml_rep, $adapt_count, $rep_count, $sizearray,$bandwidth){
    global $locate, $segment_duration_array;
    
    $adapt = $dom->getElementsByTagName('AdaptationSet')->item($adapt_count);
    $rep = $adapt->getElementsByTagName('Representation')->item($rep_count);
    $bitrate = $rep->getAttribute('bandwidth');
    
    $sidx_boxes = $xml_rep->getElementsByTagName('sidx');
    $subsegment_signaling = array();
    if($sidx_boxes->length != 0){
        foreach($sidx_boxes as $sidx_box){
            $subsegment_signaling[] = (int)($sidx_box->getAttribute('referenceCount'));
        }
    }
    
    $timescale=$xml_rep->getElementsByTagName('mdhd')->item(0)->getAttribute('timescale');
    $num_moofs=$xml_rep->getElementsByTagName('moof')->length;
    $bitrate_info = '';
    
    $segment_duration_array = array();
    $sidx_index = 0;
    $cum_subsegDur = 0;
    // Here 2 possible cases are considered for sidx -subsegment signalling.
    //First case is for no sidx box.
    if(empty($subsegment_signaling)){
        for($j=0;$j<$num_moofs;$j++){
            $cummulatedSampleDuration=$xml_rep->getElementsByTagName('trun')->item($j)->getAttribute('cummulatedSampleDuration');
            $segDur=$cummulatedSampleDuration/$timescale;
            $segSize = $sizearray[$j];
            $segment_duration_array[] = round($segDur, 2);
            $bitrate_info = $bitrate_info . (string)($segSize*8/$segDur) . ',';
        }
    }
    //Secondly, sidx exists with non-zero reference counts- 1) all segments have subsegments (referenced by some sidx boxes) 2) only some segments have subsegments. 
    else{
        for($j=0;$j<$num_moofs;$j++){
            if($sidx_index>sizeof($subsegment_signaling)-1)
                $ref_count=1;// This for case 2 of case 2.
            else
                $ref_count = $subsegment_signaling[$sidx_index];

            $cummulatedSampleDuration=$xml_rep->getElementsByTagName('trun')->item($j)->getAttribute('cummulatedSampleDuration');
            $segDur=$cummulatedSampleDuration/$timescale;
            $cum_subsegDur += $segDur;
            
            $subsegment_signaling[$sidx_index] = $ref_count - 1;
            if($subsegment_signaling[$sidx_index] == 0){
                $segSize = $sizearray[$sidx_index];
                $bitrate_info = $bitrate_info . (string)($segSize*8/$cum_subsegDur) . ',';
                $segment_duration_array[] = round($cum_subsegDur);
                $sidx_index++;
                $cum_subsegDur = 0;
            }
        }
    }
    
    $bitrate_info = substr($bitrate_info, 0, strlen($bitrate_info)-2);
    
    $bitrate_report_name = 'Adapt' . $adapt_count . 'rep' . $rep_count . '.png';
    $command="cd $locate && python bitratereport.py $bitrate_info $bandwidth $bitrate_report_name";
    exec($command);
    
    //exec($command,$output);
    
}
function content_protection_report($dom_MPD)
{
    global $locate;
    $DRM_uuid_array = array ('urn:mpeg:dash:mp4protection:2011'=>'Generic Identifier 1',
                             'urn:mpeg:dash:13818:1:CA_descriptor:2011'=>'Generic Identifier 2',
                             'urn:uuid:5E629AF538DA4063897797FFBD9902D4'=>'Marlin Adaptive Streaming Specification',
                             'urn:uuid:adb41c242dbf4a6d958b4457c0d27b95'=>'Nagra MediaAccess PRM 3.0',
                             'urn:uuid:A68129D3575B4F1A9CBA3223846CF7C3'=>'Cisco/NDS VideoGuard Everywhere DRM',
                             'urn:uuid:9a04f07998404286ab92e65be0885f95'=>'Microsoft PlayReady',
                             'urn:uuid:9a27dd82fde247258cbc4234aa06ec09'=>'Verimatrix ViewRight Web',
                             'urn:uuid:F239E769EFA348509C16A903C6932EFB'=>'Adobe Primetime',
                             'urn:uuid:1f83e1e86ee94f0dba2f5ec4e3ed1a66'=>'SecureMedia',
                             'urn:uuid:644FE7B5260F4FAD949A0762FFB054B4'=>'CMLA',
                             'urn:uuid:6a99532d869f59229a91113ab7b1e2f3'=>'MobiTV',
                             'urn:uuid:35BF197B530E42D78B651B4BF415070F'=>'DivX ',
                             'urn:uuid:B4413586C58CFFB094A5D4896C1AF6C3'=>'Viaccess-Orca',
                             'urn:uuid:edef8ba979d64acea3c827dcd51d21ed'=>'Widevine',
                             'urn:uuid:80a6be7e14484c379e70d5aebe04c8d2'=>'Irdeto',
                             'urn:uuid:dcf4e3e362f158187ba60a6fe33ff3dd'=>'DigiCAP SmartXess',
                             'urn:uuid:45d481cb8fe049c0ada9ab2d2455b2f2'=>'CoreTrust',
                             'urn:uuid:616C7469636173742D50726F74656374'=>'Alticast altiProtect',
                             'urn:uuid:992c46e6c4374899b6a050fa91ad0e39'=>'SecureMedia SteelKnot',
                             'urn:uuid:1077efecc0b24d02ace33c1e52e2fb4b'=>'W3C',
                             'urn:uuid:e2719d58a985b3c9781ab030af78d30e'=>'Clear Key',
                             'urn:uuid:94CE86FB07FF4F43ADB893D2FA968CA2'=>'Apple FairPlay Streaming',
                             'urn:uuid:279fe473512c48feade8d176fee6b40f'=>'Arris Titanium');
    $Adapt_index = 0;
    $key_rotation_used = false;
    foreach ($dom_MPD->getElementsByTagName('AdaptationSet') as $node)
    {
        $adapt_id = $Adapt_index + 1;
        $adaptreport = fopen($locate . "/Adapt".$Adapt_index."_hbbtv_dvb_compInfo.txt", 'a+b');
        if($adaptreport !== false)
        {
            $MPD_systemID_array = array();
            $missing_pssh_array = array(); //holds the uuid-s of the DRM-s which are missing the pssh in the mpd
            $reps_array = array(); // for the summary of reps and their KID
            $rep_index = 0;
            $MPD_kID_flag = false;
            $generic_identifier = "";
            $content_protection_flag = false;
            foreach ($node->getElementsByTagName('ContentProtection') as $cp_node)
            {
                // only if there is a content protection instance the below will be executed
                $content_protection_flag = true;
                if(($cp_node->getAttribute('schemeIdUri') == "urn:mpeg:dash:mp4protection:2011") || ($cp_node->getAttribute('schemeIdUri') == 'urn:mpeg:dash:13818:1:CA_descriptor:2011'))
                {
                    $generic_identifier = $cp_node->getAttribute('schemeIdUri');
                }

                if((!$MPD_kID_flag) && ($generic_identifier != "")) // if a KID was not found in the init seg check in the mpd
                {   
                    $MPD_kID = $cp_node->getAttribute('cenc:default_KID');
                    if($MPD_kID != '')
                    {
                        $MPD_kID = str_replace('-', '', $kID);
                        $MPD_kID_flag = true; //there is a cenc:default_KID, so there must be a pssh in a mpd or init seg
                    }
                }

                $cenc_pssh = $cp_node->getAttribute('cenc:pssh');
                if(($cenc_pssh == '') && ($cp_node->getAttribute('schemeIdUri') != "urn:mpeg:dash:mp4protection:2011") && ($cp_node->getAttribute('schemeIdUri') != 'urn:mpeg:dash:13818:1:CA_descriptor:2011'))
                {
                    $uuid = $cp_node->getAttribute('schemeIdUri');
                    $uuid = str_replace('-', '', $uuid);
                    $missing_pssh_array[] = $uuid; //the drm uuid which is missing a pssh in the mpd
                }

                //excluding generic identifiers which are usually in the first instance of Content Protection
                if (($cp_node->getAttribute('schemeIdUri') != "urn:mpeg:dash:mp4protection:2011") && ($cp_node->getAttribute('schemeIdUri') != 'urn:mpeg:dash:13818:1:CA_descriptor:2011'))
                {
                    $MPD_systemID = $cp_node->getAttribute('schemeIdUri');
                    $MPD_systemID = str_replace('-', '', $MPD_systemID);

                    if(array_key_exists($MPD_systemID, $DRM_uuid_array))
                    {
                        $MPD_systemID_array[$MPD_systemID] = $DRM_uuid_array[$MPD_systemID];
                    }
                    else 
                    {
                        $MPD_systemID_array[$MPD_systemID] = 'unknown'; //if no matches are found in the mapping array 
                    }
                }
            }

            /*For an encrypted Adaptation Set, ContentProtection Descriptors shall always be
            present in the AdaptationSet element, and apply to all contained Representations.
            A ContentProtection Descriptor for the mp4 Protection Scheme with the
            @schemeIdUri value of "urn:mpeg:dash:mp4protection:2011" and
            @value=’cenc’ shall be present in the AdaptationSet element if the contained
            Representations are encrypted.*/
            if($content_protection_flag)
            {    
                //reporting DRM systems in use
                if($generic_identifier != "")
                {
                    $MPD_systemID_k_v  = implode(', ', array_map(
                    function ($v, $k) { return sprintf(" '%s' :: '%s'", $k, $v); },
                    $MPD_systemID_array,array_keys($MPD_systemID_array)));
                    fwrite($adaptreport, "Information on HbbTV-DVB DASH Validation Requirements: Section 'DRM' - DRM systems present in the MPD in Adaptation Set ".$adapt_id.
                    " are identified as follows: ".$generic_identifier." :: ".$DRM_uuid_array[$generic_identifier]." ".$MPD_systemID_k_v."\n");

                    $tenc_kID_array = array();
                    foreach ($node->getElementsByTagName('Representation') as $rep)
                    {    
                        $duplication_flag = false;
                        $inconsistency_flag = false;
                        $missing_pssh_flag = false;
                        $PSSH_systemID_array = array(); // to see the DRM uuid in mpd and pssh and compare them
                        $tenc_kID_flag = false;

                        $xml_file_location = ($locate.'/Adapt'.$Adapt_index.'/Adapt'.$Adapt_index.'rep'.$rep_index.'.xml'); //first rep of the adapt set will have the same pssh as the rest
                        $load = simplexml_load_file($xml_file_location); // load mpd from url
                        if($load !== false)
                        {
                            $dom_abs = dom_import_simplexml($load);
                            if($dom_abs !== false)
                            {
                                $abs = new DOMDocument('1.0');
                                $dom_abs = $abs->importNode($dom_abs, true); //create dom element to contain mpd     
                                $abs->appendChild($dom_abs);

                                /*There SHALL be identical values of default_KID in the Track Encryption Box
                                (‘tenc’) of all Representation referenced by one Adaptation Set.*/
                                if($abs->getElementsByTagName('tenc')->length != 0)
                                {
                                    $tenc_kID = $abs->getElementsByTagName('tenc')->item(0)->getAttribute('default_KID');//default KID can be in the pssh in the init seg
                                }
                                else
                                {
                                    $tenc_kID = '';
                                }
                                if($tenc_kID != '')
                                {
                                    $tenc_kID_flag = true;
                                    $tenc_kID_array[] = $tenc_kID;
                                }
                                foreach ($abs->getElementsByTagName('pssh') as $pssh_node)
                                {

                                    $PSSH_systemID = 'urn:uuid:'.$pssh_node->getAttribute('systemID');
                                    if(array_key_exists($PSSH_systemID, $DRM_uuid_array))
                                    {
                                        $PSSH_systemID_array[$PSSH_systemID] = $DRM_uuid_array[$PSSH_systemID];
                                    }
                                    else 
                                    {
                                       $PSSH_systemID_array[$PSSH_systemID] = 'unknown'; //if no matches are found in the mapping array 
                                    }        
                                }

                                if($tenc_kID_flag || $MPD_kID_flag)
                                {   
                                    // if a pssh is missing in the mpd then there must be in the init seg
                                    // all the nr of instances which are missing the pssh in the mpd must be in the init seg  
                                    if((count($missing_pssh_array) != 0) && (count(array_intersect($missing_pssh_array, $PSSH_systemID_array)) != count($missing_pssh_array)))//not all the missing pssh are in the init seg
                                    {
                                        $missing_pssh_flag = true; 
                                    }
                                }

                                if(!empty($PSSH_systemID_array)) //comparing if there's a DRM in pssh since the MPD has at least a generic one 
                                {
                                    //flag if in both and show inconsistencies 
                                    //$diff_1 = array_diff($MPD_systemID_array, $PSSH_systemID_array); // the uuid that are in mpd but not in pssh
                                    $diff_2 = array_diff(array_keys($PSSH_systemID_array), array_keys($MPD_systemID_array)); // the uuid that are in pssh but not mpd
                                    if(count(array_intersect(array_keys($PSSH_systemID_array), array_keys($MPD_systemID_array))) !=0)
                                    {
                                        $duplication_flag = true; //there is at least one DRM with the same uuid in both
                                    }
                                    else //the pssh box has at least one DRM uuid which is not in the mpd while all the DRM uuid-s must be in the ContentProtection instance of the MPD, so we have inconsistency 
                                    {
                                        $inconsistency_flag = true; 
                                    }    
                                }
                                //add summary for encrypted rep and their kID. use rep id to identify them     
                                $repr_id = $rep->getAttribute('id');
                                $reps_array[$repr_id] = $tenc_kID; 
                                //checking for key rotation:
                                //if there is no pssh in any moof then no key rotation is used
                                foreach ($abs->getElementsByTagName('moof') as $moof) 
                                {
                                    if($moof->getElementsByTagName('pssh')->length != 0) //if pssh does't exists and is an empty node
                                    {
                                        if(($moof->getElementsByTagName('sgpd')->length != 0) && ($moof->getElementsByTagName('sbgp')->length != 0))
                                        {
                                            $key_rotation_used = true;
                                        }
                                    }     
                                }
                                //Check the scheme_type field of the ‘schm’ box has the value ‘cenc’
                                if($abs->getElementsByTagName('schm')->length != 0)
                                {
                                    if($abs->getElementsByTagName('schm')->item(0)->getAttribute('scheme') !== "cenc")
                                
                                    {
                                        fwrite($adaptreport, "Information on HbbTV-DVB DASH Validation Requirements: Section 'DRM' - 'cenc' scheme not found in 'schm' box in Adaptation Set: ".$adapt_id.", Representation: ".$repr_id."\n"); 
                                    }  
                                }
                                if(!empty($PSSH_systemID_array))
                                {
                                    $PSSH_systemID_k_v  = implode(', ', array_map(
                                    function ($v, $k) { return sprintf(" '%s' :: '%s'", $k, $v); },
                                    $PSSH_systemID_array,array_keys($PSSH_systemID_array)));
                                    fwrite($adaptreport, "Information on HbbTV-DVB DASH Validation Requirements: Section 'DRM' - DRM systems present in the PSSH in Adaptation Set: ".$adapt_id.", Representation: ".$repr_id." are identified as follows ".$PSSH_systemID_k_v."\n"); 
                                } 

                                //reporting if there is a missing PSSH
                                if($missing_pssh_flag)
                                {
                                    fwrite($adaptreport, "Information on HbbTV-DVB DASH Validation Requirements: Section 'DRM' - Warning! There is default_KID: ".$tenc_kID." but there is/are missing PSSH box/es (both in MPD and Init segment)"
                                        . " in Adaptation Set: ".$adapt_id." Representation: ".$repr_id."\n");
                                }
                                //reporting duplicate and inconsistent DRM-s in MPD and PSSH box
                                if($duplication_flag)
                                {
                                    fwrite($adaptreport, "Information on HbbTV-DVB DASH Validation Requirements: Section 'DRM' - There are consistent DRM-s in MPD and PSSH box in Adaptation Set: ".$adapt_id.", Representation: ".$repr_id."\n");
                                }

                                if($inconsistency_flag)
                                {
                                    $diff_2_k_v  = implode(', ', array_map(
                                    function ($v, $k) { return sprintf(" SystemID: '%s' ", $v); },
                                    $diff_2,array_keys($diff_2)));
                                    fwrite($adaptreport, "Information on HbbTV-DVB DASH Validation Requirements: Section 'DRM' - There are inconsistent DRM-s in MPD and PSSH box in Adaptation Set: ".$adapt_id.", Representation: ".$repr_id." :\n "
                                            . "the following DRM systems were found present in PSSH but not MPD:\n".$diff_2_k_v."\n");;
                                }
                            }
                        }
                        $rep_index ++; 
                    }

                    //summary of reps and the KID used
                    if($tenc_kID_flag)
                    {
                        if(count(array_unique($tenc_kID_array)) == 1) // is all defauld_KID-s in the adaptation set are the same then write the following report
                        {
                            $reps_k  = implode(', ', array_map(
                            function ($v, $k) { return sprintf(" '%s' ", $k); },
                            $reps_array,array_keys($reps_array)));
                            fwrite($adaptreport, "Information on HbbTV-DVB DASH Validation Requirements: Section 'DRM' - The KID: ".$tenc_kID." is used for representations:".$reps_k."in Adaptation Set ".$adapt_id."\n"); 
                        }
                        else
                        {
                            fwrite($adaptreport, "###HbbTV-DVB DASH Validation Requirements check violated: Section 'DRM' - The Representations in Adaptation Set ".$adapt_id." shall all have the same 'default_KID' in the 'tenc' box but found otherwise.\n");
                        }
                    }
                    //reporting for key retation   
                    if($key_rotation_used)
                    {
                        fwrite($adaptreport, "Information on HbbTV-DVB DASH Validation Requirements: Section 'DRM' - Adaptation Set ".$adapt_id.": Key rotation used.\n");
                    }
                    else 
                    {
                        fwrite($adaptreport, "Information on HbbTV-DVB DASH Validation Requirements: Section 'DRM' - Adaptation Set ".$adapt_id.": Key rotation not used.\n");
                    }   
                }
                else 
                {
                    fwrite($adaptreport, "###HbbTV-DVB DASH Validation Requirements check violated: Section 'DRM' - Content Protection instance found in Adaptation Set ".$adapt_id." but the ContentProtection Descriptor for the mp4 Protection Scheme with the".
                    "@schemeIdUri value of 'urn:mpeg:dash:mp4protection:2011' and @value=’cenc’ is missing.\n");
                }
            }
            else 
            {
                fwrite($adaptreport, "Information on HbbTV-DVB DASH Validation Requirements: Section 'DRM' - Content Protection not used in Adaptation Set ".$adapt_id.".\n");
            }

            fclose($adaptreport);
        }    
        $Adapt_index ++; //move to check the next adapt set
    }    
}

function DVB_period_continuous_adaptation_sets_check($dom, $opfile){
    global $locate, $associativity;
    $MPD = $dom->getElementsByTagName('MPD')->item(0);
    $periods = $MPD->getElementsByTagName('Period');
    $period_cnt = $periods->length;
    
    for($i=0; $i<$period_cnt; $i++){
        for($j=$i+1; $j<$period_cnt; $j++){
            $period1 = $periods->item($i);
            $adapts1 = $period1->getElementsByTagName('AdaptationSet');
            $period2 = $periods->item($j);
            $adapts2 = $period2->getElementsByTagName('AdaptationSet');
            
            for($a1=0; $a1<$adapts1->length; $a1++){
                for($a2=0; $a2<$adapts2->length; $a2++){
                    $adapt1 = $adapts1->item($a1);
                    $adapt2 = $adapts2->item($a2);
                    
                    $adapt1_id = $adapt1->getAttribute('id');
                    $adapt2_id = $adapt2->getAttribute('id');
                    if($adapt1_id == $adapt2_id){
                        $supps2 = $adapt2->getElementsByTagName('SupplementalProperty');
                        foreach($supps2 as $supp2){
                            if($supp2->getAttribute('schemeIdUri') == 'urn:dvb:dash:period_continuity:2014' && in_array($period1->getAttribute('id'), explode(',', $supp2->getAttribute('value')))){
                                ## Period continuous adapation sets are signalled. 
                                ## Start checking for conformity according to Section 10.5.2.3
                                
                                // Check associativity
                                $string_to_search = "$i $a1 $j $a2";
                                if(!in_array($string_to_search, $associativity))
                                    fwrite($opfile, "###'DVB check violated: Section 10.5.2.2- If Adaptation Sets in two different Periods are period continuous, then Adaptation Sets with the value of their @id attribute set to AID in the first and subsequent Periods SHALL be associated as defined in clause 10.5.2.3', not associated Adaptation Set " . ($a1+1) . " in Period " . ($i+1) . " and Adaptation Set " . ($a2+1) . " in Period " . ($j+1) . ".\n");
                                
                                // EPT1 comparisons within the Adaptation Sets
                                if($i == 0){
                                    $reps1 = $adapt1->getElementsByTagName('Representation');
                                    $EPT1 = array();
                                    for($r1=0; $r1<$reps1->length; $r1++){
                                        $xml_rep = xmlFileLoad($locate.'/Adapt'.$a1.'/Adapt'.$a1.'rep'.$r1.'.xml');
                                        if($xml_rep != 0)
                                            $EPT1[] = segment_timing_info($dom, $xml_rep);
                                    }
                                    for($r1=0; $r1<$reps1->length; $r1++){
                                        for($r1_1=$r1+1; $r1_1<$reps1->length; $r1_1++){
                                            if($EPT1[$r1] !== $EPT1[$r1_1])
                                                fwrite($opfile, "###'DVB check violated: Section 10.5.2.2- If Adaptation Sets in two different Periods are period continuous, then all the Representations in the Adaptation Set in the first Period SHALL share the same value EPT1 for the earliest presentation time', not identical for Representation " . ($r1+1) . " and Representation " . ($r1_1+1) . " in Adaptation Set " . ($a1+1) . " in Period " . ($i+1) . ".\n");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

function segment_timing_info($dom, $xml_rep){
    $type = $dom->getElementsByTagName('MPD')->item(0)->getAttribute('type');
    $xml_num_moofs=$xml_rep->getElementsByTagName('moof')->length;
    $xml_trun=$xml_rep->getElementsByTagName('trun');
    $xml_tfdt=$xml_rep->getElementsByTagName('tfdt');
    
    $sidx_boxes = $xml_rep->getElementsByTagName('sidx');
    $subsegment_signaling = array();
    if($sidx_boxes->length != 0){
        foreach($sidx_boxes as $sidx_box){
            $subsegment_signaling[] = (int)($sidx_box->getAttribute('referenceCount'));
        }
    }
    
    $xml_elst = $xml_rep->getElementsByTagName('elst');
    if($xml_elst->length == 0){
        $mediaTime = 0;
    }
    else{
        $mediaTime = (int)($xml_elst->item(0)->getElementsByTagName('elstEntry')->item(0)->getAttribute('mediaTime'));
    }
    
    $timescale=$xml_rep->getElementsByTagName('mdhd')->item(0)->getAttribute('timescale');
    $sidx_index = 0;
    $cum_subsegDur=0;
    $EPT = array();
    if($type != 'dynamic'){
        for($j=0;$j<$xml_num_moofs;$j++){
            $decodeTime = $xml_tfdt->item($j)->getAttribute('baseMediaDecodeTime');
            $compTime = $xml_trun->item($j)->getAttribute('earliestCompositionTime');
            
            $startTime = ($decodeTime + $compTime - $mediaTime)/$timescale;
            if(empty($subsegment_signaling)){
                $EPT[] = $startTime;
            }
            else{
                $ref_count = 1;
                if($sidx_index < sizeof($subsegment_signaling))
                    $ref_count = $subsegment_signaling[$sidx_index];
                
                if($cum_subsegDur == 0)
                    $EPT[] = $startTime;
                
                $cum_subsegDur += (($xml_trun->item($j)->getAttribute('cummulatedSampleDuration'))/$timescale);
                $subsegment_signaling[$sidx_index] = $ref_count - 1;
                if($subsegment_signaling[$sidx_index] == 0){
                    $sidx_index++;
                    $cum_subsegDur = 0;
                }
            }
        }
    }
    
    return $EPT;
}

function TLS_bitrate_check($dom_MPD)//$cp_dom as argument
{
    global $locate;
    //test link https://media.axprod.net/TestVectors/v7-MultiDRM-SingleKey/Manifest.mpd
    if($dom_MPD->getElementsByTagName('BaseURL')->length !=0)
    {
        $base_url = $dom_MPD->getElementsByTagName('BaseURL')->item(0)->textContent;
    }
    else
    {
        $base_url = '';
    }

    $MPD_url = $GLOBALS["url"];
    //check if TLS is used
    if (strpos($base_url, 'https') !== false)
    {
        $TLS_falg = true; 
    }
    elseif (strpos($base_url, 'http') !== false)
    {
        $TLS_falg = false;
    }
    else
    {
        if (strpos($MPD_url, 'https')!== false)
        {
            $TLS_falg = true;
        }
        else
        {
            $TLS_falg = false;
        }
    }
    //if TLS is used then check if any combination excedes the constraint
    if($TLS_falg)
    {
        $perio_index = 0;
        foreach ($dom_MPD->getElementsByTagName('Period') as $period)
        {
            $period_id = $perio_index + 1;
            $video_rep_array = array();
            $audio_rep_array = array();
            $sub_rep_array = array();
            
            foreach ($period->getElementsByTagName('AdaptationSet') as $adaptation_set)
            {
                foreach ($adaptation_set->getElementsByTagName('Representation') as $rep)
                {
                    $rep_id = $rep->getAttribute('id');
                    $rep_BW = $rep->getAttribute('bandwidth');
                    $mimeType = $adaptation_set->getAttribute('mimeType');
                    if($mimeType == 'video/mp4')
                    {
                        $video_rep_array[$rep_id] = $rep_BW;
                    }
                    elseif($mimeType == 'audio/mp4')
                    {
                        $audio_rep_array[$rep_id] = $rep_BW;
                    }
                    elseif ($mimeType == 'application/mp4') 
                    {
                        $sub_rep_array[$rep_id] = $rep_BW;
                    }
                    elseif($mimeType == '')
                    {
                        if($rep->getAttribute('mimeType') == 'video/mp4')
                        {
                            $video_rep_array[$rep_id] = $rep_BW;
                        }
                        elseif ($rep->getAttribute('mimeType') == 'audio/mp4') 
                        {
                            $audio_rep_array[$rep_id] = $rep_BW;
                        }
                        elseif ($rep->getAttribute('mimeType') == 'application/mp4') 
                        {
                            $sub_rep_array[$rep_id] = $rep_BW;
                        }
                    }     
                }
            }
            $constraint_violation_report = fopen($locate."/mpdreport.txt", 'a+b');
            //if the mpdreport.txt fails to be created or opened then skip the rest of the code
            if($constraint_violation_report !== false)
            {
                foreach ($video_rep_array as $k_v => $v_BW)
                {
                    foreach ($audio_rep_array as $k_a => $a_BW)
                    {
                        foreach ($sub_rep_array as $k_s => $s_BW)
                        {
                            $total_BW = $s_BW + $a_BW + $v_BW;
                            if(($total_BW > 12000000) && ($total_BW <= 39000000))
                            {
                                // 12 Mbit/s if the terminal does not support UHD video.
                                fwrite($constraint_violation_report, "***Information on HbbTV-DVB DASH Validation Requirements for HbbTV: Section 'TLS' - Period ".$period_id." -> HbbTV TLS bitrate constraint violation Section 7.3.1.2 - If the terminal does not support UHD video the bitrate "
                                        . "should not exceed 12 Mbit/s.\n---The bandwidth sum of representations: ".$k_v.", ".$k_a.", ".$k_s." with the respective bandwidths: "
                                        . $v_BW." bps, ".$a_BW." bps, ".$s_BW." bps which amounts to a total of ".$total_BW." bps was found to violate this constraint.***\n");

                            }
                            elseif (($total_BW > 39000000) && ($total_BW <= 51000000)) 
                            {
                                // 12 Mbit/s if the terminal does not support UHD video.
                                // 39 Mbit/s if the terminal does support UHD video but does not support HFR video. 
                                fwrite($constraint_violation_report, "***Information on HbbTV-DVB DASH Validation Requirements for HbbTV: Section 'TLS' - Period ".$period_id." -> HbbTV TLS bitrate constraint violation Section 7.3.1.2 - If the terminal does support UHD video but does not support HFR video"
                                        . " the bitrate should not exceed 39 Mbit/s.\n---The bandwidth sum of representations: ".$k_v.", ".$k_a.", ".$k_s." with the respective bandwidths: "
                                        . $v_BW." bps, ".$a_BW." bps, ".$s_BW." bps which amounts to a total of ".$total_BW." bps was found to violate this constraint.***\n");
                            }
                            elseif($total_BW > 51000000)
                            {
                                // 12 Mbit/s if the terminal does not support UHD video.
                                // 39 Mbit/s if the terminal does support UHD video but does not support HFR video.
                                // 51 Mbit/s if the terminal supports UHD HFR video. 
                                fwrite($constraint_violation_report, "***Information on HbbTV-DVB DASH Validation Requirements for HbbTV: Section 'TLS' - Period ".$period_id." -> HbbTV TLS bitrate constraint violation Section 7.3.1.2 - If the terminal supports UHD HFR video"
                                        . " the bitrate should not exceed 51 Mbit/s.\n---The bandwidth sum of representations: ".$k_v.", ".$k_a.", ".$k_s." with the respective bandwidths: "
                                        . $v_BW." bps, ".$a_BW." bps, ".$s_BW." bps which amounts to a total of ".$total_BW." bps was found to violate this constraint.***\n");
                            } 
                        }
                    }
                }
                fclose($constraint_violation_report);
            }
            else
            {
                echo "Failed to open mpdreport.txt";
            }
            $perio_index ++;
        }
    }
}


function seg_duration_checks($dom_MPD, $count1, $count2, $opfile)
{
    global $locate, $segment_duration_array;
    $MPD = $dom_MPD->getElementsByTagName('MPD')->item(0);
    $period = ($MPD->getAttribute('type') != 'dynamic') ? $dom_MPD->getElementsByTagName('Period')->item(0) : $dom_MPD->getElementsByTagName('Period')->item($dom_MPD->getElementsByTagName('Period')->length -1);
    $adapt_set = $dom_MPD->getElementsByTagName('AdaptationSet')->item($count1);
    $rep = $adapt_set->getElementsByTagName('Representation')->item($count2);
    $adapt_id = $count1 + 1;
        $rep_id = $rep->getAttribute('id');
        if($rep_id == '')
        {
            $rep_id = $count2 + 1;
        }

        if($rep->getElementsByTagName('SegmentTemplate')->length != 0) //only if there is a segment template in the representation get the timescale and duration
        {
            $seg_template = $rep->getElementsByTagName('SegmentTemplate')->item(0);
        }
        elseif($adapt_set->getElementsByTagName('SegmentTemplate')->length != 0)
        {
            if($adapt_set->getElementsByTagName('SegmentTemplate')->item(0)->parentNode->nodeName == 'AdaptationSet')
            {
                $seg_template = $adapt_set->getElementsByTagName('SegmentTemplate')->item(0);
            }
        }
        elseif ($period->getElementsByTagName('SegmentTemplate')->length != 0) 
        {
            if($period->getElementsByTagName('SegmentTemplate')->item(0)->parentNode->nodeName == 'Period')
            {
                $seg_template = $period->getElementsByTagName('SegmentTemplate')->item(0);
            }
        }
        else
        {
            $MPD_duration_sec = 'Not_Set';
        }
        
        if($MPD_duration_sec != 'Not_Set') //if there is a segment template
        {
            $duration_diff_array = array(); //array to hold the duration of segments present in atom file that are different from that advertised in the MPD
            $duration = $seg_template->getAttribute('duration');
            $timescale = $seg_template->getAttribute('timescale');
            if(($duration != '') && ($timescale != ''))
            {
                $MPD_duration_sec = round(($duration / $timescale), 2);
                $ind = 0;
                foreach ($segment_duration_array as $atom_seg_duration)
                {
                    $ind++;
                    if($atom_seg_duration != $MPD_duration_sec)
                    {
                        $duration_diff_array[$ind] = $atom_seg_duration;
                    }
                }
                
            } 
            else
            {
                if($seg_template->getElementsByTagName('SegmentTimeline')->length != 0)
                {
                    $MPD_duration_sec_array = array();
                    $seg_tline = $seg_template->getElementsByTagName('SegmentTimeline')->item(0);
                    $seg_tline_num = $seg_tline->getElementsByTagName('S')->length;
                    for ($i = 0; $i < $seg_tline_num; $i++ )
                    {
                        $seg_instance = $seg_tline->getElementsByTagName('S')->item($i);
                        $repetition = $seg_instance->getAttribute('r');
                        $duration = $seg_instance->getAttribute('d');
                        if($repetition == -1)
                        {
                            $MPD_duration_sec = round(($duration / $timescale), 2);
                            $ind = 0;
                            foreach ($segment_duration_array as $atom_seg_duration)
                            {
                                $ind++;
                                if($atom_seg_duration != $MPD_duration_sec)
                                {
                                    $duration_diff_array[$ind] = $atom_seg_duration;
                                }
                            }
                        }
                        else
                        {
                            if($repetition == '')
                            {
                                $repetition = 1;
                            }

                            for($i = 0; $i< $repetition; $i++)
                            {
                                $MPD_duration_sec_array[] = round(($duration / $timescale), 2);
                            }
                        }
                    }
                    for($j = 0; $j < count($MPD_duration_sec_array); $j++ )
                    {
                        if($MPD_duration_sec_array[$j] != $segment_duration_array[$j])
                        {
                            $duration_diff_array[$j] = $segment_duration_array[$j];
                        }
                    }
                }
                else
                {
                    $MPD_duration_sec = 'Not_Set';
                }
            }
        }
        $total_seg_duration = array_sum($segment_duration_array);
        if(!empty($duration_diff_array))
        {
            if(empty($MPD_duration_sec_array))
            {
                $duration_diff_k_v  = implode(' ', array_map(function ($v, $k) { return sprintf(" seg: '%s' -> duration: '%s' sec \n", $k, $v); },
                $duration_diff_array,array_keys($duration_diff_array)));
                fwrite($opfile, "Information on HbbTV-DVB DASH Validation Requirements: Section 'Duration Self consistency' - In Adaptation Set ".$adapt_id.", Representation with 'id' : ".$rep_id." the following segments were found to have a different"
                        . " duration from the one advertised in the MPD (".$MPD_duration_sec." sec) :\n".$duration_diff_k_v.".\n");
            }
            else
            {
                $duration_diff_k_v  = implode(' ', array_map(function ($v, $k) { return sprintf(" seg: '%s' -> duration: '%s' sec \n", $k, $v); },
                $duration_diff_array,array_keys($duration_diff_array)));
                fwrite($opfile, "Information on HbbTV-DVB DASH Validation Requirements: Section 'Duration Self consistency' - In Adaptation Set ".$adapt_id.", Representation with 'id' : ".$rep_id." the following segments were found to have a different"
                        . " duration from the one advertised in the MPD:\n".$duration_diff_k_v.".\n");
            }
        }
        
        //load the atom xml file into a dom Document
        $xml_file_location = $locate.'/Adapt'.$count1.'/Adapt'.$count1.'rep'.$count2.'.xml'; 
        $load = simplexml_load_file($xml_file_location); // load mpd from url 
        if($load !== false)
        {
            $dom_abs = dom_import_simplexml($load);
            if($dom_abs !== false)
            {
                $abs = new DOMDocument('1.0');
                $dom_abs = $abs->importNode($dom_abs, true); //create dom element to contain mpd     
                $abs->appendChild($dom_abs);
  
                if(($abs->getElementsByTagName('mehd')->length != 0) && ($abs->getElementsByTagName('mvhd')->length != 0))
                {
                    $fragment_duration = $abs->getElementsByTagName('mehd')->item(0)->getAttribute('fragmentDuration');
                    $fragment_duration_sec = (float)($fragment_duration) / (float)($abs->getElementsByTagName('mvhd')->item(0)->getAttribute('timeScale'));
                    if($abs->getElementsByTagName('hdlr')->length != 0)
                    {
                        $handler_type = $abs->getElementsByTagName('hdlr')->item(0)->getAttribute('handler_type');
                    }
                    else
                    {
                        $handler_type = 'missing';
                    }
                    if(abs(($fragment_duration_sec-$total_seg_duration)/$total_seg_duration) > 0.00001)
                    {
                        if($handler_type == 'vide')
                        {
                            fwrite($opfile, "Warning on HbbTV-DVB DASH Validation Requirements: Section 'Duration Self consistency' - The fragment duration of video type (".$fragment_duration_sec." sec) is different from the sum of all segment durations (".$total_seg_duration." sec) in Adaptation Set: "
                                    .$adapt_id." Representation with 'id' : ".$rep_id. ".\n");
                        }
                        elseif($handler_type == 'soun')
                        {
                            fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated: Section 'Duration Self consistency' - The fragment duration of audio type (".$fragment_duration_sec." sec) is different from the sum of all segment durations (".$total_seg_duration." sec) in Adaptation Set: "
                                    .$adapt_id." Representation with 'id' : ".$rep_id. ".'\n");
                        }
                        elseif ($handler_type == 'missing') 
                        {
                            fwrite($opfile, "Warning on HbbTV-DVB DASH Validation Requirements: Section 'Duration Self consistency' - The fragment duration of 'unknown' type (".$fragment_duration_sec." sec) is different from the sum of all segment durations (".$total_seg_duration." sec) in Adaptation Set: "
                                    .$adapt_id." Representation with 'id' : ".$rep_id. ".\n");
                        }
                    }
                }           
            }
        }
                    if(!empty($MPD_duration_sec_array))
                    {
                        $MPD_duration_sec = 'Not_Set'; //to avoid giving an array to the python code as an argument
                    }
                    $atm_duration_array_str = implode(',', $segment_duration_array);
                    $location = $locate.'/Adapt' . $count1 . '_rep' . $count2 . '.png';
                    $command = "cd $locate && python seg_duration.py  $atm_duration_array_str $MPD_duration_sec $location";
                    exec($command);
                    
                    // Check if the average segment duration is consistent with that of the duration information in the MPD
                    $num_segments = sizeof($segment_duration_array);
                    $average_segment_duration = (array_sum($segment_duration_array) ) / ($num_segments);
                    if($MPD_duration_sec != 'Not_Set'){
                        if(abs((round($average_segment_duration, 2)-round($MPD_duration_sec, 2)) / round($MPD_duration_sec, 2)) > 0.00001)
                            fwrite($opfile, "###'HbbTV-DVB DASH Validation Requirements check violated: Section 'Duration Self consistency' - The average segment duration is not consistent with the durations advertised by the MPD " . round($average_segment_duration, 2) . ' vs. ' . round($MPD_duration_sec, 2) . ".'\n");
                    }
}

?>