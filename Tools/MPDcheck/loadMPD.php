<?php

header('Access-Control-Allow-Origin: *');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$url = $_REQUEST['url'];

$file_headers = @get_headers($url);
if ($file_headers[0] == 'HTTP/1.1 404 Not Found') {
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
// if ($response_xml_data) {
//     $data = simplexml_load_string($response_xml_data);
// }
?>

