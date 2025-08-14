<?php
    $title = $_POST["name_bot"]; //You have to get the form data
    $file = fopen('/usr/local/bin/bottelegram/Name.txt', 'w+'); //Open your .txt file
    ftruncate($file, 0); //Clear the file to 0bit
    $content = $title;
    fwrite($file , $content); //Now lets write it in there
    fclose($file ); //Finally close our .txt
    die(header("Location: ".$_SERVER["HTTP_REFERER"]));
?>