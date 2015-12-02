<?php

/* 
TestResults folder is cleaned except References folder.
References folder is cleaned separately depending on user requirement.
 */

$flag = $_REQUEST['flag'];

chdir("../webfe/TestResults/");
exec("sudo find * -maxdepth 0 -name 'References' -prune -o -exec rm -rf '{}' ';' ");
//echo "cleaned Test Results folder";

$path = "../webfe/TestResults/References";

chdir("../webfe/TestResults/References");
$command1="sudo find * -maxdepth 0 ";
$output=array();
exec($command1,$output);
$arrlength = count($output);

if ($arrlength === 0)
{ 
    $presentFlag=0;
    echo "Reference results not present, this Test Run will create them";
}
else
{ 
    $presentFlag=1;
    echo "References present";
}
    

if($flag)
{
    chdir("../webfe/TestResults/References");
    exec("sudo rm -r *");
    if($presentFlag)
        echo ", but Old References are removed and New References created";
    else
        echo ".";
}


?>