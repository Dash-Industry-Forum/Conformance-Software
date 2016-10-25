<?php

/* 
TestResults folder is cleaned except References folder.
References folder is cleaned separately depending on user requirement.
 */

$flag = $_REQUEST['flag'];

chdir("../webfe/TestResults/");
// clean all the folders and files that are not inside the "Reference" folder.
exec("find * -maxdepth 0 -name 'References' -prune -o -exec rm -rf '{}' ';' ");
//echo "cleaned Test Results folder";

if (file_exists("References") && is_dir("References"))
{
    chdir("References");
    $command1="find * -maxdepth 0 ";
    $output=array();
    exec($command1,$output);
    $arrlength = count($output);
}else
{
    mkdir("References", 777);
    chdir("References");
    $arrlength = 0;
}

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
    if($presentFlag)
    {
        exec("rm -r *");
        echo ", but Old References are removed and New References created";
    }
    else
        echo ".";
}

?>