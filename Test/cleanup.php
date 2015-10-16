<?php

/* 
clean up temp folder
 */

$path = $_REQUEST['path'];
rmdir_recursive($path);
echo "cleaned temp folder";

function rmdir_recursive($dir) {
    foreach(scandir($dir) as $file) {
        if ('.' === $file || '..' === $file) continue;
        if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
        else unlink("$dir/$file");
    }
    rmdir($dir); 
}

?>