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

$counter_name = "counter.txt";
// Check if a text file exists. If not create one and initialize it to zero.
if (!file_exists($counter_name))
{
    $f = fopen($counter_name, "w");
    $timezone = date_default_timezone_get();
    $date = date('m/d/Y h:i:s a', time());
    fwrite($f, "The current server timezone is: " . $timezone . ", file created at: " . $date . "\n" . "0");
    fclose($f);
}
// Read the current value of our counter file
$f = fopen($counter_name, "r");
$content = fread($f, filesize($counter_name)); //the whole file including the header info
$contents = explode("\n", $content);
$info = $contents[0];
$counterVal = $contents[1];
fclose($f);

// Has visitor been counted in this session?
// If not, increase counter value by one
if (!isset($_SESSION['hasVisited']))
{
    $_SESSION['hasVisited'] = "yes";
    $counterVal++;
    $f = fopen($counter_name, "w");
    fwrite($f, $info . "\n" . $counterVal);
    fclose($f);
}
