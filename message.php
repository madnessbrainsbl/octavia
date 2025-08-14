<?php
 
$dir = opendir('/projects/sys/cache_bot');
$count = 0;
while($file = readdir($dir)){
    if($file == '.' || $file == '..' || is_dir('/projects/sys/cache_bot' . $file)){
        continue;
    }
    $count++;
}
echo 'Неотправленных сообщений: ' . $count;


    
