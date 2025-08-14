<?php
    $title = $_POST["token_bot"]; //You have to get the form data
    $file = fopen('/usr/local/bin/bottelegram/Token.txt', 'w+'); //Open your .txt file
    ftruncate($file, 0); //Clear the file to 0bit
    $content = $title;
    fwrite($file , $content); //Now lets write it in there
    fclose($file ); //Finally close our .txt
    die(header("Location: ".$_SERVER["HTTP_REFERER"]));
    exec("sudo service bottelegram.sh stop");
    sleep(2);
    exec("sudo service bottelegram.sh start");
?>