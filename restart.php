<?php

$restart = filter_input(INPUT_GET, 'restart');
    if ($restart=="password"){
        $s = file_put_contents('/projects/sys/cache_bot/101_pass', 'Изменен пароль доступа к боту');
        sleep(1);
        exec("sudo service bottelegram.sh restart");
        
        }
    if ($restart=="token"){
        $s = file_put_contents('/projects/sys/cache_bot/101_tok', 'Изменен токен. Бот перезапущен');
        sleep(1);
        exec("sudo service bottelegram.sh restart");
        exit();
        }
?>