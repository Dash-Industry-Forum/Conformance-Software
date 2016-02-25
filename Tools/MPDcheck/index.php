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

                $.post(
                    "loadMPD.php",
                    {url: vectors[i - 1]}
                ).done(function (response) {
                    console.log(response);
                    
                    if (response.trim() !== 'error')
                    {
                        var xml = $.parseXML(response);
                        checkContentType(xml);
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
//                        j++;
                        resultDivNum = i+1;
//                        alert("@contentType missing for Test Vector " + i);
                    }
                    else
                    {
                        $('#' + id).prepend('<img id="theImg" src="right.jpg" />');
                        document.getElementById('statusContent').innerHTML = "Completed vector " + i;
//                        j++;
                        resultDivNum = i+1;
                    }
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
<div id="tick" style="position: absolute; left: 900px"></div>
<p id="status">Status :</p>
<p id="statusContent"></p>
<p id="results">@contentType exists?</p>
</body>

</html>
