<?php
// Тестовый скрипт для проверки сканирования майнеров

echo "Testing miner scanning functionality...\n\n";

// IP адреса для сканирования
$test_ips = [
    '100.184.143.142',
    '100.184.143.142.5', // Возможно это опечатка, но проверим
];

// Также сканируем диапазон
$range_start = 1;
$range_end = 255;
$subnet = '100.184.143';

echo "Scanning specific IPs:\n";
foreach ($test_ips as $ip) {
    echo "Checking $ip... ";
    
    // Проверка валидности IP
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        echo "Invalid IP address\n";
        continue;
    }
    
    // Проверка подключения к порту 4028
    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket) {
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 1, 'usec' => 0]);
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]);
        
        if (@socket_connect($socket, $ip, 4028)) {
            echo "FOUND! Port 4028 is open\n";
            
            // Попробуем получить информацию
            $cmd = json_encode(['command' => 'stats']);
            @socket_write($socket, $cmd, strlen($cmd));
            
            $response = '';
            while ($out = @socket_read($socket, 4096)) {
                $response .= $out;
                if (strpos($out, "\0") !== false) break;
            }
            
            if ($response) {
                $data = json_decode(trim($response, "\0"), true);
                if ($data) {
                    echo "  Response received: " . json_encode($data) . "\n";
                } else {
                    echo "  Response received but couldn't parse JSON\n";
                }
            }
        } else {
            echo "No response on port 4028\n";
        }
        socket_close($socket);
    } else {
        echo "Failed to create socket\n";
    }
}

echo "\nScanning range $subnet.$range_start-$range_end:\n";
echo "This will take some time...\n";

$found_count = 0;
for ($i = $range_start; $i <= min($range_end, $range_start + 10); $i++) { // Сканируем только первые 10 для теста
    $ip = "$subnet.$i";
    
    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket) {
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 0, 'usec' => 500000]); // 500ms timeout
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 0, 'usec' => 500000]);
        
        if (@socket_connect($socket, $ip, 4028)) {
            echo "FOUND miner at $ip\n";
            $found_count++;
        }
        socket_close($socket);
    }
}

echo "\nScan summary:\n";
echo "Found $found_count miners in the tested range\n";

// Проверим, работает ли toolkit_cli
echo "\nChecking toolkit_cli availability:\n";
if (file_exists('/usr/local/bin/toolkit_cli')) {
    echo "toolkit_cli found at /usr/local/bin/toolkit_cli\n";
    
    // Попробуем выполнить команду
    $output = [];
    $return_var = 0;
    exec('/usr/local/bin/toolkit_cli --help 2>&1', $output, $return_var);
    
    if ($return_var === 0) {
        echo "toolkit_cli is working\n";
        echo "Output: " . implode("\n", $output) . "\n";
    } else {
        echo "toolkit_cli returned error code: $return_var\n";
        echo "Output: " . implode("\n", $output) . "\n";
    }
} else {
    echo "toolkit_cli NOT found\n";
}

// Проверим также наличие toolkit_cli в текущей директории
if (file_exists(__DIR__ . '/toolkit_cli')) {
    echo "\ntoolkit_cli found in current directory\n";
}

echo "\nTest completed!\n";
