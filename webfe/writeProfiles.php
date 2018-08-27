<?php
        $hbbtv = $_REQUEST['hbbtv'];
        $dvb = $_REQUEST['dvb'];
        
        $fhbbtv= fopen("hbbtv_OnOff.txt", "w");
        if($hbbtv==1)
            fwrite($fhbbtv, "1");
        else
            fwrite($fhbbtv, "0");
        fclose($fhbbtv);
        
        $fdvb= fopen("dvb_OnOff.txt", "w");
        if($dvb==1)
            fwrite($fdvb, "1");
        else
            fwrite($fdvb, "0");
        fclose($fdvb);
        
        echo "Done";
?>