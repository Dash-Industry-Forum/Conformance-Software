<?php

        
        $hbbtv= file_get_contents("hbbtv_OnOff.txt");
        

        
        $dvb= file_get_contents("dvb_OnOff.txt");
       $response=array($dvb,$hbbtv);
        
        echo json_encode($response);
?>