<?php

/* This program is free software: you can redistribute it and/or modify
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

$counter_name = "counter.txt"; // Do not change this name, same name is used in other functions.

function visitor_counter()
{
    global $counter_name; //= "counter.txt"; // Do not change this name, same name is used in other functions.
    // Check if a text file exists. If not create one and initialize it to zero.
    if (!file_exists($counter_name))
    {
        $f = fopen($counter_name, "w");
        $timezone = date_default_timezone_get();
        $date = date('m/d/Y h:i:s a', time());
        fwrite($f, "The current server timezone is: " . $timezone . ", file created at: " . $date . "\n" ."No. of visitors"."\n". "0"."\n");
        fwrite($f, "----IP hash, ID, Start-time, %CPU, Memory(total, used, free, shared,buffers,cached), MPD-Status, MPD-End-time, End-time----\n");
        fclose($f);
    }
    // Read the current value of visitor counter from the file.
    $f = fopen($counter_name, "r");
    $content = fread($f, filesize($counter_name)); //the whole file including the header info
    $contents = explode("\n", $content);
    //$info = $contents[0];
    //$counterVal = $contents[1];
    $contents_new=$contents;
    $counterVal=$contents[2];
    fclose($f);

    // Has visitor been counted in this session?
    // If not, increase counter value by one
    //if (!isset($_SESSION['hasVisited']))
    //{
        //$_SESSION['hasVisited'] = "yes";
        $counterVal++;
        $contents_new[2]=$counterVal;
        $user_IP = getUserIPAddr(); // get the IP address of the visitor.
        $user_IP_hash=md5($user_IP); // convert IP to MD5 hash.
        $start_time = date('m/d/Y h:i:s a', time());
        $f = fopen($counter_name, "w");
        foreach($contents_new as $value){ // write file contents as it is with incremented counter value.
         fwrite($f, $value.PHP_EOL);
        }
        fwrite($f, $user_IP_hash .", ".$_POST['foldername'].", ".$start_time.", ");

        //This returns three samples representing the average system load (the number
        // of processes in the system run queue) over the last 1, 5 and 15 minutes, respectively.
        $cpu_avg_load = sys_getloadavg(); 
        fwrite($f, $cpu_avg_load[0].", ");

        $output_mem=null;
        exec('free',$output_mem);
         //$output_mem = (string)trim($output_mem);
        //$free_arr = explode("\n", $output_mem);
        $mem = explode(" ", $output_mem[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);
        //var_dump($output_mem);
        fwrite($f, $mem[1].",".$mem[2].",".$mem[3].",".$mem[4].",".$mem[5].",".$mem[6].", ");

        fclose($f);

    //}
}


function getUserIPAddr()
{
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}

function writeEndTime($end_time_sec)
{

    global $counter_name;// = "counter.txt";
    $end_time=date('m/d/Y h:i:s a', $end_time_sec);
   // $f = fopen(dirname(__FILE__) . '/'.$counter_name, "a+");
    $file = file_get_contents(dirname(__FILE__) . '/'.$counter_name);
    $lines = explode("\n", $file);
    $ID=$_POST['foldername'];
    foreach ($lines as $key => &$value) {
        $pos_ID=strpos($value,$ID);
        if($pos_ID!=FALSE){
            $value = $value.$end_time;
            break;
        }
    }
    file_put_contents(dirname(__FILE__) . '/'.$counter_name, implode("\n", $lines));
    //fwrite($f,$end_time. "\n"); 
    //fclose($f);
}

function writeMPDStatus($mpd)
{

    global $counter_name;// = "counter.txt";
    $f = fopen(dirname(__FILE__) . '/'.$counter_name, "a+");
    //Check if the mpd is an uploaded file.
    $uploaded=(strpos($mpd, "uploaded.mpd")!=FALSE && strpos($mpd, "var/www")!=FALSE);
    if ($uploaded==FALSE){
        $output= get_headers($mpd);
        $pos=strpos($output[0], "200 OK");

        
        if($pos!=FALSE)
            fwrite($f, "200 OK, ");
        else if(strpos($output[0], "404 Not Found"))
            fwrite($f, "404 Not Found- ".$mpd);
        else
            fwrite($f, $output[0].", ");

        
    }
    else
        fwrite($f, "uploaded, ");
    
    fclose($f);
}

function writeMPDEndTime()
{
    global $counter_name;
    $f = fopen(dirname(__FILE__) . '/'.$counter_name, "a+");
    $mpd_end_time = date('m/d/Y h:i:s a', time());
    fwrite($f, $mpd_end_time.", ");
    fclose($f);
}


?>