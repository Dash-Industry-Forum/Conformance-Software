<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
-->

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <meta charset="utf-8" />
    <head>
        <title> DASH Conformance Test</title>
        <meta name="description" content="DASH Conformance">
        <meta name="keywords" content="DASH,DASH Conformance,DASH Validator">
        <meta name="author" content="Nomor Research GmbH">
        <link rel="icon" href="favicon.ico?v1" type="image/x-icon" />
    </head>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js"></script>
    <!--link rel="stylesheet" href="/resources/demos/style.css" /-->

    <link rel="STYLESHEET" type="text/css" href="tree/dhtmlxTree/codebase/dhtmlxtree.css">
    <script type="text/javascript"  src="tree/dhtmlxTree/codebase/dhtmlxtree.js"></script>
    <script type="text/javascript" src="tree/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
    <script type="text/javascript" src="tree/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>
    
<?php 
    if(isset($_REQUEST['mpdurl']))
    {
        $url = $_REQUEST['mpdurl'];     // To get url from POST request.
    }
    else
        $url = "";
    
    if(isset($_REQUEST['cmaf']) && file_get_contents("cmaf_OnOff.txt")==1)
    {
        $cmaf = $_REQUEST['cmaf'];
    }
    else
        $cmaf = "";
?>

<script type="text/javascript">

    var url = "";

    window.onload = function()
    {
        //Display the version number after refering to change log.     
        $.post(
                "getIOPVersion.php",
        ).done(function(response){
            document.getElementById("footerVersion").innerHTML=response;
        });

        url = "<?php echo $url; ?>";
        if(url !== "")
        {
            document.getElementById("urlinput").value=url;
            submit();
        }
    }

    function fixImage(id){
        switch(tree.getLevel(id)){
            case 1:
                tree.setItemImage2(id,'folderClosed.gif','folderOpen.gif','folderClosed.gif');
                break;
            case 2:
                tree.setItemImage2(id,'folderClosed.gif','folderOpen.gif','folderClosed.gif');
                break;
            case 3:
                tree.setItemImage2(id,'folderClosed.gif','folderOpen.gif','folderClosed.gif');
                break;
            default:
                tree.setItemImage2(id,'leaf.gif','folderClosed.gif','folderOpen.gif');
                break;
        }
    }

</script>
    
    
<style>

    .mytext {
        width: 600px;
    }
    div.hidden{
        display: none;
    }
    div.normal{
        display: block;
    }
    #tot{
        text-align:center;
    }
    #groupA{
        text-align:center;
        margin-left:-5%;
        margin-top:1%;
    }
    .box{
        display:inline-block;
        text-align:center;
        width:600px;
        height:50px;
        border: 1px solid rgba(0,0,0,0.08);
        background-color: grey;
        margin-top:0.2%;
    }
    #progressbar{
        text-align:center;
    }
    #to{
        text-align:center;
        border-width:medium;
    }
    #dash{
        text-align:center;
        margin-top: 15%;
        
    }
    p.sansserif {
        font-family: Arial, Helvetica, sans-serif;
    }
    #btn8 {
        -moz-box-shadow:inset 0px 1px 0px 0px #dcecfb;
        -webkit-box-shadow:inset 0px 1px 0px 0px #dcecfb;
        box-shadow:inset 0px 0px 0px 0px #dcecfb;
        background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #99ccff), color-stop(1, #80b5ea) );
        background:-moz-linear-gradient( center top, #bddbfa 5%, #80b5ea 100% );
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#bddbfa', endColorstr='#80b5ea');
        background-color:#bddbfa;
        -webkit-border-top-left-radius:0px;
        -moz-border-radius-topleft:0px;
        border-top-left-radius:3px;
        -webkit-border-top-right-radius:0px;
        -moz-border-radius-topright:0px;
        border-top-right-radius:3px;
        -webkit-border-bottom-right-radius:0px;
        -moz-border-radius-bottomright:0px;
        border-bottom-right-radius:3px;
        -webkit-border-bottom-left-radius:0px;
        -moz-border-radius-bottomleft:0px;
        border-bottom-left-radius:3px;
        text-indent:-1px;
        border:1px solid #84bbf3;
        display:inline-block;
        color:#ffffff;
        font-family:Helvetica;
        font-size:16px;
        font-weight:bold;
        font-style:italic;
        height:50px;
        line-height:40px;
        width:100px;
        text-decoration:none;
        text-align:center;
        text-shadow:1px 1px 1px #183d61;
        position:absolute;
        margin-left:0.5%;
        margin-top: -0.7%;
    }
    #btn8:hover:enabled {
        background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #80b5ea), color-stop(1, #bddbfa) );
        background:-moz-linear-gradient( center top, #80b5ea 5%, #bddbfa 100% );
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#80b5ea', endColorstr='#bddbfa');
        background-color:#80b5ea;
    }
    #btn8:active:enabled {
        /*position:relative;
        top:1px;*/
    }
    #btn8:disabled {
        background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #C0C0C0), color-stop(1, #808080) );
        background:-moz-linear-gradient( center top, #808080 5%, #808080 100% );
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#C0C0C0', endColorstr='#808080');
        background-color:#808080;
        color:#C0C0C0;
        -moz-box-shadow:inset 0px 1px 0px 0px #808080;
        -webkit-box-shadow:inset 0px 1px 0px 0px #808080;
        box-shadow:inset 0px 1px 0px 0px #808080;
    }
    input{
        text-align:center;
    }
    #not{
        position:center;
    }
    .footer {
        height: 200px; 
        overflow: hidden; 
        margin-top:10%;
    }
    #dynamictable{
        position:absolute;
        top:280px;
        right:40px;
    }
    .box__dragndrop{
        display: none;
    }
    .box.has-advanced-upload {
        background-color: #e6e6e6;
        outline: 1px dashed grey;
        outline-offset: -3px;
    }
    .box.has-advanced-upload .box__dragndrop {
        display: inline;
    }
    .box.is-dragover {
        background-color: grey;
    }
    .box__file {
	width: 0.1px;
	height: 0.1px;
	opacity: 0;
	overflow: hidden;
	position: absolute;
	z-index: -1;
    }
    .box__file + label {
        position: relative;
        margin-top:2.5%;
        margin-left:3%;
        display: inline-block;
        cursor: pointer; /* "hand" cursor */
    }
    .box__button {
        position: absolute;
        margin-top:0.8%;
        margin-left: 8%;
        display: none;
    }

