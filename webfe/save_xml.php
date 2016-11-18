
<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$filename = $_REQUEST['filename'];

//filter_input(INPUT_REQUEST, 'dirid');
$xml = $_POST['content'];
//filter_input(INPUT_POST, 'file_contents');
$file = fopen($filename, "wb");
fwrite($file, $xml);

fclose($file);
?> 
