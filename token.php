<?php
// Проверяем наличие файла токена
$token_file = "/usr/local/bin/bottelegram/Token.txt";

if (file_exists($token_file)) {
    $token = htmlentities(file_get_contents($token_file));
    echo $token;
} else {
    echo "Токен не найден";
}
?>

