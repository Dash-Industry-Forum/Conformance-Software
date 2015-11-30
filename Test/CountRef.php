<?php

/* 
Count the number of results folders available inside References folder.
 */

chdir("/var/www/html/Conformance-Software/webfe/TestResults/References");
$command1="sudo find * -maxdepth 0 ";
$output=array();
exec($command1,$output);
$arrlength = count($output);

echo $arrlength;