<?php

//require_once('index.html');
//статус работы бота
$status = filter_input(INPUT_GET, 'status');
    if ($status == "work"){
        $stat = exec("ps aux | grep tg_bot.php | grep -v grep");
        echo $stat;
        exit();
        }

//статусы выбранных хранилищ
    if ($status == "redirectreports_int"){

        $json = file_get_contents('/usr/local/bin/bottelegram/settings.json');
        $json = json_decode($json, true);
            if ($json['RedirectReports']['int'] == 'On'){
                $int ='On';
                echo $int;
            }
            else {
                $int = 'Off';
                echo $int;
            }
        exit();
    }

    if ($status == "redirectreports_sd"){

        $json = file_get_contents('/usr/local/bin/bottelegram/settings.json');
        $json = json_decode($json, true);
            if ($json['RedirectReports']['sd'] == 'On'){
                $sd ='On';
                echo $sd;
            }
            else {
                $sd = 'Off';
                echo $sd;
            }
        exit();
    }

    if ($status == "redirectreports_usb"){

        $json = file_get_contents('/usr/local/bin/bottelegram/settings.json');
        $json = json_decode($json, true);
            if ($json['RedirectReports']['usb'] == 'On'){
                $usb ='On';
                echo $usb;
            }
            else {
                $usb = 'Off';
                echo $usb;
            }
        exit();
    }






