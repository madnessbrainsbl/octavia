<?php

exec("sudo service bottelegram.sh stop"); 
$token = file_put_contents('/usr/local/bin/bottelegram/Token.txt', '');
$db = file_put_contents('/usr/local/bin/bottelegram/UserDB.txt', '');
$pw = file_put_contents('/usr/local/bin/bottelegram/Password.txt', 'segnetics');
$num = file_put_contents('/usr/local/bin/bottelegram/Number.txt', '101');