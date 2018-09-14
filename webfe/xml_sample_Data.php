<?php

    $locate = $_REQUEST['location'];
    $sample_data_files = glob($locate."/*sample_data.xml");
    $sample_data_files = implode(";", $sample_data_files);
    echo $sample_data_files;


?>