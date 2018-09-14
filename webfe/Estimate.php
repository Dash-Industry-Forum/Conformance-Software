<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="utf-8" />
        <meta name="description" content="DASH Conformance">
        <meta name="keywords" content="DASH,DASH Conformance,DASH Validator">
        <meta name="author" content="Nomor Research GmbH">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css" />
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js"></script>
        <link rel="STYLESHEET" type="text/css" href="tree/dhtmlxTree/codebase/dhtmlxtree.css">
        <script type="text/javascript" src="tree/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
        <script type="text/javascript"  src="tree/dhtmlxTree/codebase/dhtmlxtree.js"></script>
        <script type="text/javascript" src="tree/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>
	<style>
            html,body
            {
                background-color: #fff; 
                background-image: 
                linear-gradient(90deg, transparent 79px, #abced4 79px, #abced4 81px, transparent 81px),
                linear-gradient(#eee .1em, transparent .1em);
                background-size: 100% 1.2em;
            }
            
            /* The container */
            .container 
            {     
                position: relative;
                padding-left: 35px;
                margin-bottom: 12px;
                cursor: pointer;
                font-size: 20px;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                font-family: Avantgarde, TeX Gyre Adventor, URW Gothic L, sans-serif; 
            }

            /* Hide the browser's default radio button */
            .container input 
            {
                position: absolute;
                opacity: 0;
                cursor: pointer;
            }

            /* Create a custom radio button */
            .checkmark 
            {
                position: absolute;
                top: 0;
                left: 0;
                height: 25px;
                width: 25px;
                background-color: #eee;
                border-radius: 50%;
            }

            /* On mouse-over, add a grey background color */
            .container:hover input ~ .checkmark 
            {
                background-color: #ccc;
            }

            /* When the radio button is checked, add a blue background */
            .container input:checked ~ .checkmark 
            {
                background-color: #2196F3;
            }

            /* Create the indicator (the dot/circle - hidden when not checked) */
            .checkmark:after 
            {
                content: "";
                position: absolute;
                display: none;
            }

            /* Show the indicator (dot/circle) when checked */
            .container input:checked ~ .checkmark:after 
            {
                display: block;
            }

            /* Style the indicator (dot/circle) */
            .container .checkmark:after 
            {
                    top: 9px;
                    left: 9px;
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    background: white;
            }

            /*for the button*/
            .button 
            {
              display: inline-block;
              padding: 10px 20px;
              font-size: 18px;
              cursor: pointer;
              text-align: center;
              text-decoration: none;
              outline: none;
              color: #fff;
              background-color: #4CAF50;
              border: none;
              border-radius: 15px;
              box-shadow: 0 9px #999;
            }

            .button:hover 
            {
                background-color: #3e8e41;
            }

            .button:active 
            {
              background-color: #3e8e41;
              box-shadow: 0 5px #666;
              transform: translateY(4px);
            }
            /*.button:disabled {
                    background-color: #3e7e41;
                    box-shadow: 0 5px #666;

                }*/

            /*for the forms*/
            .input
            {
                position:absolute;
                left: 200px;
            }

            input[type=text]:enabled 
            { 
                border-radius: 8px;
            }

            input[type=text]:disabled 
            {
                border-radius: 8px;
            }

            .content
            {
                border-radius: 50%;
                height: 500px;
                width:500px;
                position: absolute;
                left: 35%;
                top:25%;
                background: rgba(163,247,230,0.4);
                border-style: solid;
                border-color: #77f475;
            }
            
            .title
            {                           
                font-family: Avantgarde, TeX Gyre Adventor, URW Gothic L, sans-serif;
                font-size:24px;
                font-weight:bold;
                font-style:normal;
            }
            
            .center
            {
                position: relative;
                top:33%;
                left: 13%;
            }

        </style>
    </head>
    <body>
        
        <div class="content">
            <div class="center">
            <label class="title">Choose a parameter to estimate:</label>
            <br><br>
            <label class="container">
                <input type="radio"   id="MinBufferTime" checked="true" value="MinBufferTime" name="radio" required>MinBufferTime
                <span class="checkmark"></span>
            </label>
            <input type="text" class="input" id="field1" name="field1" placeholder="">
            <br><br>

            <label class="container">Bandwidth
                <input type="radio" name="radio" id ="Bandwidth" value ="Bandwidth"> 
                <span class="checkmark"></span>
            </label>
            <input class="input" type="text" id="field2" name="field2" placeholder="">
            <br><br>
            <button class="button">Estimate</button>
            </div>
        </div>
            
        <script>
$(document).ready(function () 
{
    function checkradiobox()
    {
        var radio = $("input[name='radio']:checked").val();
        $('#field1, #field2').attr('disabled',true);
        if(radio == "MinBufferTime")
        {
         $('#field1').attr('disabled',false);
         $("#field1").focus(function() {$( this ).css( "display", "inline" );});
        }
        else if(radio == "Bandwidth")
        {
            $('#field2').attr('disabled',false);
            $("#field2").focus(function() {$( this ).css( "display", "inline" );});
        }
    }
    $("#MinBufferTime, #Bandwidth").change(function () {checkradiobox();});
     checkradiobox();

});
        </script>
    </body>
</html>
