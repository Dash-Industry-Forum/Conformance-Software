<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
  <meta charset="utf-8" />
  <head>
  <title> DASH Conformance Test</title>
    <meta name="description" content="DASH Conformance">
    <meta name="keywords" content="DASH,DASH Conformance,DASH Validator">
    <meta name="author" content="Nomor Research GmbH">
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

;
?>
	  
 <script type="text/javascript">
	
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

}

p.sansserif {
    font-family: Arial, Helvetica, sans-serif;
}

#btn8 {
	-moz-box-shadow:inset 0px 1px 0px 0px #dcecfb;
	-webkit-box-shadow:inset 0px 1px 0px 0px #dcecfb;
	box-shadow:inset 0px 1px 0px 0px #dcecfb;
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #bddbfa), color-stop(1, #80b5ea) );
	background:-moz-linear-gradient( center top, #bddbfa 5%, #80b5ea 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#bddbfa', endColorstr='#80b5ea');
	background-color:#bddbfa;
	-webkit-border-top-left-radius:0px;
	-moz-border-radius-topleft:0px;
	border-top-left-radius:0px;
	-webkit-border-top-right-radius:0px;
	-moz-border-radius-topright:0px;
	border-top-right-radius:0px;
	-webkit-border-bottom-right-radius:0px;
	-moz-border-radius-bottomright:0px;
	border-bottom-right-radius:0px;
	-webkit-border-bottom-left-radius:0px;
	-moz-border-radius-bottomleft:0px;
	border-bottom-left-radius:0px;
	text-indent:-1px;
	border:1px solid #84bbf3;
	display:inline-block;
	color:#ffffff;
	font-family:Arial;
	font-size:15px;
	font-weight:bold;
	font-style:normal;
	height:40px;
	line-height:40px;
	width:100px;
	text-decoration:none;
	text-align:center;
	text-shadow:1px 2px 0px #183d61;
}
#btn8:hover:enabled {
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #80b5ea), color-stop(1, #bddbfa) );
	background:-moz-linear-gradient( center top, #80b5ea 5%, #bddbfa 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#80b5ea', endColorstr='#bddbfa');
	background-color:#80b5ea;
}
#btn8:active:enabled {
	position:relative;
	top:1px;
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
}

#dynamictable{
position:absolute;
 top:280px;
 right:40px;

}

</style>
<body>
<div id="dash">
        <br>
<img id="img2" border="0" src="dashlogo.jpeg" alt ="DASH Conformance" width="543" height="88" >
    <img id="img2" border="0" src="Dash1.jpeg" width="191" height="61" >

	<br>    <br>
</div>
    <p id="aaaa" align="center" class="sansserif">Validation (Conformance check) of ISO/IEC 23009-1 MPEG-DASH MPD and Segments</p>
<div id="groupA">

  <input type="text" id='urlinput' name="urlinput" class="mytext" placeholder="Enter MPD URL" onkeyup="CheckKey(event)"/>
  <!--input type="text" id='urlinput' name="urlinput" class="mytext" value="http://localhost/content/TestCases/1b/thomson-networks/2/manifest.mpd" onkeyup="CheckKey(event)"/-->
  <!--input type="text" id='urlinput' name="urlinput" class="mytext" value="http://dash.edgesuite.net/dash264/TestCases/1a/qualcomm/1/MultiRate.mpd" onkeyup="CheckKey(event)"/-->
  <!--input type="text" id='urlinput' name="urlinput" class="mytext" value="http://10.4.193.185/Content/TestCases/1b/qualcomm/1/MultiRate_Broken.mpd" onkeyup="CheckKey(event)"/-->

<button id="btn8" onclick="submit()">Submit</button>

<b>or</b>

<input type="file" name="afile" id="afile" />



<!--<input type="file" id="selectfile" /> Uploading local mpd for testing -->

<form action="">
<p class="sansserif"><input type="checkbox" id="mpdvalidation" class = "validation" value="0">MPD conformance only</p><br>
</form>
<a id="dynamic" href="url" target="_blank" style="visibility:hidden;" >Dynamic timing validation</a>

</div>


<div id="progressbar" style="width:100;background:#FFFFF;"></div>

<div id = "not">
        <br>    <br>   


</div>
<div id="to" >
<p align="center">
<p id="par" class="sansserif" style="visibility:hidden;">Loading....</p>

<a id="list" href="url" target="_blank" style="visibility:hidden;" >Feature list</a>
</div>



			

</p>
	<table>
		<tr>
			<td valign="top">


				<div id="treeboxbox_tree" style="width:500px; height:500px;background-color:#0000;border :none;; overflow:auto;"></div>
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
var counting =0;
var representationid =1;
var adaptationid = 1;
hinindex = 1;
var repid =[];	
var  totarr = [];
var adaptid=[];
 var file,fd,xhr;
 var uploaded = false;
