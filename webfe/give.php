<?php
if(isset($_POST['val'])){
$filename=$_POST['val'];
//$string = file_get_contents ('./temp/'.$filename.'.txt',true);
$stringarr = file('./temp/'.$filename.'.txt',FILE_IGNORE_NEW_LINES);
$string=json_encode($stringarr);

echo $string;
}
?>