<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Функция для проверки доступности порта
function checkPort($ip, $port = 4028, $timeout = 1) {
    $connection = @fsockopen($ip, $port, $errno, $errstr, $timeout);
    if ($connection) {
        fclose($connection);
        return true;
    }
    return false;
}

// Тест подключения к майнеру через API
function testMinerAPI($ip, $port = 4028) {
    echo "Testing connection to $ip:$port...\n";
    
    if (!checkPort($ip, $port)) {
        echo "Port $port is not open on $ip\n";
        return false;
    }
    
    echo "Port is open! Trying to connect via socket...\n";
    
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (!$socket) {
        echo "Failed to create socket: " . socket_strerror(socket_last_error()) . "\n";
        return false;
    }
    
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 2, 'usec' => 0));
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 2, 'usec' => 0));
    
    if (!@socket_connect($socket, $ip, $port)) {
        echo "Failed to connect: " . socket_strerror(socket_last_error()) . "\n";
        socket_close($socket);
        return false;
    }
    
    echo "Connected! Sending stats command...\n";
    
    // Отправляем команду stats
    $cmd = json_encode(['command' => 'stats']);
    socket_write($socket, $cmd, strlen($cmd));
    
    $response = '';
    while ($out = @socket_read($socket, 4096)) {
        $response .= $out;
        if (strpos($out, "\0") !== false) break;
    }
    
    socket_close($socket);
    
    if ($response) {
        echo "Got response:\n";
        $data = json_decode(trim($response, "\0"), true);
        if ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
            return true;
        } else {
            echo "Raw response: " . substr($response, 0, 200) . "\n";
        }
    } else {
        echo "No response received\n";
    }
    
    return false;
}

// Тест toolkit_cli
echo "\n=== Testing toolkit_cli ===\n";
$toolkit_path = '/usr/local/bin/toolkit_cli';
if (file_exists($toolkit_path)) {
    echo "toolkit_cli found at: $toolkit_path\n";
    
    // Проверяем версию
    $version = shell_exec("$toolkit_path --version 2>&1");
    echo "Version info: $version\n";
    
    // Пробуем экспорт
    echo "\nTrying export with different parameters:\n";
    
    // Без фильтров
    echo "\n1. Export without filters:\n";
    $cmd = "$toolkit_path export -f json -i 192.168.0.1-192.168.0.10";
    echo "Command: $cmd\n";
    $result = shell_exec($cmd . ' 2>&1');
    echo "Result: " . substr($result, 0, 500) . "\n";
    
    // С платформой
    echo "\n2. Export with platform:\n";
    $platforms = ['xil', 'aml', 'bb', 'cv'];
    foreach ($platforms as $platform) {
        $cmd = "$toolkit_path export -f json -p $platform -i 192.168.0.1-192.168.0.10";
        echo "Command: $cmd\n";
        $result = shell_exec($cmd . ' 2>&1');
        echo "Result for $platform: " . substr($result, 0, 200) . "\n";
    }
} else {
    echo "toolkit_cli not found!\n";
}

// Проверяем сеть
echo "\n=== Network Configuration ===\n";
$ifconfig = shell_exec('ip addr 2>&1 || ifconfig 2>&1');
echo $ifconfig . "\n";

// Пробуем пинг
echo "\n=== Testing connectivity ===\n";
$test_ips = ['192.168.0.1', '192.168.0.243', '100.184.143.142'];
foreach ($test_ips as $ip) {
    echo "Ping $ip: ";
    $result = shell_exec("ping -c 1 -W 1 $ip 2>&1");
    if (strpos($result, '1 received') !== false || strpos($result, '1 packets received') !== false) {
        echo "OK\n";
    } else {
        echo "FAILED\n";
    }
}

// Сканируем диапазон на открытые порты майнеров
echo "\n=== Scanning for miners (port 4028) ===\n";
$range_start = ip2long('192.168.0.1');
$range_end = ip2long('192.168.0.20');

for ($ip_long = $range_start; $ip_long <= $range_end; $ip_long++) {
    $ip = long2ip($ip_long);
    if (checkPort($ip, 4028, 0.2)) {
        echo "Found open port 4028 on $ip!\n";
        testMinerAPI($ip);
    }
}

// Проверяем конкретный IP если он передан
if (isset($argv[1])) {
    $test_ip = $argv[1];
    echo "\n=== Testing specific IP: $test_ip ===\n";
    testMinerAPI($test_ip);
}

echo "\nDone!\n";
