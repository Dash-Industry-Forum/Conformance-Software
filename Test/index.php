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
                $.post(
                    "cleanup.php",
                    {path:'../webfe/temp'},
                    function(response)
                    {
                        console.log(response);
                    }
                )
            }
        });
        
        i=1;
        var vectorstr = document.getElementById('vectors').value;
        if (vectorstr!='')
        {
            var vectors = vectorstr.split("\n");
            console.log(vectors);
            ajaxcall();
        }
        
        function ajaxcall()
        {
            console.log("i="+i+"; vector length is "+vectors.length);
            if(i<=vectors.length)
            {
                newtab(vectors[i-1]);  //content of this line
                $.post(
                    "second.php"
                ).done(function(){
                    alert("Successfully tested vector "+i); 
                    i++;
                    ajaxcall();
                });
            }
        }     
    }
</script>
</head>

<body> 
<br>

Test vectors :<br>
<textarea name="Text1" cols="100" rows="50" id='vectors'></textarea>
<br><input type=button id="Start" value="Start Testing" style="width:200px;" onclick="testing()">  
<h3 id="status"></h3>
</body>

</html>

