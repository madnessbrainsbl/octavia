<?php
    $title = $_POST["password_bot"]; //You have to get the form data
    $file = fopen('/usr/local/bin/bottelegram/Password.txt', 'w+'); //Open your .txt file
    ftruncate($file, 0); //Clear the file to 0bit
    $content = $title;
    fwrite($file , $content); //Now lets write it in there
    fclose($file ); //Finally close our .txt
    die(header("Location: ".$_SERVER["HTTP_REFERER"]));
    $s = file_put_contents('/projects/sys/cache_bot/101_pass', 'Изменен пароль доступа к боту');
    exec("sudo service bottelegram.sh stop");
    sleep(2);
    exec("sudo service bottelegram.sh start");
?>