</style>
    
    
<body>
<div id="dash">
    <br>
    <img id="img2" border="0" src="dashlogo.jpeg" alt ="DASH Conformance" width="543" height="88" >
    <img id="img2" border="0" src="Dash1.jpeg" width="191" height="61" >
    <br>    <br>
</div>
<p align="center" class="sansserif">Validation (Conformance check) of ISO/IEC 23009-1 MPEG-DASH MPD and Segments</p>
<div id="groupA">
    <div>
        <input type="text" id='urlinput' name="urlinput" class="mytext" placeholder="Enter MPD URL" onkeyup="CheckKey(event)"/>

    </div>
    <div>
        <div class="box"  >
            <div class="box__input" id="drop_div">
                <input class="box__file" type="file" name="file" id="file" />
                <label for="file"><strong>Choose a file</strong><span class="box__dragndrop"> or drag it here</span>.</label>
                <button class="box__button" type="submit">Upload</button>
            </div>
        </div>
        <button id="btn8" onclick="submit()">Submit</button>
    </div>
  <!--input type="text" id='urlinput' name="urlinput" class="mytext" value="http://localhost/content/TestCases/1b/thomson-networks/2/manifest.mpd" onkeyup="CheckKey(event)"/-->
  <!--input type="text" id='urlinput' name="urlinput" class="mytext" value="http://dash.edgesuite.net/dash264/TestCases/1a/qualcomm/1/MultiRate.mpd" onkeyup="CheckKey(event)"/-->
  <!--input type="text" id='urlinput' name="urlinput" class="mytext" value="http://10.4.193.185/Content/TestCases/1b/qualcomm/1/MultiRate_Broken.mpd" onkeyup="CheckKey(event)"/-->

    <!--b>or</b>

    <input type="file" name="afile" id="afile" /-->
    <!--<input type="file" id="selectfile" /> Uploading local mpd for testing -->
    
    <form action="">
        <p class="sansserif"><input type="checkbox" id="mpdvalidation" class = "validation" value="0">MPD conformance only</p><br>
    </form>
    <a id="dynamic" href="url" target="_blank" style="visibility:hidden;" >Dynamic timing validation</a>

</div>

<div id="progressbar" style="width:100px;background:#FFFFF;"></div>

<div id = "not">
    <br>    <br>
</div>

<div id="to" >
    <p align="center"></p>
    <p id="par" class="sansserif" style="visibility:hidden;">Loading....</p>
    <p id="profile" class="sansserif" style="visibility:hidden;">Profiles: </p>
    <a id="list" href="url" target="_blank" style="visibility:hidden;" >Feature list</a>
</div>
    
<table>
    <tr>
        <td valign="top">
            <div id="treeboxbox_tree" style="width:500px; height:400px;background-color:#0000;border :none;; overflow:auto;"></div>
        </td>
        
        <td rowspan="2" style="padding-left:25" valign="top">
        </td>
    </tr>
</table>

