<?php
// Тестовый скрипт для прямого подключения к майнерам
echo "Testing direct miner connection...\n";

// Популярные порты для майнеров
$ports = [4028, 4029, 4030, 8080, 80, 443, 22, 23];
$test_ips = ['192.168.0.1', '192.168.0.2', '192.168.0.3', '192.168.0.4', '192.168.0.5', '192.168.0.146'];

foreach ($test_ips as $ip) {
    echo "Testing IP: $ip\n";
    
    foreach ($ports as $port) {
        $connection = @fsockopen($ip, $port, $errno, $errstr, 2);
        if ($connection) {
            echo "  Port $port: OPEN\n";
            
            // Попробуем отправить команду для майнера
            if ($port == 4028) {
                fwrite($connection, '{"command":"stats"}');
                $response = fread($connection, 1024);
                if ($response) {
                    echo "    Response: " . substr($response, 0, 100) . "...\n";
                }
            }
            
            fclose($connection);
        }
    }
    echo "\n";
}

echo "Testing completed.\n";
?>
