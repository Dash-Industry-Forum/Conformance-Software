<!DOCTYPE html>
<html>
    <head>
        <title>MPD Feature Check</title>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="getMPDParameters.js" type="text/javascript"></script> 
        <!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>-->    
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
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                        var xml = xmlhttp.responseXML;

                        currentData = getMPDParameters(xml, currentData);
                        document.getElementById('statusContent').innerHTML = "Completed vector " + i;
                        finalData.push(currentData);
                    }
                    i++;
                    ajaxcall();
                };
                xmlhttp.open("GET", vectors[i-1], true);
                xmlhttp.send();
                
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