var numPeriods = 0;
var SessionID = "id"+Math.floor(100000 + Math.random() * 900000);
/////////////////////////////////////////////////////////////
document.querySelector('#afile').addEventListener('change', function(e) {

  file = this.files[0];
   fd = new FormData();
  fd.append("afile", file);
  fd.append("sessionid", JSON.stringify(SessionID));
  xhr = new XMLHttpRequest();
  xhr.open('POST', 'process.php', true);
  
  
  xhr.onload = function() {
  uploaded=true;
  submit();

  };
 xhr.send(fd);
}, false);
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
        var lastRep = representationid-1;
        var progressText = "Processing Representation "+lastRep+" in Adaptationset "+adaptationid+", "+progressPercent+"% done ( "+dataDownloaded+" KB downloaded, "+dataProcessed+" MB processed )";
		
		if( numPeriods > 1 )
		{
			progressText = progressText + "<br><font color='red'> MPD with multiple Periods (" + numPeriods + "). Only segments of the first period will be checked.</font>"
		}
		
        document.getElementById("par").innerHTML=progressText;

             		
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

	  progressXMLRequest.open("GET", progressDocURL += (progressDocURL.match(/\?/) == null ? "?" : "&") + now.getTime(), false);  // initiate server request, trying to bypass cache using tip
	                                                                                                            // from 
	                                                                                                            // https://developer.mozilla.org/es/docs/XMLHttpRequest/Usar_XMLHttpRequest#Bypassing_the_cache,
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

    //var url = document.getElementById('urlinput').value;
    
    //var url = window.location.search;
    var parts = window.location.search.substr(1).split("&");
    var $_GET = {};
    var temp = parts[0].split("=");
    $_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
   

    //alert($_GET['urlinput']);
    var url= $_GET['urlinput'];
    document.getElementById("urlinput").value=url;
        
    if (uploaded===true)
	url="upload";
    
    var checkedValue = $('.validator:checked').val();
    var stringurl = [];
	
    stringurl[0] = url;

    stringurl[1] =  "mpdvalidator";
	
    if($("#mpdvalidation").is(':checked'))
        stringurl[2] = 1;
    else
   	stringurl[2] = 0 ;
    initVariables();
    setUpTreeView();
    setStatusTextlabel("Processing...");
    document.getElementById("btn8").disabled="true";
    document.getElementById("afile").disabled="true";
    document.getElementById('list').style.visibility='hidden';
    //document.getElementById('img').style.visibility='visible';
    //document.getElementById('par').style.visibility='visible';
    console.log(stringurl);
    $.post ("process.php",
    {urlcode:JSON.stringify(stringurl),sessionid:JSON.stringify(SessionID)},
    function(totarrstring)
    {
		console.log("process_returned:");
		
        
        if (totarrstring.indexOf("Error:") > -1)
        {
	
            window.alert("Error loading the MPD, please check the URL.");
			
            finishTest();            
            return false;
        }
        
        
        totarr=JSON.parse(totarrstring);
		console.log("totarr=");
		console.log(totarr);
		
        var currentpath = window.location.pathname;
        currentpath = currentpath.substring(0, currentpath.lastIndexOf('/'));

	   
        if(totarr[totarr.length-1]==='dynamic'){
            console.log("i'M DYNAMIC");

            dirid = totarr[totarr.length-2];
            document.getElementById("list").href=currentpath+'/temp/'+dirid+'/featuretable.html';

            document.getElementById('dynamic').style.visibility='visible';

            document.getElementById("dynamic").href='http://ec2-54-194-95-240.eu-west-1.compute.amazonaws.com/dynamic/?mpdurl=' +url ;
            document.getElementById('list').style.visibility='visible';

            finishTest();
            return false;
        }
        dirid = totarr[totarr.length-1];
	document.getElementById("list").href=currentpath+'/temp/'+dirid+'/featuretable.html';
	document.getElementById('list').style.visibility='visible';
        progressTimer = setInterval(function(){progressupdate()},1000);
		console.log("dirid=");
        console.log(dirid);
		console.log("totarrstring=");
        console.log(totarrstring);
		
        var failed ='false';

        var x=2;
        var childno=1;
        var y=1;
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
								 failed=totarr[0];
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
								 failed=totarr[0];
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
								 failed=totarr[0];
		}
		totarr.splice(0,1);
		 x++;
	
		if (failed!=='false')
		{
		automate(y,x,"mpd error log");
		tree.setItemImage2(x,'log.jpg','log.jpg','log.jpg');
		                kidsloc.push(x);
		urlarray.push(failed);
		console.log(kidsloc);
		console.log(urlarray[0]);
                lastloc++;
                
                finishTest();
                return false;
		}
		
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
        
		numPeriods = totarr[totarr.length-2];
		if(numPeriods > 1)
		{
			console.log("MDP With Multiple Period:" + numPeriods);
			
		}
		
        lastloc = repid[repid.length-1]+1;

        progress();
        document.getElementById('par').style.visibility='visible';
        document.getElementById('list').style.visibility='visible';
    });

}