<script type="text/javascript">
var progressXMLRequest;
var progressXML;
var progressTimer;
var current = 0;
var dirid="";
var kidsloc=[];
var lastloc = 0;
var mpdprocessed = false;
var counting =0;
var representationid =1;
var adaptationid = 1;
var hinindex = 1;
var repid =[];	
var totarr = [];
var adaptid=[];
var file,fd,xhr;
var uploaded = false;
var numPeriods = 0;
var dynamicsegtimeline = false;
var segmentListExist = false;
var SessionID = "id"+Math.floor(100000 + Math.random() * 900000);
var totarrstring=[];
var xmlDoc_progress;
var progressSegmentsTimer;
var pollingTimer;
var ChainedToUrl;
var cmaf = "<?php echo $cmaf; ?>";


/////////////////////////////////////////////////////////////
//Check if 'drag and drop' feature is supported by the browser, if not, then traditional file upload can be used.
var isAdvancedUpload = function() {
    var div = document.createElement('div');
    return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
}();

var $form = $('.box');
var droppedFile = false;

if (isAdvancedUpload) {
    $form.addClass('has-advanced-upload');
    $form.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
    })
    .on('dragover dragenter', function() {
        $form.addClass('is-dragover');
    })
    .on('dragleave dragend drop', function() {
        $form.removeClass('is-dragover');
    })
    .on('drop', function(e) {
        droppedFile = e.originalEvent.dataTransfer.files;
        showFiles( droppedFile );
        $form.trigger('submit');
    });
}

$('.box__file').on('change', function(e) { // when drag & drop is NOT supported
    droppedFile=e.target.files;
    showFiles( droppedFile );
    $form.trigger('submit');
});

//document.querySelector('#btn8').addEventListener('change', function(e) {
$form.on('submit', function(e) {
 
    file=droppedFile[0];
    //file = this.files[0];
    fd = new FormData();
    fd.append("afile", file);
    fd.append("sessionid", JSON.stringify(SessionID));
    //xhr = new XMLHttpRequest();
    // xhr.open('POST', 'process.php', true);
    // xhr.onload = function() {
    uploaded=true;
    submit();

    //};
    //xhr.send(fd);
    //}, false);
});

var $input    = $form.find('input[type="file"]'),
    $label    = $form.find('label'),
    showFiles = function(files) {
        $label.text(files[0].name);
    };

///////////////////////////////////////////////////////////////

function button()
{
    current = current+1;
    $( "#progressbar" ).progressbar({
      value: current
    });
}

function CheckKey(e) //receives event object as parameter
{
   var code = e.keyCode ? e.keyCode : e.which;
   if((code === 13) && (document.getElementById("btn8").disabled == false))
   {
       submit();
   }
}

function createXMLHttpRequestObject(){ 
    var xmlHttp; // xmlHttp will store the reference to the XMLHttpRequest object
    try{         // try to instantiate the native XMLHttpRequest object
        xmlHttp = new XMLHttpRequest(); // create an XMLHttpRequest object
    }
    catch(e) {
        try     // assume IE6 or older
        {
            xmlHttp = new ActiveXObject("Microsoft.XMLHttp");
        }
        catch(e) { }
    }
    if (!xmlHttp)       // return the created object or display an error message
        alert("Error creating the XMLHttpRequest object.");
    else
        return xmlHttp;
}

function  progressEventHandler(){
    if (progressXMLRequest.readyState == 4){    // continue if the process is completed
        if (progressXMLRequest.status == 200) {       // continue only if HTTP status is "OK" 
            try {
        
                response = progressXMLRequest.responseXML;          // retrieve the response

                // do something with the response
                progressXML = progressXMLRequest.responseXML.documentElement;

                var progressPercent = progressXML.getElementsByTagName("percent")[0].childNodes[0].nodeValue;
                var dataProcessed = progressXML.getElementsByTagName("dataProcessed")[0].childNodes[0].nodeValue;
                var dataDownloaded = progressXML.getElementsByTagName("dataDownloaded")[0].childNodes[0].nodeValue;

                dataProcessed = Math.floor( dataProcessed / (1024*1024) );
                dataDownloaded = Math.floor( dataDownloaded / (1024) );
                //Get currently running Adaptation and Representation numbers.
                var lastRep = progressXML.getElementsByTagName("CurrentRep")[0].childNodes[0].nodeValue;
                var lastAdapt =progressXML.getElementsByTagName("CurrentAdapt")[0].childNodes[0].nodeValue;
                
                var progressText;
                if (lastRep == 1 && lastAdapt == 1 && progressPercent == 0 && dataDownloaded == 0 && dataProcessed == 0) //initial state
                    progressText = "Processing MPD, please wait...";
                else
                    progressText = "Processing Representation "+lastRep+" in Adaptationset "+lastAdapt+", "+progressPercent+"% done ( "+dataDownloaded+" KB downloaded, "+dataProcessed+" MB processed )";

		if( numPeriods > 1 )
		{
                    progressText = progressText + "<br><font color='red'> MPD with multiple Periods (" + numPeriods + "). Only segments of the first period will be checked.</font>"
		}
		
                if( dynamicsegtimeline)
		{
                    progressText = progressText + "<br><font color='red'> Segment timeline for type dynamic is not supported, only MPD will be tested. </font>"
		}
                
                if(segmentListExist)
		{
                    progressText = progressText + "<br><font color='red'> SegmentList is not supported, only MPD will be tested. </font>"
		}
                
                document.getElementById("par").innerHTML=progressText;
                
                //update only once
                if (document.getElementById("profile").innerHTML === "Profiles: ")
                {
                    var profileList = progressXML.getElementsByTagName("Profile")[0].childNodes[0].nodeValue;
                    document.getElementById("profile").innerHTML="Profiles: " + profileList;            
                    document.getElementById('profile').style.visibility='visible';
                }
            }
            catch(e)
            {
                ;//alert("Error processing: " + e.toString());          // display error message
            }
        }
        else
        {
            ;//alert("" + );        // display status message
        }
    }
}

