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

/** This function is responsible for Downloading segments it take the location to save the segments 
  and an Array containing the URLs for all segments within presentation, the segment is not completely downloaded but
  it check the boxes within the segment and ignore Mdat box and download only other boxes* */
function downloaddata($directory, $array_file)
{
    global $locate, $progressXML;
    $sizefile = fopen($locate . '/mdatoffset.txt', 'a+b'); //create text file containing the original size of Mdat box that is ignored (Important for execution of conformance software
    $initoffset = 0; // Set pointer to 0
    $totaldownloaded = 0; // bytes downloaded
    $totalDataProcessed = 0; //bytes processed within segments
    $totalDataDownloaded = 0;
    $downloadMdat=0;
    // Load XML with SimpleXml from string
    //$progressXML = simplexml_load_string('<root><percent>0</percent><dataProcessed>0</dataProcessed><dataDownloaded>0</dataDownloaded><completed>false</completed></root>'); //xml file containing progress to be fetched by client


    $ch = curl_init();


    for ($index = 0; $index < sizeof($array_file); $index++) //itterate on all segments
    {
        $filePath = $array_file[$index]; //get segment URL
        $file_size = remote_file_size2($filePath); // Get actual data size
        if ($file_size === false)// if URL return 404 report it as broken url
        {
            $missing = fopen($locate . '/missinglink.txt', 'a+b');

            fwrite($missing, $filePath . "\n");

            error_log("downloaddata_Missing:" . $filePath);
        }
        else
        {

            $file_sizearr[$index] = $file_size; //save the original size of segments
            $tok = explode('/', $filePath); //get all directories of URL
            $filename = $tok[sizeof($tok) - 1]; // find name of segment in the last directory of URL
            $sizepos = 0;

            while ($sizepos < $file_size) // iterate over the content of the segment
            {

                $content = partialdownload($filePath, $sizepos, $sizepos + 1500, $ch);  //Download 1500 byte 
                $totalDataDownloaded = $totalDataDownloaded + 1500; // update the total size of downloaded data
                $byte_array = unpack('C*', $content); // Unpacks from a binary string into an array
                $location = 1; // temporary pointer
                $name = null; // box name
                $size = 0; // box size
                $newfile = fopen($directory . $filename, 'a+b'); // create an empty mp4 file to contain data needed from remote segment



                while ($location < sizeof($byte_array)) //assure that the pointer doesn't exceed size of downloaded bytes
                {
                    $size = $byte_array[$location] * 16777216 + $byte_array[$location + 1] * 65536 + $byte_array[$location + 2] * 256 + $byte_array[$location + 3]; //calculate the size of current box
                    if (sizeof($array_file) === 1) // if presentation contain only single segment
                    {
                        $totaldownloaded = $totaldownloaded + $size;   // total data being processed 
                        $percent = (int) (100 * $totaldownloaded / $file_size); //get percent over the whole file size
                    }
                    else
                        $percent = (int) (100 * $index / (sizeof($array_file) - 1)); // percent of remaining segments

                    $name = substr($content, $location + 3, 4); //get box name exist in the next 4 bytes from the bytes containing the size

                    if ($name != 'mdat') // if it is not mdat box download it
                    {
                        //error_log( "Not_mdat:download");
                        $total = $location + $size; // The total size being downloaded is location + size
                        if ($total < sizeof($byte_array)) // if the amount of byte processed < the data downloaded at begining  
                        {
                            fwrite($newfile, substr($content, $location - 1, $size)); // copy the whole data to the new mp4 file
                        }
                        else
                        {
                            $rest = partialdownload($filePath, $sizepos, $sizepos + $size - 1, $ch); //otherwise download the rest of the box from the remote segment
                            $totalDataDownloaded = $totalDataDownloaded + $size - 1; //calculate the rest being downloaded
                            fwrite($newfile, $rest); // copy the rest to the file
                        }
                    }
                    else
                    {
                        //error_log( "mdat:dontdownload");
                        if($downloadMdat) //To stuff complete mdat data with zeros.
                        {
                            fwrite($sizefile, ($initoffset + $sizepos + 8) . " " . 0 . "\n");
                            fwrite($newfile, substr($content, $location - 1, 8));  //copy only the mdat name and size to the segment

                            fwrite($newfile,str_pad("0",$size-8,"0")); //Incase of the requirement of stuffing mdat with zeros
                        }
                        else
                        {
                            fwrite($sizefile, ($initoffset + $sizepos + 8) . " " . ($size - 8) . "\n"); // add the original size of the mdat to text file without the name and size bytes(8 bytes) 
                            fwrite($newfile, substr($content, $location - 1, 8));  //copy only the mdat name and size to the segment 
                            
                        }
                       
                        
                    }

                    $sizepos = $sizepos + $size; // move size pointer
                    $location = $location + $size; // move location pointer
                }

                // Modify node
                $progressXML->Progress->percent = strval($percent);
                $progressXML->Progress->dataProcessed = strval($totalDataProcessed + $sizepos);
                $progressXML->Progress->dataDownloaded = strval($totalDataDownloaded);

                // Saving the whole modified XML to a new filename
                $progressXML->asXml(trim($locate . '/progress.xml'));
                //error_log( "percent:".$percent );
            }

            fflush($newfile);
            fclose($newfile);
            $initoffset = $initoffset + $file_size; // initial offset after processing the whole file
            $totalDataProcessed = $totalDataProcessed + $file_size; // data processed 
            //error_log( "totalDataProcessed:");
            //error_log( $totalDataProcessed);
        }
    }

    curl_close($ch);

    fflush($sizefile);
    fclose($sizefile);

    fflush($missing);
    fclose($missing);

    if (!isset($file_sizearr))
        $file_sizearr = 0;
    return $file_sizearr;
}

// This function get the size of the segment remotely without downloading it
function remote_file_size2($url)
{
    # Get all header information
    $data = get_headers($url, true);

    if ($data[0] === 'HTTP/1.1 404 Not Found' || $data[0] === 'HTTP/1.0 404 Not Found' || $data[0] === 'HTTP/2 404 Not Found')
    {
        return false;
    }

    # Look up validity
    if (isset($data['Content-Length']))
    # Return file size
        return (int) $data['Content-Length'];
    else
        return false;
}

//This function download partial bytes of a file by giving file location + stat byte + end byte
function partialdownload($url, $begin, $end, &$ch)
{
    global $locate;
    $range = $begin . '-' . $end;
    $fileName = $locate . '//' . "getthefile.mp4"; // this file act as a temperoray container for partial segments downloaded


    $ret = curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 500); // 500 seconds
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);



    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');

    $ret = curl_setopt($ch, CURLOPT_RANGE, $range);


    $fp = fopen($fileName, "w+");
    if (!$fp)
    {
        error_log("fopen:.exit!");
        exit;
    }

    $ret = curl_setopt($ch, CURLOPT_FILE, $fp);


    $result = curl_exec($ch);

    if (curl_errno($ch))
    {
        error_log("curl_errno:" . curl_errno($ch));
    }

    $content = file_get_contents($fileName);
    if (!$content)
    {
        error_log("file_get_contents:failed" . $url . "/" . $begin . "/" . $end);
    }
    fclose($fp);

    return $content;
}

?>
