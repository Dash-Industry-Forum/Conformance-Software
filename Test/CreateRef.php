<?php

/* 
To place results into References folder.
 */

//$folderName = $_REQUEST['folder'];
chdir("/var/www/html/Conformance-Software/webfe/TestResults/");
$command1="sudo find * -maxdepth 0 -not -name 'References'";
$output=array();
exec($command1,$output);
//echo $output;

$arrlength = count($output);

for($x = 0; $x < $arrlength; $x++) {
    $command2="sudo mv ". $output[$x] . " References";
    exec($command2);
}