function progressupdate()
{
    progressXMLRequest=createXMLHttpRequestObject();
    if (progressXMLRequest)     // continue only if xmlHttp isn't void
    {
        try          // try to connect to the server
        {
            var progressDocURL='temp/'+dirid+'/progress.xml';
            var now = new Date();
            progressXMLRequest.open("GET", progressDocURL += (progressDocURL.match(/\?/) == null ? "?" : "&") + now.getTime(), false);  
            //initiate server request, trying to bypass cache using tip from 
            //https://developer.mozilla.org/es/docs/XMLHttpRequest/Usar_XMLHttpRequest#Bypassing_the_cache,
            progressXMLRequest.onreadystatechange = progressEventHandler;
            progressXMLRequest.send(null);
        }
        catch (e)      // display an error in case of failure
        {
            ;//alert("Failed loading progress\n" + e.toString());
        }
    }
}

function submit()
{
    document.getElementById("dash").style.marginTop="1%";
    mpdprocessed = false;
    url = document.getElementById("urlinput").value; 
 
    if (uploaded===true)
	url="upload";
    
    var stringurl = [];
	
    stringurl[0] = url;

    stringurl[1] =  "mpdvalidator";
	
    if($("#mpdvalidation").is(':checked'))
        stringurl[2] = 1;
    else
   	stringurl[2] = 0 ;
    stringurl[4] = "<?php echo $cmaf; ?>";
    initVariables();
    setUpTreeView();
    setStatusTextlabel("Processing...");
    document.getElementById("btn8").disabled="true";
    document.getElementById("drop_div").disabled="true";
    document.getElementById('list').style.visibility='hidden';
    //document.getElementById('img').style.visibility='visible';
    //document.getElementById('par').style.visibility='visible';
//    console.log(stringurl);
    //Generate a random folder name for results in "temp" folder
    dirid="id"+Math.floor((Math.random() * 10000000000) + 1);
   
    if(uploaded===true){ // In the case of file upload.
        fd.append("foldername", dirid);
        fd.append("urlcode", JSON.stringify(stringurl));
        $.ajax ({
            type: "POST",
            url: "process.php",
            data: fd,
            contentType: false,
            processData: false
        });
    }else{  // Pass to server only, no JS response model.
           UrlExists(stringurl[0], function(urlStatus){
                 //console.log(urlStatus);
                if(urlStatus === 200 && stringurl[0]!=""){
                    $.post("process.php",{urlcode:JSON.stringify(stringurl),sessionid:JSON.stringify(SessionID),foldername: dirid});
                }
                else{ //if(urlStatus === 404){
                   window.alert("Error loading the MPD, please check the URL.");
                   clearInterval( pollingTimer);	
                   finishTest(); 
                }
            });
      
        //$.post("process.php",{urlcode:JSON.stringify(stringurl),sessionid:JSON.stringify(SessionID),foldername: dirid});
    }
    
     //Start polling of progress.xml for the progress percentage results.
    progressTimer = setInterval(function(){progressupdate()},100);
    pollingTimer = setInterval(function(){pollingProgress()},800);//Start polling of progress.xml for the MPD conformance results.
}

