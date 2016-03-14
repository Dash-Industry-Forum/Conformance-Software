<?php

/*This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function Assemble ($path,$period,$sizearr)  // Assemble all segments into a single file
{
    global $init_flag, $repno, $locate;
    if ($init_flag) // if the segments contain intialization segment 
        $index = 0; // segments start at index 0
    else
        $index = 1; // segments start at index 1

    foreach ($period as $unit)
        $names[] = basename($unit); // create an array containing the names of all segments



    for ($i = 0;$i<sizeof($names);$i++){
        $fp1 = fopen($locate . '/' . $repno . ".mp4", 'a+');  // Create container file to assemble all segments within it
        if (file_exists($path . $names[$i])) {  // if file exist 
            $size = $sizearr[$i]; // Get the real size of the file (passed as inupt for function)
            $file2 = file_get_contents($path . $names[$i]); // Get the file contents


            fwrite($fp1, $file2); // bend it in the container file
            fclose($fp1);
            file_put_contents($locate . '/' . $repno . ".txt", $index . " " . $size . "\n", FILE_APPEND); // add size to a text file to be passed to conformance software
            $index++; // iterate over all segments within the segments folder
        }
    }
}

/** This function remove all files and folders within the input folder (Used to delete old sessions to prevent accumulation of sessions on server**/
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir")
                    rrmdir($dir."/".$object);
                else {
                    chmod($dir."/".$object,0777);
                    unlink($dir."/".$object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }    
}

// Modification of standard PHP System() function to have system output from both the STDERR and STDOUT
function syscall($command){
$result=0;
    if ($proc = popen("($command)2>&1","r")){
        while (!feof($proc)) $result .= fgets($proc, 1000);
        pclose($proc);
        return $result; 
    }
}
	
// Clean URL or Path in case of many slashes where created due to the lack of depth in BaseURL
function removeabunchofslashes($url) {
    $explode = explode('://', $url);
    while (strpos($explode[1], '//'))
        $explode[1] = str_replace('//', '/', $explode[1]);
    return implode('://', $explode);
}

?>