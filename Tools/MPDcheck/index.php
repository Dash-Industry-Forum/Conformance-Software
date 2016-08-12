<!DOCTYPE html>
<html>
    <head>
        <title>MPD Feature Check</title>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>    
        <script src="http://code.jquery.com/jquery-latest.js"></script>
        <link rel="stylesheet" type="text/css" href="TestFramework.css">
    </head>
<body> 
    <h2> MPD Feature Check </h2>
<script>
    var resultDivNum = 0;
    var finalData = [];
    
    window.onload = function()
    {
        document.getElementById('vectors').value = <?php $file = file_get_contents('DefaultVectorList.txt');
        echo json_encode($file); ?>;
    };
    
    Array.prototype.unique = function()
    {
	var n = {},r=[];
	for(var i = 0; i < this.length; i++) 
	{
            if (!n[this[i]]) 
            {
                n[this[i]] = true; 
                r.push(this[i]); 
            }
	}
	return r;
    }
    
    Array.prototype.max = function() {
        return Math.max.apply(null, this);
    }
    
//    function downloadCSV(args) {
//        var data, filename, link;
//        var csv = args.data;
//        if (csv == null) return;
//        filename = args.filename || 'export.csv';
////        if (!csv.match(/^data:text\/csv/i)) {
////            csv = 'data:text/csv;charset=utf-8,' + csv;
////        }
//        data = encodeURI(csv);
//        link = document.createElement('a');
//        link.setAttribute('href', data);
//        link.setAttribute('download', filename);
//        link.click();
//    }
    
    function testing()
    { 
        //Remove old Results division from the webpage
        for (var z = 1; z <= resultDivNum; z++) {
            if (!(document.getElementById('resultDiv' + z) === null))
                document.getElementById('resultDiv' + z).remove();
        }

        var i = 1;
        var vectorstr = document.getElementById('vectors').value;
        if (vectorstr !== '')
        {
            var vectors = vectorstr.split("\n");
        }
        ajaxcall();

        function ajaxcall()
        {
            if (i <= vectors.length)
            {
                var id = 'resultDiv' + i;                
                var topn = 120 + 15 * (i + 1);
                var top = topn + 'px';
                var div = '<div id= ' + id + ' style="position: absolute;left:1000px; top:' + top + ';"></div>';
//                document.body.insertAdjacentHTML('beforeend', div);
                document.getElementById('statusContent').innerHTML = "Running vector " + i;
                
                //another column for test results
                var id2 = 'resultDiv' + (i+vectors.length);
                var div2 = '<div id= ' + id2 + ' style="position: absolute;left:1200px; top:' + top + ';"></div>';
//                document.body.insertAdjacentHTML('beforeend', div2);

                //another column for test results
                var id3 = 'resultDiv' + (i+vectors.length*2);
                var div3 = '<div id= ' + id3 + ' style="position: absolute;left:1320px; top:' + top + ';"></div>';
//                document.body.insertAdjacentHTML('beforeend', div3);
                
                //another column for test results
                var id4 = 'resultDiv' + (i+vectors.length*3);
                var div4 = '<div id= ' + id4 + ' style="position: absolute;left:1500px; top:' + top + ';"></div>';
//                document.body.insertAdjacentHTML('beforeend', div4);

                //another column for test results
                var id5 = 'resultDiv' + (i+vectors.length*4);
                var div5 = '<div id= ' + id5 + ' style="position: absolute;left:1650px; top:' + top + ';"></div>';
//                document.body.insertAdjacentHTML('beforeend', div5);
                
                //another column for test results
                var id6 = 'resultDiv' + (i+vectors.length*5);
                var div6 = '<div id= ' + id6 + ' style="position: absolute;left:1920px; top:' + top + ';"></div>';
//                document.body.insertAdjacentHTML('beforeend', div6);
                
                //another column for test results
                var id7 = 'resultDiv' + (i+vectors.length*6);
                var div7 = '<div id= ' + id7 + ' style="position: absolute;left:2030px; top:' + top + ';"></div>';
//                document.body.insertAdjacentHTML('beforeend', div7);
                
                //another column for test results
                var id8 = 'resultDiv' + (i+vectors.length*7);
                var div8 = '<div id= ' + id8 + ' style="position: absolute;left:2150px; top:' + top + ';"></div>';
//                document.body.insertAdjacentHTML('beforeend', div8);
                
                                //another column for test results
                var id9 = 'resultDiv' + (i+vectors.length*8);
                var div9 = '<div id= ' + id9 + ' style="position: absolute;left:2200px; top:' + top + ';"></div>';
//                document.body.insertAdjacentHTML('beforeend', div9);

                var currentData = [];
                $.post(
                    "loadMPD.php",
                    {url: vectors[i - 1]}
                ).done(function (response) {
//                    console.log(response);
                    
                    if (response.trim() !== 'error')
                    {
                        var xml = $.parseXML(response);
                        checkDashInfo(xml);
                        printNumPeriods(xml);
                        countTotalDuration(xml);
                        maxNumRepr(xml);
                        printSize(xml);
                        listMimeType(xml);
//                        checkMimeType(xml);
                        printCodecs(xml);
                        checkSegmentTemplate(xml,"");
                        checkSegmentTemplate(xml,"media");
                        checkSegmentTemplate(xml,"duration");
                        checkSegmentTimeline(xml);
                        checkSchIU(xml,"ContentProtection");
                        checkSchIU(xml,"EssentialProperty");
                        checkSchIU(xml,"UTCTiming");
                        checkSchIU(xml,"InbandEventStream");
                        contentPro(xml);
                        countBaseURL(xml);
                        checkSegmentTemplate(xml,"presentationTimeOffset");
                        checkSegmentBase(xml,"presentationTimeOffset");
                        checkSchIU(xml,"SupplementalProperty");  //for "urn:mpeg:dash:period_continuity:2014"
                        checkUTCTiming(xml);
                        checkPeriod(xml);
                        verifyEarlyTerminated(xml);  //needs manual check
                        verifyDefaultContent(xml);
//                        checkContentType(xml);
//                        maxNumAdapt(xml);
//                        printAdaptRep(xml, "frameRate", "video");
//                        printAdaptRep(xml, "sar", "video");
//                        printAdapt(xml, "par");
//                        printRep(xml, "bandwidth", "video");
//                        printRep(xml, "bandwidth", "audio");
//                        printAdapt(xml, "segmentAlignment");
//                        printAdapt(xml, "subsegmentAlignment");
//                        printAdaptRep(xml, "startWithSAP", "both");
//                        printAdapt(xml, "subsegmentStartsWithSAP");
//                        printAdaptRep(xml, "audioSamplingRate", "audio");
                        finalData.push(currentData);
                    }else
                    {
                        $('#' + id).prepend('<b>Broken Link</b>');
                    }
                    i++;
                    ajaxcall();
                });
                
                
                function verifyEarlyTerminated(xmlDoc)
                {
                    //3 preconditions: multiperiod + Period@start + Period@duration (could be in different periods)
                    currentData.push("Early Terminated period");
                    var periods = xmlDoc.getElementsByTagName("Period");
                    if (periods.length === 1)
                    {
                        currentData.push("no");
                        return;
                    }
                    
                    var existStart = "no";
                    for (var k = 0; k < periods.length; k++)
                    {
                        if (periods[k].getAttribute("start"))
                        {
                            existStart = "yes";
                        }
                    }
                    if (existStart === "no")
                    {
                        currentData.push("no");
                        return;
                    }
                    
                    var existDur = "no";
                    for (var k = 0; k < periods.length; k++)
                    {
                        if (periods[k].getAttribute("duration"))
                        {
                            existDur = "yes";
                        }
                    }
                    if (existDur === "no")
                    {
                        currentData.push("no");
                        return;
                    }
                    currentData.push("check");
                }
                
                function verifyDefaultContent(xmlDoc)
                {
                    //precondition: "xlink:href" exists
                    currentData.push("Default Content");
                    var existDefault = "no";
                    var periods = xmlDoc.getElementsByTagName("Period");
                    for (var k = 0; k < periods.length; k++)
                    {
                        if (periods[k].getAttribute("xlink:href"))
                        {
                            var adaptSets = periods[k].getElementsByTagName("AdaptationSet");
                            if (adaptSets.length > 0)
                            {
                                existDefault = "yes";
                                break;
                            }
                        }
                    }
                    if (existDefault === "no")
                    {
                        currentData.push("no");
                        return;
                    }
                    currentData.push("yes");
                }

                function checkContentType(xmlDoc) {
                    var adaptSets = xmlDoc.getElementsByTagName("AdaptationSet");
                    var missingContentType = false;
                    for (var k = 0; k < adaptSets.length; k++)
                    {
                        var contentType = adaptSets[k].getAttribute("contentType");
                        if (!contentType)
                        {
                            missingContentType = true;
                            break;
                        }
                    }

                    if (missingContentType)
                    {
                        $('#' + id).prepend('<img id="theImg" src="button_cancel.png" />');
//                        alert("@contentType missing for Test Vector " + i);
                    }
                    else
                    {
                        $('#' + id).prepend('<img id="theImg" src="right.jpg" />');
                    }
                    document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                    resultDivNum = i+1;
                }
                
                
                function checkMimeType(xmlDoc) {
                    var mimeTypeError = false;
                    //go through every Period
                    var periods = xmlDoc.getElementsByTagName("Period");
                    for (var k = 0; k < periods.length; k++)
                    {
                        //loop every Adaptation set
                        var adaptSets = periods[k].getElementsByTagName("AdaptationSet");
                        if (adaptSets.length === 0) //used for handling xlink etc.
                        {
                            //don't show warning if remote element is contained
                            if (!periods[k].getAttribute("xlink:href"))
                            {
                                console.log("Warning: no Adaptation set in Period " + k + " for Test Vector " + i);
                            }
                            continue;
                        }
                        var numVideo = 0;
                        var numAudio = 0;
                        for (var m = 0; m < adaptSets.length; m++)
                        {
                            var mimeType = adaptSets[m].getAttribute("mimeType");
                            var numSubVideo = 0;
                            var numSubAudio = 0;
                            if (!mimeType)
                            {
                                //search @mimeType in corresponding Representation Sets if not present in Adaptation set
                                var repSets = adaptSets[m].getElementsByTagName("Representation");
                                if (repSets.length === 0)
                                {
                                    console.log("Warning: @mimeType missing in Period " + k + " Adaptation set " + m + " for Test Vector " + i);
                                    mimeTypeError = true;
                                    continue;
                                }
                                for (var n = 0; n < repSets.length; n++)
                                {
                                    var subMimeType = repSets[n].getAttribute("mimeType");
                                    if (!subMimeType)
                                    {
                                        console.log("Warning: @mimeType missing in Period " + k + " Adaptation set " + m + " Representation set " + n + " for Test Vector " + i);
                                        mimeTypeError = true;
                                    }
                                    else
                                    {
                                        if(subMimeType === "video/mp4")
                                        {
                                            numSubVideo++;
                                        }
                                        else if(subMimeType === "audio/mp4")
                                        {
                                            numSubAudio++;
                                        }
                                        else
                                        {
                                            console.log("Warning: unknown @mimeType \"" + subMimeType + "\" in Period " + k + " Adaptation set " + m + " Representation set " + n + " for Test Vector " + i);
                                            mimeTypeError = true;
                                        }
                                    }
                                }
                                if (numSubVideo>0)
                                {
                                    numVideo++;
                                }
                                if (numSubAudio>0)
                                {
                                    numAudio++;
                                }
                            }
                            else
                            {
                                if(mimeType === "video/mp4")
                                {
                                    numVideo++;
                                }
                                else if(mimeType === "audio/mp4")
                                {
                                    numAudio++;
                                }
                                else
                                {
                                    console.log("Warning: unknown @mimeType \"" + mimeType + "\" in Period " + k + " Adaptation set " + m + " for Test Vector " + i);
                                    mimeTypeError = true;
                                }
                            }
                        }
                        if (numAudio<1)
                        {
                            console.log("Warning: no Adaptation set with @mimeType=\"audio/mp4\" in Period " + k + " for Test Vector " + i);
                            mimeTypeError = true;
                        }
                        if (numVideo>1) //TODO is it acceptable that number of video adapt. set == 0 (audio only)?
                        {
                            //we need to verify if it's caused by trick mode
//                            console.log("Period " + k + ":");
                            if (!inTrickMode(periods[k], numVideo))
                            {
                                console.log("Warning: too many Adaptation sets with @mimeType=\"video/mp4\" in Period " + k + " for Test Vector " + i);
                                mimeTypeError = true;
                            }
                        }
                    }
                    
                    if (mimeTypeError)
                    {
                        $('#' + id2).prepend('<img id="theImg" src="button_cancel.png" />');
                    }
                    else
                    {
                        $('#' + id2).prepend('<img id="theImg" src="right.jpg" />');
                    }
                    document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                    resultDivNum = i+vectors.length+1;
                }
                
                
                //NOTE: here we only verify additional video Adaptation set 
                function inTrickMode(xmlDoc, numVideo)
                {
                    var essentialP = xmlDoc.getElementsByTagName("EssentialProperty");
                    var supplementalP = xmlDoc.getElementsByTagName("SupplementalProperty");
                    var numTrickMode = 0;
                    if (essentialP.length === 0 && supplementalP.length === 0)
                    {
                        return false;
                    }
                    
                    if (essentialP.length > 0)
                    {
                        for (var m = 0; m < essentialP.length; m++)
                        {
                            var uri = essentialP[m].getAttribute("schemeIdUri");
                            if (uri === "http://dashif.org/guidelines/trickmode")
                                numTrickMode++;
                        }
                    }
                    
                    if (supplementalP.length > 0)
                    {
                        for (var m = 0; m < supplementalP.length; m++)
                        {
                            var uri = essentialP[m].getAttribute("schemeIdUri");
                            if (uri === "http://dashif.org/guidelines/trickmode")
                                numTrickMode++;
                        }
                    }
                    
                    if (numTrickMode === numVideo - 1) //TODO check if corresponding @value is set correctly
                        return true;
                    
                    return false;
                }
                
                function printCodecs(xmlDoc)
                {   var videoCodecs = [];
                    var audioCodecs = [];
                    var adaptSets = xmlDoc.getElementsByTagName("AdaptationSet");    
                    for (var k = 0; k < adaptSets.length; k++)
                    {
                        //skip if the adaptation set is for subtiles
                        var role = adaptSets[k].getElementsByTagName("Role");
                        if (role.length > 0)
                        {
                            var numSubtitle = 0;
                            for (var t = 0; t < role.length; t++)
                            {
                                if (role[t].getAttribute("value") === "subtitle")
                                    numSubtitle++;
                                else
                                    break;
                            }
                            if (numSubtitle === role.length)
                                continue;
                        }
                        
                        var codecs = adaptSets[k].getAttribute("codecs");
                        if (codecs === null || codecs === "") //no codecs info in Adaptation set level
                        {
                            //check in representation level
                            var repSets = adaptSets[k].getElementsByTagName("Representation");
                            for (var t = 0; t < repSets.length; t++)
                            {
                                codecs = repSets[t].getAttribute("codecs");
                                if (codecs === null || codecs === "") //no codecs info in Representation set set level
                                {
                                    //TODO may have to check in sub representation level
                                    var subRepSets = repSets[t].getElementsByTagName("SubRepresentation");
                                    if (subRepSets.length>0)
                                        alert("error!!! no codecs!!!");
                                }
                                else
                                {
                                    if (repSets[t].getAttribute("contentType") === "video" || repSets[t].getAttribute("mimeType") === "video/mp4" || adaptSets[k].getAttribute("contentType") === "video" || adaptSets[k].getAttribute("mimeType") === "video/mp4")
                                    {
                                        videoCodecs.push(codecs);
                                    }
                                    else if (repSets[t].getAttribute("contentType") === "audio" || repSets[t].getAttribute("mimeType") === "audio/mp4" || adaptSets[k].getAttribute("contentType") === "audio" || adaptSets[k].getAttribute("mimeType") === "audio/mp4")
                                    {
                                        audioCodecs.push(codecs);
                                    }
                                    else
                                    {
                                        alert("error!!! neither video nor audio representation set!!!");
                                    }
                                }
                            }
                        }
                        else
                        {
                            if (adaptSets[k].getAttribute("contentType") === "video" || adaptSets[k].getAttribute("mimeType") === "video/mp4")
                            {
                                videoCodecs.push(codecs);
                            }
                            else if (adaptSets[k].getAttribute("contentType") === "audio" || adaptSets[k].getAttribute("mimeType") === "audio/mp4")
                            {
                                audioCodecs.push(codecs);
                            }
                            else
                            {
                                alert("error!!! neither video nor audio adaptation set!!!");
                            }
                        }
                    }
                    videoCodecs = filterResults(videoCodecs);
                    audioCodecs = filterResults(audioCodecs);
//                    console.log("videoCodecs: "+videoCodecs +"; audioCodecs: "+audioCodecs);
                    currentData.push("Video codecs");
                    currentData.push(videoCodecs);
                    currentData.push("Audio codecs");
                    currentData.push(audioCodecs);
                    $('#' + id3).prepend(videoCodecs);
                    $('#' + id4).prepend(audioCodecs);
                    document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                    resultDivNum = i+vectors.length*3+1;
                }
                
                function filterResults(elements)
                {
                    //remove duplicate results
                    elements = elements.unique();
                    //add "x" when the result is empty
                    if (elements.length === 0)
                    {
                        elements = "x";
                    }
                    else
                    {
                        elements = elements.toString();
                    }
                    return elements;
                }
                
                function printSize(xmlDoc)
                {   
                    var sizes = [];
                    var maxWidth = "";
                    var coHeight = "";
                    var maxHeight = "";
                    var adaptSets = xmlDoc.getElementsByTagName("AdaptationSet");
                    for (var k = 0; k < adaptSets.length; k++)
                    {
                        //skip if the adaptation set is for subtiles
                        var role = adaptSets[k].getElementsByTagName("Role");
                        if (role.length > 0)
                        {
                            var numSubtitle = 0;
                            for (var t = 0; t < role.length; t++)
                            {
                                if (role[t].getAttribute("value") === "subtitle")
                                    numSubtitle++;
                                else
                                    break;
                            }
                            if (numSubtitle === role.length)
                                continue;
                        }
                        
                        //skip audio Adaptation set
                        if (adaptSets[k].getAttribute("contentType") === "audio" || adaptSets[k].getAttribute("mimeType") === "audio/mp4")
                            continue;
                        
                        var width = adaptSets[k].getAttribute("width");
                        var height = adaptSets[k].getAttribute("height");
                        if (width === null || width === "" || height === null || height === "") //no size info in Adaptation set level
                        {
                            //check in representation level
                            var repSets = adaptSets[k].getElementsByTagName("Representation");
                            for (var t = 0; t < repSets.length; t++)
                            {
                                if (repSets[t].getAttribute("contentType") === "audio" || repSets[t].getAttribute("mimeType") === "audio/mp4")
                                    continue; //skip audio Representation set
                                width = repSets[t].getAttribute("width");
                                height = repSets[t].getAttribute("height");
                                if (width === null || width === "" || height === null || height === "") //no size info in Representation set level
                                {
                                    //TODO may have to check in sub representation level
                                    var subRepSets = repSets[t].getElementsByTagName("SubRepresentation");
                                    if (subRepSets.length>0)
                                        alert("error!!! no sizes!!!");
                                }
                                else{
                                    sizes.push(width + "x" + height);
                                    if (Number(width) > Number(maxWidth))
                                    {
                                        maxWidth = width;
                                        coHeight = height;
                                    }
                                    if (Number(height) > Number(maxHeight))
                                        maxHeight = height;
                                }
                                
                            }
                        }
                        else
                        {
                            sizes.push(width + "x" + height);
                            if (Number(width) > Number(maxWidth))
                            {
                                maxWidth = width;
                                coHeight = height;
                            }
                            if (Number(height) > Number(maxHeight))
                                maxHeight = height;
                        }
                        
                    }
                    sizes = filterResults(sizes);
                    currentData.push("Muti-resolution video");
                    if ((sizes.split(",")).length > 1)
                    {
                        currentData.push("yes");
                    }
                    else
                    {
                        currentData.push("no");
                    }
                    
                    currentData.push("Max video resolution");
                    if (coHeight === maxHeight)
                        currentData.push(maxWidth + "x" + maxHeight);
                    else
                        alert("Please check the max resolution, max width is "+maxWidth+", co height is "+coHeight);
//                    currentData.push(sizes);
                    $('#' + id5).prepend(sizes);
                    document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                    resultDivNum = i+vectors.length*4+1;
                }
                
                function checkSchIU(xmlDoc, name)
                {
                    currentData.push(name+"@schemeIdUri");
                    var schIUs = [];
                    var CP = xmlDoc.getElementsByTagName(name);
                    if(CP.length === 0)
                    {
//                        currentData.push("no");
                        currentData.push("x");
                        return;
                    }
//                    currentData.push("yes");
                    for (var k = 0; k < CP.length; k++)
                    {
                        var schIU = CP[k].getAttribute("schemeIdUri");
                        if(schIU === null || schIU === "")
                        {
                            alert("ContentProtection don't have @schemeIdUri!!!");
                        }
                        else
                        {
                            schIUs.push(schIU);
                        }
                    }
                    schIUs = filterResults(schIUs);
                    currentData.push(schIUs);
                }
                
                function checkUTCTiming(xmlDoc)
                {
                    currentData.push("UTCTiming");
                    var values = xmlDoc.getElementsByTagName("UTCTiming");
                    if (values.length > 0)
                        currentData.push("yes");
                    else
                        currentData.push("no");
                }
                
                function checkSegmentTemplate(xmlDoc, attrib)
                {
//                    var dur_exist = "x";
                    var values1 = [];
                    var values2 = [];
                    var segTemp = xmlDoc.getElementsByTagName("SegmentTemplate");
                    if (attrib === "")
                    {
                        var exist = segTemp.length > 0? "yes":"no";
                        currentData.push("SegmentTemplate");
                        currentData.push(exist);
                        return;
                    }
                    for (var k = 0; k < segTemp.length; k++)
                    {
                        var duration = segTemp[k].getAttribute(attrib);
                        if(duration === null || duration === "")
                        {
//                            dur_exist = "no";
                        }
                        else
                        {
                            if (attrib === "media")
                            {
                                if (duration.includes("$Number$"))
                                    values1.push("yes");
                                else
                                    values1.push("no");
                                
                                if (duration.includes("$Time$"))
                                    values2.push("yes");
                                else
                                    values2.push("no");
                            }
                            else if (attrib === "duration")
                            {
                                values1.push(duration);
//                                dur_exist = "yes";
//                                break;
                            }
                            else if (attrib === "presentationTimeOffset")
                                values1.push(duration);
                        }
                    }
                    if (attrib === "media")
                    {
                        values1 = filterResults(values1);
                        currentData.push("SegmentTemplate$Number$");
                        currentData.push(values1);
                        values2 = filterResults(values2);
                        currentData.push("SegmentTemplate$Time$");
                        currentData.push(values2);
                    }
                    else if (attrib === "duration")
                    {                        
                        values1 = filterResults(values1);
                        currentData.push("SegmentTemplate@" + attrib);
                        currentData.push(values1);
//                        currentData.push("SegmentTemplate@duration exist");
//                        currentData.push(dur_exist);
                    }
                    else if (attrib === "presentationTimeOffset")
                    {
                        values1 = filterResults(values1);
                        currentData.push("SegmentTemplate@" + attrib);
                        currentData.push(values1);
                    }
//                    currentData.push(dur_exist);
//                    var exist = (segTemp.length>0)? "yes":"no";
//                    currentData.push(exist);
//                    $('#' + id6).prepend(exist);
//                    document.getElementById('statusContent').innerHTML = "Completed vector " + i;
//                    resultDivNum = i+vectors.length*5+1;
                }
                
                function checkSegmentBase(xmlDoc, attrib)
                {
                    var values1 = [];
                    var segBase = xmlDoc.getElementsByTagName("SegmentBase");
                    for (var k = 0; k < segBase.length; k++)
                    {
                        var value = segBase[k].getAttribute(attrib);
                        if(value === null || value === "")
                        {
//                            dur_exist = "no";
                        }
                        else
                        {
                            if (attrib === "presentationTimeOffset")
                                values1.push(value);
                        }
                    }
                    if (attrib === "presentationTimeOffset")
                    {
                        values1 = filterResults(values1);
                        currentData.push("SegmentBase@" + attrib);
                        currentData.push(values1);
                    }
//                    currentData.push(dur_exist);
//                    var exist = (segTemp.length>0)? "yes":"no";
//                    currentData.push(exist);
//                    $('#' + id6).prepend(exist);
//                    document.getElementById('statusContent').innerHTML = "Completed vector " + i;
//                    resultDivNum = i+vectors.length*5+1;
                }
                
                function checkSegmentTimeline(xmlDoc)
                {
                    var segTemp = xmlDoc.getElementsByTagName("SegmentTimeline");
                    var exist = (segTemp.length>0)? "yes":"no";
                    currentData.push(exist);
                    $('#' + id7).prepend(exist);
                    document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                    resultDivNum = i+vectors.length*6+1;
                }
                
                function printNumPeriods(xmlDoc)
                {
                    currentData.push("Num Periods");
                    var periods = xmlDoc.getElementsByTagName("Period");
                    currentData.push(periods.length);
                    $('#' + id8).prepend(periods.length);
                    document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                    resultDivNum = i+vectors.length*7+1;
                }
                
                function listMimeType(xmlDoc) {
                    var mimeTypes = [];
                    var adaptSets = xmlDoc.getElementsByTagName("AdaptationSet");
                    for (var k = 0; k < adaptSets.length; k++)
                    {
                        var mimeType = adaptSets[k].getAttribute("mimeType");
                        if (mimeType === null || mimeType === "" ) //no mimeType in Adaptation set level
                        {
                            //check in representation level
                            var repSets = adaptSets[k].getElementsByTagName("Representation");
                            for (var t = 0; t < repSets.length; t++)
                            {
                                mimeType = repSets[t].getAttribute("mimeType");
                                if (mimeType === null || mimeType === "") //no mimeType in Representation set level
                                {
                                    //TODO may have to check in sub representation level
                                    var subRepSets = repSets[t].getElementsByTagName("SubRepresentation");
                                    if (subRepSets.length>0)
                                        alert("error!!! no mimeType!!!");
                                }
                                else{
                                    mimeTypes.push(mimeType);
                                }
                            }
                        }
                        else
                        {
                            mimeTypes.push(mimeType);
                        }
                    }
                    mimeTypes = filterResults(mimeTypes);
                    currentData.push("mimeType(s)");
                    currentData.push(mimeTypes);
                    $('#' + id9).prepend(mimeTypes);
                    document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                    resultDivNum = i+vectors.length*8+1;
                }
                
                function maxNumAdapt(xmlDoc)
                {
                    var numAdapt = [];
                    //go through every Period
                    var periods = xmlDoc.getElementsByTagName("Period");
                    for (var k = 0; k < periods.length; k++)
                    {
                        //loop every Adaptation set
                        var adaptSets = periods[k].getElementsByTagName("AdaptationSet");
                        if (adaptSets.length > 0) //TODO currently don't consider xlink etc.
                        {
                            numAdapt.push(adaptSets.length);
                        }
                    }
                    currentData.push(numAdapt.max());
                    $('#' + id).prepend(numAdapt.max());
                    document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                    resultDivNum = i+1;
                }
                
                function maxNumRepr(xmlDoc)
                {
                    //guarantee that there won't be any empty array
                    var numAudioReprArr = [0];
                    var numVideoReprArr = [0];
                    var numAudioRepr = 0;
                    var numVideoRepr = 0;
                    //go through every Adaptation Set
                    var adaptSets = xmlDoc.getElementsByTagName("AdaptationSet");
                    for (var k = 0; k < adaptSets.length; k++)
                    {
                        //skip if the adaptation set is for subtiles
                        var role = adaptSets[k].getElementsByTagName("Role");
                        if (role.length > 0)
                        {
                            var numSubtitle = 0;
                            for (var t = 0; t < role.length; t++)
                            {
                                if (role[t].getAttribute("value") === "subtitle")
                                    numSubtitle++;
                                else
                                    break;
                            }
                            if (numSubtitle === role.length)
                                continue;
                        }
                        
                        if (adaptSets[k].getAttribute("contentType") === "video" || adaptSets[k].getAttribute("mimeType") === "video/mp4")
                        {
                            numVideoRepr = adaptSets[k].getElementsByTagName("Representation").length;
                            numVideoReprArr.push(numVideoRepr);
                        }
                        else if (adaptSets[k].getAttribute("contentType") === "audio" || adaptSets[k].getAttribute("mimeType") === "audio/mp4")
                        {
                            numAudioRepr = adaptSets[k].getElementsByTagName("Representation").length;
                            numAudioReprArr.push(numAudioRepr);
                        }
                        else  //count in Representation level
                        {
                            numVideoRepr = 0;
                            numAudioRepr = 0;
                            //loop every Representation set
                            var repSets = adaptSets[k].getElementsByTagName("Representation");
                            for (var t = 0; t < repSets.length; t++)
                            {            
                                if (repSets[t].getAttribute("contentType") === "video" || repSets[t].getAttribute("mimeType") === "video/mp4")
                                {
                                    numVideoRepr++;
                                }
                                else if (repSets[t].getAttribute("contentType") === "audio" || repSets[t].getAttribute("mimeType") === "audio/mp4")
                                {
                                    numAudioRepr++;
                                }
                                else
                                {
                                    alert("error!!! neither video nor audio representation set!!!");
                                }
                            }
                            numVideoReprArr.push(numVideoRepr);
                            numAudioReprArr.push(numAudioRepr);
                        }
                    }
                    currentData.push("max num. Video Repr.");
                    currentData.push(numVideoReprArr.max());
                    currentData.push("max num. Audio Repr.");
                    currentData.push(numAudioReprArr.max());
//                    currentData.push(numVideoReprArr.max() + "+" + numAudioReprArr.max());
                    $('#' + id2).prepend(numVideoReprArr.max() + "+" + numAudioReprArr.max());
                    document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                    resultDivNum = i+vectors.length+1;
                }
                
                function checkDashInfo(xmlDoc)
                {
                    var mpd = xmlDoc.getElementsByTagName("MPD");
                    var profiles = mpd[0].getAttribute("profiles");
                    var type = mpd[0].getAttribute("type");
                    currentData.push("Dash Profile(s)");
                    currentData.push(profiles);
                    currentData.push("MPD type");
                    currentData.push(type);
                    currentData.push("@minimumUpdatePeriod");
                    var minimumUpdatePeriod = mpd[0].getAttribute("minimumUpdatePeriod");
                    if (minimumUpdatePeriod === null || minimumUpdatePeriod === "")
                    {
                        currentData.push("x");
                    }
                    else
                        currentData.push(minimumUpdatePeriod);
                    
                    currentData.push("@timeShiftBufferDepth");
                    var timeShiftBufferDepth = mpd[0].getAttribute("timeShiftBufferDepth");
                    if (timeShiftBufferDepth === null || timeShiftBufferDepth === "")
                    {
                        currentData.push("x");
                    }
                    else
                        currentData.push(timeShiftBufferDepth);
                }
                
                //NOTE currently check video related parameters
                function printAdapt(xmlDoc, name)
                {   
                    if (name === "par")
                    {
                        var contentComp = xmlDoc.getElementsByTagName("ContentComponent");
                        if (contentComp.length>0)
                            alert("ContentComponent present, check @par");
                    }
                        
                    var values = [];
                    var adaptSets = xmlDoc.getElementsByTagName("AdaptationSet");
                    for (var k = 0; k < adaptSets.length; k++)
                    {
                        if (name === "par")
                        {
                            //skip if the adaptation set is for subtiles
                            var role = adaptSets[k].getElementsByTagName("Role");
                            if (role.length > 0)
                            {
                                var numSubtitle = 0;
                                for (var t = 0; t < role.length; t++)
                                {
                                    if (role[t].getAttribute("value") === "subtitle")
                                        numSubtitle++;
                                    else
                                        break;
                                }
                                if (numSubtitle === role.length)
                                    continue;
                            }

                            //skip audio Adaptation set
                            if (adaptSets[k].getAttribute("contentType") === "audio" || adaptSets[k].getAttribute("mimeType") === "audio/mp4")
                                continue;
                        }
                        
                        var value = adaptSets[k].getAttribute(name);
                        if (value === null || value === "") //no value info in Adaptation set level
                        {
                            if (name === "segmentAlignment" || name === "subsegmentAlignment")
                            {
                                values.push("false"); //default value
                            }
                            else if (name === "subsegmentStartsWithSAP")
                            {
                                values.push(0);
                            }
                        }
                        else
                        {
                            values.push(value);
                        }
                    }
                    values = filterResults(values);
                    currentData.push(values);
                }    
                
                //NOTE currently check video related parameters
                function printAdaptRep(xmlDoc, name, type)
                {   
                    var values = [];
                    var adaptSets = xmlDoc.getElementsByTagName("AdaptationSet");
                    for (var k = 0; k < adaptSets.length; k++)
                    {
//                        //skip if the adaptation set is for subtiles
//                        var role = adaptSets[k].getElementsByTagName("Role");
//                        if (role.length > 0)
//                        {
//                            var numSubtitle = 0;
//                            for (var t = 0; t < role.length; t++)
//                            {
//                                if (role[t].getAttribute("value") === "subtitle")
//                                    numSubtitle++;
//                                else
//                                    break;
//                            }
//                            if (numSubtitle === role.length)
//                                continue;
//                        }
                        
                        if (type === "video")
                        {
                            //skip audio Adaptation set
                            if (adaptSets[k].getAttribute("contentType") === "audio" || adaptSets[k].getAttribute("mimeType") === "audio/mp4")
                                continue;
                        }
                        else if (type === "audio")
                        {
                            //skip video Adaptation set
                            if (adaptSets[k].getAttribute("contentType") === "video" || adaptSets[k].getAttribute("mimeType") === "video/mp4")
                                continue;
                        }
                        
                        var value = adaptSets[k].getAttribute(name);
                        if (value === null || value === "") //no value info in Adaptation set level
                        {
                            //check in representation level
                            var repSets = adaptSets[k].getElementsByTagName("Representation");
                            for (var t = 0; t < repSets.length; t++)
                            {
                                if (type === "video")
                                {
                                    //skip audio Representation set
                                    if (repSets[t].getAttribute("contentType") === "audio" || repSets[t].getAttribute("mimeType") === "audio/mp4")
                                        continue;
                                }
                                else if (type === "audio")
                                {
                                    //skip video Representation set
                                    if (repSets[t].getAttribute("contentType") === "video" || repSets[t].getAttribute("mimeType") === "video/mp4")
                                        continue;
                                }
                                
                                value = repSets[t].getAttribute(name);
                                if (value === null || value === "") //no value info in Representation set level
                                {
                                    //TODO may have to check in sub representation level
                                    var subRepSets = repSets[t].getElementsByTagName("SubRepresentation");
                                    if (subRepSets.length>0)
                                        alert("error!!! no " + name);
                                }
                                else{
                                    values.push(value);
                                }
                            }
                        }
                        else
                        {
                            values.push(value);
                        }
                    }
                    values = filterResults(values);
                    currentData.push(values);
                }
                
                function printRep(xmlDoc, name, type)
                {   
                    var values = [];
                    var adaptSets = xmlDoc.getElementsByTagName("AdaptationSet");
                    for (var k = 0; k < adaptSets.length; k++)
                    {
                        //check in representation level
                        var repSets = adaptSets[k].getElementsByTagName("Representation");
                        for (var t = 0; t < repSets.length; t++)
                        {
                            if (adaptSets[k].getAttribute("contentType") === type || adaptSets[k].getAttribute("mimeType") === type+"/mp4" || repSets[t].getAttribute("contentType") === type || repSets[t].getAttribute("mimeType") === type+"/mp4")
                            {
                                var value = repSets[t].getAttribute(name);
                                if (value === null || value === "") //no value info in Representation set level
                                {
                                    alert("error!!! no " + name);
                                }
                                else{
                                    values.push(value);
                                }
                            }
                        }
                    }
                    values = filterResults(values);
                    currentData.push(values);
                }
                
                function countTotalDuration(xmlDoc)
                {
                    var mpd = xmlDoc.getElementsByTagName("MPD");
                    currentData.push("Total duration")
                    // don't process for dynamic MPD
                    if (mpd[0].getAttribute("type") === "dynamic")
                    {
                        currentData.push("x");
                        return;
                    }
                    var mediaPresentationDuration = mpd[0].getAttribute("mediaPresentationDuration");
                    if (mediaPresentationDuration === null || mediaPresentationDuration === "")
                    {
                        var periods = xmlDoc.getElementsByTagName("Period");
                        if (periods.length>1)
                        {
                            currentData.push("x");
                            alert("notice: multi period!!!");
                        }
                        else
                        {
                            var duration = periods[0].getAttribute("duration");
                            if (duration === null || duration === "")
                            {
                                currentData.push("x");
                                alert("notice: single period but has no @duration");
                            }
                            else
                                currentData.push(duration);
                        }
                    }
                    else
                        currentData.push(mediaPresentationDuration);
                }
                
                function countBaseURL(xmlDoc)
                {
                    var maxMpd = 0;
                    var maxPeriod = [];
                    var maxAdapt = [];
                    var maxRep = 0;
                    var mpd = xmlDoc.getElementsByTagName("MPD");
                    var mpdLevel = (mpd[0].getElementsByTagName("BaseURL")).length;
                    if (mpdLevel > 0)
                        maxMpd = mpdLevel; //the total number, might be subtracted
                    
                    var Periods = mpd[0].getElementsByTagName("Period");
                    for (var p = 0; p < Periods.length; p++)
                    {
                        var periodLevel = (Periods[p].getElementsByTagName("BaseURL")).length;
                        if (periodLevel > 0)
                        {
                            maxMpd = maxMpd - periodLevel;
                            maxPeriod.push(periodLevel);
//                            if (periodLevel > maxPeriod)
//                                maxPeriod = periodLevel;
                        }
                    
                        var adaptSets = mpd[0].getElementsByTagName("AdaptationSet");
                        for (var k = 0; k < adaptSets.length; k++)
                        {
                            var adaptLevel = (adaptSets[k].getElementsByTagName("BaseURL")).length;
                            if (adaptLevel > 0)
                            {
                                maxPeriod[maxPeriod.length-1] -= adaptLevel;
                                maxAdapt.push(adaptLevel);
//                                if (adaptLevel > maxAdapt)
//                                    maxAdapt = adaptLevel;
                            }

                            //check in representation level
                            var repSets = adaptSets[k].getElementsByTagName("Representation");
                            for (var t = 0; t < repSets.length; t++)
                            {
                                var repLevel = (repSets[t].getElementsByTagName("BaseURL")).length;
                                if (repLevel > 0)
                                {
                                    maxAdapt[maxAdapt.length-1] -= repLevel;
                                    if (repLevel > maxRep)
                                        maxRep = repLevel;
                                }
                            }
                        }
                    }
                    
                    maxPeriod = maxPeriod.max();
                    maxAdapt = maxAdapt.max();
                    var maxNum = Math.max(maxMpd, maxPeriod, maxAdapt, maxRep);
                    
                    if (maxNum === 0)
                    {
                        //TODO may have to check in sub representation level
                        var subRepSets = xmlDoc.getElementsByTagName("SubRepresentation");
                        if (subRepSets.length>0)
                            alert("error!!! may check BaseURL in SubRepresentation");
                    }
                    currentData.push("Max num. BaseURL");
                    currentData.push(maxNum);
                }
                
                function checkPeriod(xmlDoc)
                {
                    var value = [];
                    var existXlink = "no";
                    var existAsset = "no";
                    var periods = xmlDoc.getElementsByTagName("Period");
                    for (var k = 0; k < periods.length; k++)
                    {
                        if (periods[k].getAttribute("xlink:href"))
                        {
                            existXlink = "yes";
                        }

                        var xlink = periods[k].getAttribute("xlink:actuate");
                        if (xlink)
                            value.push(xlink);
                        
                        if ((periods[k].getElementsByTagName("AssetIdentifier")).length > 0)
                        {
                            existAsset = "yes";
                        }
                    }
                    value = filterResults(value);
                    currentData.push("xlink:href");
                    currentData.push(existXlink);
                    currentData.push("xlink:actuate");
                    currentData.push(value);
                    currentData.push("AssetIdentifier");
                    currentData.push(existAsset);
                }
                
                function contentPro(xmlDoc)
                {
                    currentData.push("cenc:pssh");
                    var value = xmlDoc.getElementsByTagName("cenc:pssh");
                    if (value.length > 0)
                        currentData.push("yes");
                    else
                        currentData.push("no");
                }
                
            }
            else  // Creating Reference results.
            {
//                downloadCSV({data:finalData});
                document.getElementById('statusContent').innerHTML = "Completed checking all test vectors";
                
                $.post(
                    "save_file.php",
                    {filename:"test.csv", content:finalData}
                ).done(function (result) {
                    document.getElementById("results").innerHTML = result;
                });
            }
        }
    }
</script>

<br>
<p id="Testvectors">Test vectors :</p><br>
<textarea name="Text1" cols="110" rows="40" id='vectors'></textarea>
<br><input type=button id="Start" value="Start Testing" onclick="testing()">  
<!--<div id="tick" style="position: absolute; left: 900px"></div>-->
<p id="status">Status :</p>
<p id="statusContent"></p>
<p id="results">Results</p>
<!--<p id="results">@contentType exists?</p>
<p id="results2">@mimeType correct?</p>
<!--<p id="results">maxNumAdapt</p>
<p id="results2">maxNumRepr(V+A)</p>
<p id="results3">Video codecs</p>
<p id="results4">Audio codecs</p>
<p id="results5">Resolution</p>
<p id="results6">SegmentTemplate</p>
<p id="results7">SegmentTimeline</p>
<p id="results8">NumPeriods</p>
<p id="results9">mimeType</p>-->
</body>

</html>