function pollingProgress()
{
    xmlDoc_progress=loadXMLDoc("temp/"+dirid+"/progress.xml");

    if (xmlDoc_progress === null)
        return;
    else
        var MPDError=xmlDoc_progress.getElementsByTagName("MPDError");

    if(MPDError.length === 0)
        return;
    else    
        totarrstring=MPDError[0].childNodes[0].nodeValue;

//    console.log("process_returned:");
//    console.log(totarrstring);
    if (totarrstring == 1)//Check for the error in MPD loading.
    {
        window.alert("Error loading the MPD, please check the URL.");
        clearInterval( pollingTimer);	
        finishTest();            
        return false;
    }
    
//    console.log("dirid=");
//    console.log(dirid);

   //Get MPD Conformance results from progress.xml file.
    var MPDtotalResultXML=xmlDoc_progress.getElementsByTagName("MPDConformance");
    if(MPDtotalResultXML.length==0)
        return;
    else
    {
        if (!mpdprocessed)
        {
            mpdprocessed = true; //only process it once!
            processmpdresults(MPDtotalResultXML);
        }
    }
}

function processmpdresults(MPDtotalResultXML)
{
    var x=2;
    var y=1;
    var MPDtotalResult=MPDtotalResultXML[0].childNodes[0].nodeValue; 

    totarr=MPDtotalResult.split(" ");

    var currentpath = window.location.pathname;
    currentpath = currentpath.substring(0, currentpath.lastIndexOf('/'));

    //Check if the MPD is dynamic.
    if(xmlDoc_progress.getElementsByTagName("dynamic").length !== 0)
    {
        if (xmlDoc_progress.getElementsByTagName("dynamic")[0].innerHTML === "true")
        {
    //        console.log("i'M DYNAMIC");
            if (xmlDoc_progress.getElementsByTagName("SegmentTimeline").length !== 0)
                dynamicsegtimeline = true;
    //            document.getElementById("list").href=currentpath+'/temp/'+dirid+'/featuretable.html';

            document.getElementById('dynamic').style.visibility='visible';

            document.getElementById("dynamic").href='http://vm1.dashif.org/DynamicServiceValidator/?mpdurl=' +url ;
    //            document.getElementById('list').style.visibility='visible';

    //            finishTest();
    //            return false;
        }
    }

    //check if SegmentList exist
    if(xmlDoc_progress.getElementsByTagName("segmentList").length !== 0)
    {
//        console.log("SegmentList exist!");
        segmentListExist = true;
    }

    document.getElementById("list").href=currentpath+'/temp/'+dirid+'/featuretable.html';
    document.getElementById('list').style.visibility='visible';

//        console.log("totarr=");
//        console.log(totarr);
    var failed ='false';

    repid =[];
    tree.loadJSONObject({
        id: 0,
        item: [{
            id: 1,
            text: "Mpd"
        }]
        });
    if(totarr[0]==='true')
    {
        automate(y,x,"XLink resolving");
        tree.setItemImage2( x,'right.jpg','right.jpg','right.jpg');
    }
    else {
        automate(y,x,"XLink resolving");
        tree.setItemImage2( x,'button_cancel.png','button_cancel.png','button_cancel.png');
        failed='temp/'+dirid+'/mpdreport.html';//totarr[0];
    }
    totarr.splice(0,1);
    x++;
    if(totarr[0]==='true')
    {
        automate(y,x,"MPD validation");
        tree.setItemImage2( x,'right.jpg','right.jpg','right.jpg');
    }
    else
    {
        automate(y,x,"MPD validation");
        tree.setItemImage2( x,'button_cancel.png','button_cancel.png','button_cancel.png');
        failed='temp/'+dirid+'/mpdreport.html';//totarr[0];
    }
    totarr.splice(0,1);
    x++;
    if(totarr[0]==='true')
    {
        automate(y,x,"Schematron validation");
        tree.setItemImage2( x,'right.jpg','right.jpg','right.jpg');
    }
    else {
        automate(y,x,"Schematron validation");
        tree.setItemImage2( x,'button_cancel.png','button_cancel.png','button_cancel.png');
        failed='temp/'+dirid+'/mpdreport.html';//totarr[0];
    }
    totarr.splice(0,1);
    x++;

    if (failed!=='false')
    {
        automate(y,x,"mpd error log");
        tree.setItemImage2(x,'log.jpg','log.jpg','log.jpg');
        kidsloc.push(x);
        urlarray.push(failed);
//        console.log(kidsloc);
//        console.log(urlarray[0]);
        lastloc++;
        clearInterval( pollingTimer);
        finishTest();
        return false;
    }

    if (dynamicsegtimeline || segmentListExist)
    {
        clearInterval( pollingTimer);
        finishTest();
        return;
    }

    var childno=1;
    //For dynamic type.
    if(totarrstring!=null && totarrstring=="true"){//TODO temporarily exit before processing adaptation sets
        clearInterval( pollingTimer);
        finishTest();
        return false;
    }
    //Get the number of AdaptationSets, Representations and Periods.   
    var  Treexml=xmlDoc_progress.getElementsByTagName("Representation");
    if (Treexml.length==0){
        var complete=xmlDoc_progress.getElementsByTagName("completed");
        if(complete[0].textContent == "true"){
            clearInterval( pollingTimer);
            finishTest();
        }              
        return;
    }else{
        var Periodxml=xmlDoc_progress.getElementsByTagName("Period"); 
        Adapt_count= Periodxml[0].childNodes.length;
        var AdaptRepPeriod_count=Adapt_count;
        var Adaptxml=xmlDoc_progress.getElementsByTagName("Adaptation");
        for (var v=0; v<Adapt_count; v++){
            AdaptRepPeriod_count=AdaptRepPeriod_count+" "+Adaptxml[v].childNodes.length;
        }
        AdaptRepPeriod_count=AdaptRepPeriod_count+" "+Periodxml.length;
    }
    
    totarr=AdaptRepPeriod_count.split(" ");
    for(var i=0;i<totarr[0];i++)
    {
        automate(y,x,"Adaptationset "+(i+1));
        adaptid.push(x);
        tree.setItemImage2( x,'adapt.jpg','adapt.jpg','adapt.jpg');

        for(var j=0;j<totarr[childno];j++)
        {
            automate(x,x+j+1,"Representation "+(j+1));
            repid.push(x+j+1);
        }

        childno++;
        x=x+j;
        x++;
    }
    numPeriods = totarr[totarr.length-1];
    if(numPeriods > 1)
    {
        console.log("MDP With Multiple Period:" + numPeriods);
    }

    lastloc = repid[repid.length-1]+1;
    clearInterval( pollingTimer);
    progressSegmentsTimer = setInterval(function(){progress()},400);
    document.getElementById('par').style.visibility='visible';
    document.getElementById('list').style.visibility='visible';
}

