<?php
$cmd = filter_input(INPUT_GET, 'cmd');

if ($cmd == "token"){
   $rngval = filter_input(INPUT_GET, 'rngval');
   exec("sudo service bottelegram.sh stop"); //останавливаем бота
   $fh = file_put_contents('/usr/local/bin/bottelegram/Token.txt', $rngval );
   echo $rngval;
   exit();

}
if ($cmd == "password"){
   $rngval = filter_input(INPUT_GET, 'rngval');
   $fh = file_put_contents('/usr/local/bin/bottelegram/Password.txt', $rngval );
   echo $rngval;
   exit();

}
if ($cmd == "number"){
   $rngval = filter_input(INPUT_GET, 'rngval');
   $fh = file_put_contents('/usr/local/bin/bottelegram/Number.txt', $rngval );
   echo $rngval;
   exit();

}
if ($cmd == "user"){
   $rngval = filter_input(INPUT_GET, 'rngval');
   $fh = file_put_contents('/usr/local/bin/bottelegram/UserDB.txt', $rngval );
   echo $rngval;
   exit();
}




?>