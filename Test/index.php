<!DOCTYPE html>
<?php 
;?>
<html>
<head>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>    
<script src="http://code.jquery.com/jquery-latest.js"></script>
<h2> Test Automation </h2>
<script>
   function newtab(mpdfile)
    {
     window.open("http://localhost/Conformance-Software/webfe/conformancetest.php?urlinput="+mpdfile);
    } 
    
    function testing()
    {
        //test if the temp folder is empty, if not, then ask if clean it. Don't process before cleaning!
        $.post(
            "checkempty.php",
            {path:'../webfe/temp'},
            function(response)
            {
                console.log(response);
            }
        ).done(function(response){
            if(response == "temp folder not empty")
            {
                while(!confirm("Temp folder not empty, clean now?")) //wait until confirmation
                {
                    alert("You cannot proceed before cleaning!");
                }
                // otherwise clean up
//                 {
                    $.post(
                        "cleanup.php",
                        {path:'../webfe/temp'},
                        function(response)
                        {
                            console.log(response);
                        }
                    )
//                 }
            }
        });
        
        //alert('Test is running ..Please Wait');
        i=1;
        ajaxcall();
        function ajaxcall()
        {
            if(i<=10)
            {
                var id= 'vector'+i;
                if (document.getElementById(id).value!='')
                {
                  newtab(document.getElementById(id).value);
                  $.ajax({                             //call the server
                    type: 'POST',
                    url:  "second.php",                     //At this url
                    data : {
                            
                           }
                    }).done(function(){
                       alert("Successfully tested vector"+i); 
                       i++;
                       ajaxcall();
                    });
                
                }
            }
                     
        }
     
    }
</script>
</head>

<body> 
<br>
Test vector 1 :<input type="text" id='vector1' name="vector1" style="width:800px; "> <br>
Test vector 2 :<input type="text" id='vector2' name="vector2" style="width:800px; "> <br>
Test vector 3 :<input type="text" id='vector3' name="vector3" style="width:800px; "> <br>
Test vector 4 :<input type="text" id='vector4' name="vector4" style="width:800px; "> <br>
Test vector 5 :<input type="text" id='vector5' name="vector5" style="width:800px; "> <br>
Test vector 6 :<input type="text" id='vector6' name="vector6" style="width:800px; "> <br>
Test vector 7 :<input type="text" id='vector7' name="vector7" style="width:800px; "> <br>
Test vector 8 :<input type="text" id='vector8' name="vector8" style="width:800px; "> <br>
Test vector 9 :<input type="text" id='vector9' name="vector9" style="width:800px; "> <br>
Test vector10:<input type="text" id='vector10' name="vector10" style="width:800px; "> <br>
<br><input type=button id="Start" value="Start Testing" style="width:200px;" onclick="testing()">  
<h3 id="status"></h3>
</body>

</html>

