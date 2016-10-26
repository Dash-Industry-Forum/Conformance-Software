<?php

/* 

 */

//$opener=$_REQUEST['handle'];
$length = $_REQUEST['length'];
$path = $_REQUEST['path'];
 // To get initial list of folders inside temp.
if (is_dir($path)) {
    $foldernames = scandir($path, 0);
    $i=0;
    $folders=array();
    foreach ($foldernames as $result) {
        if ($result === '.' or $result === '..') continue;

        if (is_dir($path . '/' . $result)) {
            $folders[$i]=$result;
            $i=$i+1;
        }
    }
    
} else {
    echo "No such directory";
}       

// To get the new list of folders which includes newly created results folder.       
$j=0;
while($j<=$i)
{
    if (is_dir($path)) 
    {
        $foldernamesNew = scandir($path, 0);
        $k=0;
        $foldersNew=array();
        foreach ($foldernamesNew as $result) {
            if ($result === '.' or $result === '..') continue;

            if (is_dir($path . '/' . $result)) {
                $foldersNew[$k]=$result;
                $k=$k+1;
            }
        }
    }
    $j=$k;
}

$Newfolder=$foldersNew[0];

$newPath='../webfe/TestResults';
$FoldName='Test';
for($n=1;$n<=$length;$n++)
{
    if(!is_dir($newPath.'/'.$FoldName.$n))
    {
        $FoldName=$FoldName.$n;
        break;
    }
            
}

echo $FoldName;
// Check progress.xml to find if Conformance Test is completed, then move the results to TestResults folder.
while(1)
{
    if(file_exists($path.'/'.$Newfolder.'/progress.xml')){
        //$feed = file_get_contents($path.'/'.$Newfolder.'/progress.xml');
        $filesize = filesize($path.'/'.$Newfolder.'/progress.xml');
        if($filesize != 0)
        {
            $xml=simplexml_load_file($path.'/'.$Newfolder.'/progress.xml');

            if($xml->completed=="true")
            { 
                rename($path.'/'.$Newfolder, $newPath.'/'.$FoldName );
                break;
            }
        }
    }
    sleep(3);
}
// Following is to remove temp/id-random-number folder name in myphp-error.log file
if ( file_exists( $newPath.'/'.$FoldName.'/myphp-error.log' ) )
{
    $fileContents=file_get_contents($newPath.'/'.$FoldName.'/myphp-error.log');
    $fileContents=str_replace('["temp\/'.$Newfolder, $FoldName, $fileContents); 
    $fileContents=str_replace('"temp\/'.$Newfolder, $FoldName, $fileContents); 
    $fileContents=str_replace('temp/'.$Newfolder, 'TestResults/'.$FoldName, $fileContents);
    $fileContents=str_replace('"]', '"', $fileContents);
    file_put_contents($newPath.'/'.$FoldName.'/myphp-error.log', $fileContents);

    // Remove date-time info
    $fileContents1=file_get_contents($newPath.'/'.$FoldName.'/myphp-error.log');

    while(strpos($fileContents1, '] ')!== FALSE){
        $startPos=  strpos($fileContents1, '[');
        $endPos=  strpos($fileContents1, '] ');
        $fileContents1= substr_replace($fileContents1, '', $startPos , $endPos-$startPos+2);  
    } 
    file_put_contents($newPath.'/'.$FoldName.'/myphp-error.log', $fileContents1);
}

$contents = file_get_contents($newPath.'/'.$FoldName.'/mpdreport.txt');

$pattern = '/Total time: (\d+) seconds/i';
$replacement = 'Total time: x seconds';
$contents = preg_replace($pattern, $replacement, $contents);

file_put_contents($newPath.'/'.$FoldName.'/mpdreport.txt', $contents);

unlink($newPath.'/'.$FoldName.'/progress.xml');
unlink($newPath.'/'.$FoldName.'/ValidateMP4.exe');

// Following is to remove temp/id-random-number folder name in respective files
replaceFolderName("stdout.txt",$newPath,$FoldName,$Newfolder);
replaceFolderName("config_file.txt",$newPath,$FoldName,$Newfolder);
replaceFolderName("command.txt",$newPath,$FoldName,$Newfolder);

// Compare and get the differences.
if (is_dir($newPath.'/'.'References'.'/'.$FoldName))
{
    $oldfile=$newPath.'/'.'References'.'/'.$FoldName;
    $newfile=$newPath.'/'.$FoldName;
    $command = 'diff'.' '.'-r '.$oldfile.' '.$newfile.' '.'>'.$newPath.'/'.$FoldName.'_diff.txt';
    //echo $command;
    $output=array();$status=0;
    exec($command,$output,$status);

}

function replaceFolderName($fileName,$newPath,$FoldName,$Newfolder)
{
    if ( file_exists( $newPath.'/'.$FoldName.'/'.$fileName) )
    {
        $fileContents=file_get_contents($newPath.'/'.$FoldName.'/'.$fileName);
        $fileContents=str_replace('temp/'.$Newfolder, $FoldName, $fileContents);  
       
        file_put_contents($newPath.'/'.$FoldName.'/'.$fileName, $fileContents);
    }
}
//echo "<p>pasted</p>";

?>