function progress()
{

	console.log("progress():");
	console.log(totarr);
    if(representationid >totarr[hinindex])
    {
        representationid = 1;
        hinindex++;
        adaptationid++;
    }
    
    //var status = "Processing Representation "+representationid+" in Adaptationset "+adaptationid;
    representationid++;
    //document.getElementById("par").innerHTML=status;
    tree.setItemImage2( repid[counting],'progress3.gif','progress3.gif','progress3.gif');
    
    console.log("progress(): representationid=",representationid,",hinindex=",hinindex,",adaptationid=",adaptationid  );
    
    $.post("process.php",{download:"downloading",sessionid:JSON.stringify(SessionID)},
    function(response)
    {
		console.log("downloading, response:");
        console.log(response);
	var locations = JSON.parse(response);
        if (locations[0]=="done")
        {
            console.log("Inside locations");
		
            for(var i =1; i<locations.length-1;i++)
            {
                    if(locations[i]=="noerror"){

                        tree.setItemImage2(adaptid[i-1],'right.jpg','right.jpg','right.jpg');
                        automate(adaptid[i-1],lastloc,"Cross-representation validation success");

                        tree.setItemImage2(lastloc,'right.jpg','right.jpg','right.jpg');
                        lastloc++;
                    // 			 tree.updateItem(adaptid[i-1],"Adaptationset " + i + " -cross validation success",'right.jpg','right.jpg','right.jpg',false);

                    }
                    else{

                        tree.setItemImage2(adaptid[i-1],'button_cancel.png','button_cancel.png','button_cancel.png');
                        kidsloc.push(lastloc);
                        urlarray.push(locations[i]);

                        automate(adaptid[i-1],lastloc,"Cross-representation validation error");

                        tree.setItemImage2(lastloc,'button_cancel.png','button_cancel.png','button_cancel.png');
                        lastloc++;
                    }  


            }
            kidsloc.push(lastloc);
            if(locations[locations.length-1]!="noerror")
            {
                urlarray.push(locations[locations.length-1]);

                automate(1,lastloc,"Broken URL list");
                tree.setItemImage2(lastloc,'404.jpg','404.jpg','404.jpg');
                lastloc++;
            }
			 
            console.log("go");
            clearTimeout(progressTimer);
            setStatusTextlabel("Conformance test completed");
            
            finishTest();
        }
        else
        {
            console.log("Got output:");
            console.log(response);
            console.log(locations);

            //setTimeout(automate(repid[counting],lastloc,"infofile"),1);
            console.log(lastloc);

            if(locations[3]==="noerror")
                tree.setItemImage2( repid[counting],'right.jpg','right.jpg','right.jpg');
            else
            {
                tree.setItemImage2( repid[counting],'button_cancel.png','button_cancel.png','button_cancel.png');

                console.log("errors");

                automate(repid[counting],lastloc,"log");
                tree.setItemImage2( lastloc,'log.jpg','log.jpg','log.jpg');

                kidsloc.push(lastloc);
                urlarray.push(locations[1]);

                lastloc++;
            }

            //lastloc++;
            counting++;

            progress();
        }
    });
}
/////////////////////////Automation starts///////////////////////////////////////////////////
var urlarray=[];
var x=2;
	var y=1;
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
console.log(position);
console.log(urlto);
if(urlto)
window.open(urlto, "_blank");

}
var parsed;
var uploaded = "false";

function finishTest()
{
	document.getElementById("btn8").disabled=false;
	document.getElementById("afile").disabled=false;
	
	clearInterval( progressTimer);
        
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
	uploaded = false;
}

function setUpTreeView()
{
        if (typeof tree === "undefined") 
        {
            console.log("tree:doesnt exist");				
        }
        else
        {
            console.log("tree: exist");	
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
	
	document.getElementById("par").innerHTML=status;
	document.getElementById('par').style.visibility='visible';
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

<script>
                    submit();
                  
</script>
<footer>
 <center> <p>v0.96b
         <a target="_blank" href="https://github.com/DASHIndustryForum/Conformance-Software/issues">Report issue</a></p>
 </center>
</footer>
</body>
</html>
