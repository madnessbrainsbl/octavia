<?php

exec ('sudo cp -f /usr/local/bin/bottelegram/UserDB.txt_bak /usr/local/bin/bottelegram/UserDB.txt');
sleep(5);
exec("sudo service bottelegram.sh restart");
$s = file_put_contents('/projects/sys/cache_bot/101_returnDB', 'База данных пользователей восстановлена! Вы снова зарегистрированы!');
echo "База данных восстановлена";

?>