function progress()  //Progress of Segments' Conformance
{
    xmlDoc_progress=loadXMLDoc("temp/"+dirid+"/progress.xml");
//    console.log("progress():");
//    console.log(totarr);
    if(representationid >totarr[hinindex])
    {
        representationid = 1;
        hinindex++;
        adaptationid++;
    }

    //var status = "Processing Representation "+representationid+" in Adaptationset "+adaptationid;
    
    //document.getElementById("par").innerHTML=status;
    tree.setItemImage2( repid[counting],'progress3.gif','progress3.gif','progress3.gif');
    
    if(xmlDoc_progress == null)
        return;
//    console.log("progress(): representationid=",representationid,",hinindex=",hinindex,",adaptationid=",adaptationid  );
//    console.log("downloading, response:");
    var CrossRepValidation=xmlDoc_progress.getElementsByTagName("CrossRepresentation");
    var ComparedRepresentations = xmlDoc_progress.getElementsByTagName("ComparedRepresentations");
    
    if ((CrossRepValidation.length!=0 && adaptationid>totarr[0]) || (ComparedRepresentations.length !=0 && adaptationid>totarr[0]))
    {
//        console.log("Inside locations");
        if(CrossRepValidation.length!=0 && adaptationid>totarr[0]){
            for(var i =1; i<=CrossRepValidation.length;i++)
            {
                if(CrossRepValidation[i-1].textContent=="noerror"){

                    tree.setItemImage2(adaptid[i-1],'right.jpg','right.jpg','right.jpg');
                    automate(adaptid[i-1],lastloc,"Cross-representation validation success");

                    tree.setItemImage2(lastloc,'right.jpg','right.jpg','right.jpg');
                    lastloc++;
                // 			 tree.updateItem(adaptid[i-1],"Adaptationset " + i + " -cross validation success",'right.jpg','right.jpg','right.jpg',false);

                }
                else{

                    tree.setItemImage2(adaptid[i-1],'button_cancel.png','button_cancel.png','button_cancel.png');
//                  kidsloc.push(lastloc);
                    //urlarray.push(locations[i]);

                    automate(adaptid[i-1],lastloc,"Cross-representation validation error");

                    tree.setItemImage2(lastloc,'button_cancel.png','button_cancel.png','button_cancel.png');
                    lastloc++;

//                console.log("errors");

                    automate(adaptid[i-1],lastloc,"log");
                    tree.setItemImage2( lastloc,'log.jpg','log.jpg','log.jpg');
                    kidsloc.push(lastloc);
                    urlarray.push("temp/"+dirid+"/"+ "Adapt"+(i-1)+ "_infofile.html");
                    lastloc++;
                }
            }
        }
        
        if(cmaf == "yes")
        {
            if(ComparedRepresentations.length !=0 && adaptationid>totarr[0]){
                for(var i =1; i<=ComparedRepresentations.length;i++){
                
                    if(ComparedRepresentations[i-1].textContent=="noerror"){
                        tree.setItemImage2(adaptid[i-1],'right.jpg','right.jpg','right.jpg');
                        automate(adaptid[i-1],lastloc,"CMAF Compared representations validation success");

                        tree.setItemImage2(lastloc,'right.jpg','right.jpg','right.jpg');
                        lastloc++;
                    }
                    else{
                        tree.setItemImage2(adaptid[i-1],'button_cancel.png','button_cancel.png','button_cancel.png');
                        automate(adaptid[i-1],lastloc,"CMAF Compared representations validation error");

                        tree.setItemImage2(lastloc,'button_cancel.png','button_cancel.png','button_cancel.png');
                        lastloc++;
                    
                        automate(lastloc-1,lastloc,"log");//adaptid[i-1]
                        tree.setItemImage2( lastloc,'log.jpg','log.jpg','log.jpg');
                        kidsloc.push(lastloc);
                        urlarray.push("temp/"+dirid+"/"+ "Adapt"+(i-1)+ "_compInfo.html");
                        lastloc++;
                    }
                }
            }
            //Additions for CMAF Selection Set and Presentation profile.
            var SelectionSet=xmlDoc_progress.getElementsByTagName("SelectionSet");
            var CmafProfile=xmlDoc_progress.getElementsByTagName("CMAFProfile");
            if(SelectionSet.length!=0)
            {
                if(SelectionSet[0].textContent=="noerror"){
                        automate(1,lastloc,"CMAF Selection Set");

                        tree.setItemImage2(lastloc,'right.jpg','right.jpg','right.jpg');
                        lastloc++;
                }
                else{
                        automate(1,lastloc,"CMAF Selection Set");

                        tree.setItemImage2(lastloc,'button_cancel.png','button_cancel.png','button_cancel.png');
                        lastloc++;
                    
                        automate(lastloc-1,lastloc,"log");//adaptid[i-1]
                        tree.setItemImage2( lastloc,'log.jpg','log.jpg','log.jpg');
                        kidsloc.push(lastloc);
                        urlarray.push("temp/"+dirid+"/"+ "SelectionSet_infofile.html");
                        lastloc++;
                    }
            }
            if(CmafProfile.length!=0)
            {
                if(CmafProfile[0].textContent=="noerror"){
                        automate(1,lastloc,"CMAF Presentation Profile");

                        tree.setItemImage2(lastloc,'right.jpg','right.jpg','right.jpg');
                        lastloc++;
                }
                else{
                        automate(1,lastloc,"CMAF Presentation Profile");

                        tree.setItemImage2(lastloc,'button_cancel.png','button_cancel.png','button_cancel.png');
                        lastloc++;
                    
                        automate(lastloc-1,lastloc,"log");//adaptid[i-1]
                        tree.setItemImage2( lastloc,'log.jpg','log.jpg','log.jpg');
                        kidsloc.push(lastloc);
                        urlarray.push("temp/"+dirid+"/"+ "Presentation_infofile.html");
                        lastloc++;
                    }
            }
        }
        
        kidsloc.push(lastloc);
        var BrokenURL=xmlDoc_progress.getElementsByTagName("BrokenURL");
        if( BrokenURL != null && BrokenURL[0].textContent == "error")//if(locations[locations.length-1]!="noerror")
        {
            urlarray.push("temp/" + dirid+"/missinglink.html");//urlarray.push(locations[locations.length-1]);

            automate(1,lastloc,"Broken URL list");
            tree.setItemImage2(lastloc,'404.jpg','404.jpg','404.jpg');
            lastloc++; 
        }

//        console.log("go");
        clearTimeout(progressTimer);
        setStatusTextlabel("Conformance test completed");
        finishTest();
    }
    else
    {
//        console.log("Got output:");
//        console.log(lastloc);

        var AdaptXML=xmlDoc_progress.getElementsByTagName("Adaptation"); 
        if(AdaptXML[adaptationid-1]== null)
            return;
        else if(AdaptXML[adaptationid-1].childNodes[representationid-1] == null) {
            return;
        }
        else{   
            var RepXML=AdaptXML[adaptationid-1].childNodes[representationid-1].textContent;
            if(RepXML == "")
                return;
//            console.log("Adapt:"+(adaptationid)+" Rep:"+(representationid));
//            console.log(RepXML);
            representationid++;
        }


        if(RepXML == "noerror")
            tree.setItemImage2( repid[counting],'right.jpg','right.jpg','right.jpg');
        else
        {
            tree.setItemImage2( repid[counting],'button_cancel.png','button_cancel.png','button_cancel.png');

//            console.log("errors");

            automate(repid[counting],lastloc,"log");
            tree.setItemImage2( lastloc,'log.jpg','log.jpg','log.jpg');

            kidsloc.push(lastloc);
            urlarray.push("temp/"+dirid+"/"+ "Adapt"+(adaptationid-1)+"rep"+(representationid-2) + "log.html");

            lastloc++;  
        }

        counting++;

        progress();
    }
}
/////////////////////////Automation starts///////////////////////////////////////////////////
var urlarray=[];
//var x=2;
//var y=1;
function automate(y,x,stri)
{
    tree.insertNewChild(y,x,stri,0,0,0,0,'SELECT');
    fixImage(x.valueOf());
    x++;
    y++;
}
function brother(y,x)
{
    tree.insertNewNext(y,x,"New Node"+x,0,0,0,0,'SELECT');
    fixImage(x.valueOf());
    x++;
    y++;
}

