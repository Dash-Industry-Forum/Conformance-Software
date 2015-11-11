<?php

/* 
check if temp folder is empty
 */
 
 $path = $_REQUEST['path'];
 if (count(glob("$path/*")) === 0 ) // empty, do nothing
 { 
    echo "temp folder empty";
 }
 else
 {
    echo "temp folder not empty";
 }

?>