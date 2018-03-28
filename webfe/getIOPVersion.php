<?php 
$file_contents= file_get_contents("change_log.txt");
$verison_pos=strpos($file_contents, "Version");
$eol=strpos($file_contents,PHP_EOL);
$version=substr($file_contents, $verison_pos+8, $eol-($verison_pos+8));
echo $version;
?>