function tondblclick(id)
{
    var urlto="";
    var position = kidsloc.indexOf(id);
    urlto=urlarray[position];
    //console.log(position);
    //console.log(urlto);
    if(urlto)
    window.open(urlto, "_blank");
}
//var parsed;
//var uploaded = "false";

function loadXMLDoc(dname)
{
    if (window.XMLHttpRequest)
    {
        xhttp=new XMLHttpRequest();
    }
    else
    {
        xhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xhttp.open("GET",dname,false);
    xhttp.send("");
    return xhttp.responseXML;
}

function finishTest()
{
    document.getElementById("btn8").disabled=false;
    document.getElementById("drop_div").disabled=false;

    clearInterval( progressTimer);
    clearInterval( progressSegmentsTimer);
    
    
    //Open a new window for checking Conformance of Chained-to MPD (if present).
    xmlDoc_progress=loadXMLDoc("temp/"+dirid+"/progress.xml");
    if (xmlDoc_progress !== null){
        var MPDChainingUrl=xmlDoc_progress.getElementsByTagName("MPDChainingURL");

        if(MPDChainingUrl.length !== 0){   
            ChainedToUrl=MPDChainingUrl[0].childNodes[0].nodeValue;
            window.open("conformancetest.php?mpdurl="+ChainedToUrl);
        }
    }
    setStatusTextlabel("Conformance test completed");
}

function initVariables()
{
    urlarray.length = 0;
    kidsloc.length = 0;
    current = 0;
    dirid="";
    lastloc = 0;
    counting =0;
    representationid =1;
    adaptationid = 1;
    hinindex = 1;
    numPeriods = 0;
    //uploaded = false;
    dynamicsegtimeline = false;
    segmentListExist = false;
}

function setUpTreeView()
{
    if (typeof tree === "undefined") 
    {
//        console.log("tree:doesnt exist");				
    }
    else
    {
//        console.log("tree: exist");
        tree.deleteChildItems(0);
        tree.destructor(); 
    }

    tree = new dhtmlXTreeObject('treeboxbox_tree', '100%', '100%', 0);
    tree.setOnDblClickHandler(tondblclick);
    tree.setSkin('dhx_skyblue');
    tree.setImagePath("img/");
    tree.enableDragAndDrop(true);
}

function setStatusTextlabel(textToSet)
{
    status = textToSet;

    if( numPeriods > 1 )
    {
        status = status + "<br><font color='red'> MPD with multiple Periods (" + numPeriods + "). Only segments of the first period were checked.</font>"
    }

    if( dynamicsegtimeline)
    {
        status = status + "<br><font color='red'> Segment timeline for type dynamic is not supported, only MPD will be tested. </font>"
    }
    
    if(segmentListExist)
    {
        status = status + "<br><font color='red'> SegmentList is not supported, only MPD will be tested. </font>"
    }
    
    if(ChainedToUrl)
    {
        status = status + "<br><font color='red'> Chained-to MPD conformance is opened in new window. </font>"
    }

    document.getElementById("par").innerHTML=status;
    document.getElementById('par').style.visibility='visible';
}

function UrlExists(url, cb){
    jQuery.ajax({
        url:      url,
        dataType: 'text',
        type:     'GET',
        complete:  function(xhr){
            if(typeof cb === 'function')
               cb.apply(this, [xhr.status]);
        }
    });
}
</script>

<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-48482208-1', 'dashif.org');
    ga('send', 'pageview');
</script>

<footer>
    <center> <p id="footerVersion"></p>
        <p><a target="_blank" href="https://github.com/DASHIndustryForum/Conformance-Software/issues">Report issue</a></p>
    </center>
    <center> <p>
        <a target="_blank" href="https://github.com/DASHIndustryForum/Conformance-Software/">GitHub</a></p>
    </center>
</footer>
</body>
</html>
