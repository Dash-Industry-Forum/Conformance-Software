<?php

//header('Access-Control-Allow-Origin: *');

$url = $_REQUEST['url'];

$file_headers = @get_headers($url);
if ($file_headers[0] == 'HTTP/1.0 404 Not Found' || $file_headers[0] == 'HTTP/1.1 404 Not Found' || $file_headers[0] == 'HTTP/2 404 Not Found') {
    $exists = false;
} else {
    $exists = true;
}

if($exists)
{
    $response_xml_data = file_get_contents($url);
    echo $response_xml_data;
} 
else {
    echo 'error';
}

?>

