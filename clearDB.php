<?php

$s = file_put_contents('/projects/sys/cache_bot/101_delDB', 'База данных пользователей очищена! Зарегистрируйтесь заново');
exec ('sudo cp -f /usr/local/bin/bottelegram/UserDB.txt /usr/local/bin/bottelegram/UserDB.txt_bak');
sleep(5);
$fh = file_put_contents('/usr/local/bin/bottelegram/UserDB.txt', '');
exec("sudo service bottelegram.sh restart");
echo $fh;

?>