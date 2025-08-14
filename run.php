<?php

$run = filter_input(INPUT_GET, 'run');

    if ($run=="Пуск"){        
        exec("sudo service bottelegram.sh start");
        $s = file_put_contents('/projects/sys/cache_bot/101_start', 'Бот запущен из админ-панели');
        echo "1";
                }
    if ($run=="Стоп") {
        echo "0";
        $s = file_put_contents('/projects/sys/cache_bot/101_stop', 'Бот остановлен из админ-панели');
        sleep(6);        
        exec("sudo service bottelegram.sh stop");
        echo "2";             
        }
