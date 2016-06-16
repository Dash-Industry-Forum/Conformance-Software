<!DOCTYPE html>
<html>
<head>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>    
<script src="http://code.jquery.com/jquery-latest.js"></script>
<link rel="stylesheet" type="text/css" href="TestFramework.css">
</head>
<body> 
    <h2> MPD Feature Check </h2>
<script>
    var resultDivNum = 0;
    
    window.onload = function()
    {
        document.getElementById('vectors').value = <?php $file = file_get_contents('DefaultVectorList.txt');
        echo json_encode($file); ?>;
    };
    
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
                document.body.insertAdjacentHTML('beforeend', div);
                document.getElementById('statusContent').innerHTML = "Running vector " + i;
                
                //another column for test results
                var id2 = 'resultDiv' + (i+vectors.length);
                var div2 = '<div id= ' + id2 + ' style="position: absolute;left:1200px; top:' + top + ';"></div>';
                document.body.insertAdjacentHTML('beforeend', div2);

                $.post(
                    "loadMPD.php",
                    {url: vectors[i - 1]}
                ).done(function (response) {
//                    console.log(response);
                    
                    if (response.trim() !== 'error')
                    {
                        var xml = $.parseXML(response);
                        checkContentType(xml);
                        checkMimeType(xml);
                    }else
                    {
                        $('#' + id).prepend('<b>Broken Link</b>');
                    }
                    i++;
                    ajaxcall();
                });


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
                        document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                        resultDivNum = i+1;
//                        alert("@contentType missing for Test Vector " + i);
                    }
                    else
                    {
                        $('#' + id).prepend('<img id="theImg" src="right.jpg" />');
                        document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                        resultDivNum = i+1;
                    }
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
                            if (!periods[k].getElementsByTagName("xlink:href") || !periods[k].getElementsByTagName("xlink:actuate"))
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
                        document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                        resultDivNum = i+vectors.length+1;
                    }
                    else
                    {
                        $('#' + id2).prepend('<img id="theImg" src="right.jpg" />');
                        document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                        resultDivNum = i+vectors.length+1;
                    }
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
            }
            else  // Creating Reference results.
            {
                document.getElementById('statusContent').innerHTML = "Completed checking all test vectors";
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
<p id="results">@contentType exists?</p>
<p id="results2">@mimeType correct?</p>
</body>

</html>
