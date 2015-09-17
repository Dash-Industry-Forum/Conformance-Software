<!DOCTYPE html>

<html>
<head>
</head>
<body>
<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//$opener=$_REQUEST['handle'];
 $path = '../webfe/temp'; 
 
if (is_dir($path)) {
    $foldernames = scandir($path, 0);
    $i=0;
     $folders=array();
   foreach ($foldernames as $result) {
        if ($result === '.' or $result === '..') continue;

        if (is_dir($path . '/' . $result)) {
            $folders[$i]=$result;
            echo "\r\n";
            $i=$i+1;

            }
        }
       
    } else {
        echo "No such directory";
        }
    
        
       
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


$flags=array_fill(0, $j, 0);
for($l=0;$l<$j;$l++)
{
    for($m=0;$m<$i;$m++)
    {
        if($foldersNew[$l]==$folders[$m])
        {
            $flags[$l]=1;
        }
    }
    if($flags[$l]==0)
        $Newfolder=$foldersNew[$l];
}
 echo "<p>New folder created is $Newfolder</p>";
 $newPath='../webfe/TestResults';
 $FoldName='Test';
 for($n=1;$n<=10;$n++)
 {
 
     if(!is_dir($newPath.'/'.$FoldName.$n))
     {
         $FoldName=$FoldName.$n;
         break;
     }
             
 }
 
 while(1)
 {
 $xml=simplexml_load_file($path.'/'.$Newfolder.'/progress.xml');

 if($xml->completed=="true")
  { 
     rename($path.'/'.$Newfolder, $newPath.'/'.$FoldName );
   
     break;
  }
  sleep(3);
 }
 //echo "<p>testing</p>";
 if (is_dir($newPath.'/'.'References'.'/'.$FoldName))
 {
     $oldfile=$newPath.'/'.'References'.'/'.$FoldName;
     $newfile=$newPath.'/'.$FoldName;
     $command = 'diff'.' '.$oldfile.' '.$newfile.' '.'>'.$newPath.'/'.$FoldName.'_diff.txt';
     echo $command;
     $output=array();$status=0;
     exec($command,$output,$status);

 }
  //echo "<p>pasted</p>";

?>
</body>
</html>