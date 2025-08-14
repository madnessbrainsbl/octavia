<?php
// Mock miner server для тестирования
$port = 4028;
$address = '127.0.0.1';

// Создаем socket сервер
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    die("socket_create() failed: " . socket_strerror(socket_last_error()));
}

socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

if (socket_bind($socket, $address, $port) === false) {
    die("socket_bind() failed: " . socket_strerror(socket_last_error($socket)));
}

if (socket_listen($socket, 5) === false) {
    die("socket_listen() failed: " . socket_strerror(socket_last_error($socket)));
}

echo "Mock miner server listening on $address:$port\n";

while (true) {
    $client = socket_accept($socket);
    if ($client === false) {
        continue;
    }

    $input = socket_read($client, 1024);
    echo "Received: " . trim($input) . "\n";

    // Mock response для stats команды
    $response = json_encode([
        'STATUS' => [
            [
                'STATUS' => 'S',
                'When' => time(),
                'Code' => 11,
                'Msg' => 'Summary',
                'Description' => 'cgminer 4.11.1'
            ]
        ],
        'SUMMARY' => [
            [
                'Elapsed' => 12345,
                'MHS av' => 110000.0,
                'MHS 5s' => 110000.0,
                'MHS 1m' => 110000.0,
                'MHS 5m' => 110000.0,
                'MHS 15m' => 110000.0,
                'Found Blocks' => 0,
                'Getworks' => 1000,
                'Accepted' => 950,
                'Rejected' => 50,
                'Hardware Errors' => 0,
                'Utility' => 1.5,
                'Discarded' => 0,
                'Stale' => 0,
                'Get failures' => 0,
                'Local Work' => 1000,
                'Remote Failures' => 0,
                'Network Blocks' => 100,
                'Total MH' => 1000000.0,
                'Work Utility' => 1.5,
                'Difficulty Accepted' => 950.0,
                'Difficulty Rejected' => 50.0,
                'Difficulty Stale' => 0.0,
                'Best Share' => 1000000,
                'Device Hardware%' => 0.0,
                'Device Rejected%' => 5.0,
                'Pool Rejected%' => 5.0,
                'Pool Stale%' => 0.0,
                'Last getwork' => time()
            ]
        ],
        'id' => 1
    ]);

    socket_write($client, $response . "\0");
    socket_close($client);
}

socket_close($socket);
?>
