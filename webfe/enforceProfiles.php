
<html>
    <meta charset="utf-8" />
    <head>  
    </head>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js"></script>
    
    <style>
    .profiles{
        top: 1px;
    }
    </style>
    <body>    
        <div class="profiles">
                    Enforce profile(s): <input type="checkbox" class="profile1" id="dvbprofile">
                                            <label for="dvbprofile">DVB</label>
                                       <input type="checkbox" class="profile2" id="hbbtvprofile">
                                            <label for="hbbtvprofile">HbbTV</label>
                                        <button id="btn8" onclick="submit()">Submit</button>
            </div>
    <script>
        var dvb=0;
        var hbbtv=0;
        function submit()
        {
             if($("#dvbprofile").is(':checked'))
                dvb = 1;
             if($("#hbbtvprofile").is(':checked'))
                hbbtv= 1;
            
            $.post(
                    "writeProfiles.php",
                    {hbbtv: hbbtv, dvb:dvb}
                   ).done(function(response){
                       
                           console.log(response); 
                           window.close();
                       });
        }
    </script>
    
     <?php
   
     ?>
    
</body>  
</html>
