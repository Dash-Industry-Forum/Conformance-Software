<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
  <meta charset="utf-8" />
  <head>
  <title> DASH ISO Segment Conformance Test</title>

  </head>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
  <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.js"></script>
  <script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
  <!--link rel="stylesheet" href="/resources/demos/style.css" /-->
 

          
            

         
	
	<link rel="STYLESHEET" type="text/css" href="tree/dhtmlxTree/codebase/dhtmlxtree.css">
	<script type="text/javascript"  src="tree/dhtmlxTree/codebase/dhtmlxtree.js"></script>
	<script type="text/javascript" src="tree/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
	  <script type="text/javascript" src="tree/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script> 

<?php 
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

</style>
<body>
<div id="dash">
        <br>
<img id="img2" border="0" src="dashtool.jpeg" alt="Pulpit rock" width="887" height="55" >
    <br>    <br>
</div>
<div id="groupA">

  <input type="text" id='urlinput' name="urlinput" class="mytext" placeholder="Enter MPD URL"/>
  <!--input type="text" id='urlinput' name="urlinput" class="mytext" value="http://localhost/content/TestCases/1b/thomson-networks/2/manifest.mpd"/-->
  <!--input type="text" id='urlinput' name="urlinput" class="mytext" value="http://dash.edgesuite.net/dash264/TestCases/1a/qualcomm/1/MultiRate.mpd"/-->
  <!--input type="text" id='urlinput' name="urlinput" class="mytext" value="http://10.4.127.99/dash264/TestCases/6c/Microsoft/CENC_SD_Time/CENC_SD_time_MPD.mpd"/-->

<button id="btn8" onclick="submit()">Submit</button>

</div>
<div id="progressbar" style="width:100;background:#FFFFF;"></div>

<div id = "not">
        <br>    <br>   
<p id = "note2"> <a target="_blank" href="http://www-itec.uni-klu.ac.at/dash/?page_id=605">Link</a> to MPD conformance tool  </p>


</div>
<div id="to" >
<img id="img" border="0" src="loading.gif" alt="Pulpit rock" width="150" height="150" style="visibility:hidden"/>
<p align="center">
<p id="par" style="visibility:hidden;">Loading....</p>

</div>



			

</p>
	<table>
		<tr>
			<td valign="top">


				<div id="treeboxbox_tree" style="width:250px; height:218px;background-color:#0000;border :none;; overflow:auto;"></div>
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

function button()
{
  
    current = current+1;
    $( "#progressbar" ).progressbar({
      value: current
    });
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
    var url = document.getElementById('urlinput').value;
    document.getElementById("btn8").disabled="true";

    //document.getElementById('img').style.visibility='visible';
    //document.getElementById('par').style.visibility='visible';

    $.post ("process.php",
    {urlcode:url},
    function(totarrstring)
    {
        console.log(totarrstring);
        
        if(totarrstring.indexOf("Error:") > -1)
        {
            window.alert("Error loading the MPD, please check the URL.");
			
            return false;
        }
		if(totarrstring==='dynamic'){
		            window.alert("Dynamic MPD conformance not supported");
					return false;
}

        totarr=JSON.parse(totarrstring);
        dirid = totarr[totarr.length-1];
        progressTimer = setInterval(function(){progressupdate()},1000);
        console.log(dirid);
        console.log(totarrstring);

        var x=2;
        var childno=1;
        var y=1;
        repid =[];
        tree.loadJSONObject({
            id: 0,
            item: [{
                id: 1,
                text: "Period"
            }]
            });
        tree.setOnDblClickHandler(tondblclick);
        
        for(var i=0;i<totarr[0];i++)
        { 
            var count=1;

            setTimeout(automate(y,x,"Adaptationset "+(i+1)),1);
			adaptid.push(x);
            tree.setItemImage2( x,'adapt.jpg','adapt.jpg','adapt.jpg');

            for(var j=0;j<totarr[childno];j++)
            {
                setTimeout(automate(x,x+j+1,"Representation "+(j+1)),1);
                repid.push(x+j+1);
            }
            
            childno++;
            x=x+j;
            x++;
        }
        
        lastloc = repid[repid.length-1]+1;

        setTimeout(progress,1);
        document.getElementById('par').style.visibility='visible';

    });

}

function progress()
{
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

    $.post("process.php",{download:"downloading"},
    function(response)
    {

console.log(response);
	var locations = JSON.parse(response);
        if (locations[0]=="done")
        {
		console.log("Inside locations");
		
		     for(var i =1; i<locations.length-1;i++)
			 
			 {
			 if(locations[i]=="noerror"){
			 tree.setItemImage2(adaptid[i-1],'right.jpg','right.jpg','right.jpg');
			 
			 }
			 else{
			 tree.setItemImage2(adaptid[i-1],'button_cancel.png','button_cancel.png','button_cancel.png');
			 							 kidsloc.push(lastloc);
                                      urlarray.push(locations[i]);
									  
			                 setTimeout(automate(adaptid[i-1],lastloc,"Cross-representation validation error"),1);
							 tree.setItemImage2(lastloc,'button_cancel.png','button_cancel.png','button_cancel.png');
                  lastloc++;
				  }
			 
			 
			 }
			 kidsloc.push(lastloc);
			         if(locations[locations.length-1]!="noerror")
					 {
                                      urlarray.push(locations[locations.length-1]);
									  
			                 setTimeout(automate(1,lastloc,"Broken URL list"),1);
							 tree.setItemImage2(lastloc,'404.jpg','404.jpg','404.jpg');
                  lastloc++;
			 }
			 
            console.log("go");
            clearTimeout(progressTimer);
            status = "Conformance test completed";
            document.getElementById("par").innerHTML=status;
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

                setTimeout(automate(repid[counting],lastloc,"log"),1);
                tree.setItemImage2( lastloc,'log.jpg','log.jpg','log.jpg');

                kidsloc.push(lastloc);
                urlarray.push(locations[1]);

                lastloc++;
            }

            //lastloc++;
            counting++;

            setTimeout(progress,1);
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
	var tree = new dhtmlXTreeObject('treeboxbox_tree', '100%', '100%', 0);
tree.setSkin('dhx_skyblue');
tree.setImagePath("img/");
tree.enableDragAndDrop(true);

function tondblclick(id)
{
var urlto="";
var position = kidsloc.indexOf(id);
urlto=urlarray[position];
if(urlto)
window.open(urlto, "_blank");

}
</script>

<footer>
 <center> <p>v0.8b
         <a target="_blank" href="https://github.com/DASHIndustryForum/Conformance-Software/issues">Report issue</a></p>
 </center>
</footer>
</body>
</html>
