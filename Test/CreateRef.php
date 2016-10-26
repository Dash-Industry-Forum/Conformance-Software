<?php

/* 
To place results into References folder.
 */

//$folderName = $_REQUEST['folder'];
chdir("../webfe/TestResults/");
$command1="find * -maxdepth 0 -not -name 'References'";
$output=array();
exec($command1,$output);

if (!file_exists("References"))
    mkdir("References", 777);

$arrlength = count($output);

for($x = 0; $x < $arrlength; $x++) {
    $command2="mv ". $output[$x] . " References";
    exec($command2);
}


