<?php

/* 
To find if any differences are present in diff txt file.
 */

$folderName = $_REQUEST['folder'];
$file= filesize('../webfe/TestResults/'.$folderName.'_diff.txt');
if($file == 0)
{
    echo "right";
}
else
{
    echo "wrong";
}