<?php
$cmd = filter_input(INPUT_GET, 'cmd');



if ($cmd == "db"){
	$db = file_get_contents("../../../usr/local/bin/bottelegram/UserDB.txt");
	echo $db;
	exit();
}
if($cmd == "user"){





	if (filesize('/usr/local/bin/bottelegram/UserDB.txt') == 0){

		echo 'Зарегистрированных пользователей: 0';

	}

	else{

		$UserDB = file_get_contents('/usr/local/bin/bottelegram/UserDB.txt');
		$userarr = explode(",", $UserDB);

		$user = count($userarr);
		echo 'Зарегистрированных пользователей: '.$user;


	}




	

	exit();
}



?>