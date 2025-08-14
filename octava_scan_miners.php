<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$hst_path = 'hst_x86';
if (file_exists('/usr/local/bin/hst')) {
    $hst_path = '/usr/local/bin/hst';
} elseif (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'hst_x86')) {
    $hst_path = __DIR__ . DIRECTORY_SEPARATOR . 'hst_x86';
} elseif (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'hst')) {
    $hst_path = __DIR__ . DIRECTORY_SEPARATOR . 'hst';
} elseif (PHP_OS_FAMILY === 'Windows' && file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'hst.exe')) {
    $hst_path = __DIR__ . DIRECTORY_SEPARATOR . 'hst.exe';
}

// Ensure the path is clean
$hst_path = str_replace('\\', '/', $hst_path);

if (!file_exists($hst_path) && $hst_path !== 'hst' && $hst_path !== 'hst_x86') {
    echo json_encode(['error' => 'hst tool not found', 'path' => $hst_path]);
    exit;
}

if (isset($_GET['ip'])) {
    $ip = $_GET['ip'];
    $miner_port = 4028;
    $statusJson = get_miner_status($ip, $miner_port);
    if ($statusJson === false) {
        $cmd = escapeshellarg($hst_path) . " export";
        $res = shell_exec($cmd);
        if ($res) {
            echo $res;
        } else {
            echo json_encode(['error' => 'unreachable']);
        }
    } else {
        echo $statusJson;
    }
    exit;
}

if (isset($_GET['action'], $_GET['ip'])) {
    $action = $_GET['action'];
    $ip = $_GET['ip'];
    $cmd = escapeshellarg($hst_path) . " $action -i " . escapeshellarg($ip);
    $output = shell_exec($cmd);
    echo json_encode(['output' => $output]);
    exit;
}

$network = isset($_GET['network']) ? $_GET['network'] : '192.168.1.1-192.168.1.254';

$miner_port = 4028;
// Build command considering absolute paths
if ($hst_path[0] === '/' || $hst_path === 'hst' || $hst_path === 'hst_x86') {
    $cmd = $hst_path . " start " . escapeshellarg($network);
} else {
    $cmd = escapeshellarg($hst_path) . " start " . escapeshellarg($network);
}

$res = shell_exec($cmd . ' 2>&1');

error_log("HST command: $cmd");
error_log("HST result: $res");

if ($res === null || $res === false) {
    echo json_encode(['error' => 'Failed to execute hst command', 'command' => $cmd, 'details' => 'Command returned null']);
    exit;
}

$out = explode(PHP_EOL, $res);
$miners = [];

foreach ($out as $value) {
    if (empty(trim($value))) continue;
    
    $tmp = explode(' ', $value);
    foreach ($tmp as $tmp1) {
        if (strpos($tmp1, ':') !== false) {
            $ip = trim(explode(':', $tmp1)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $miners[$ip] = trim($ip);
            }
        }
    }
}

echo json_encode($miners);
exit;

function connect($ip, $port, $timeout=array('sec' => 1, 'usec' => 0)) {
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $timeout);
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout);

    $res = @socket_connect($socket, $ip, $port);
    if (@socket_getpeername($socket, $ip)) {
        return $socket;
    } else {
        return false;
    }
}

function read($socket) {
    $line = '';
    while (true) {
        $byte = socket_read($socket, 1);
        if ($byte === false || $byte === '') {
            break;
        }
        if ($byte === "\0") {
            break;
        }
        $line .= $byte;
    }
    return $line;
}

function get_miner_status($miner_ip, $miner_port) {
    if (!$socket = connect($miner_ip, $miner_port, array('sec' => 5, 'usec' => 0))) {
        //echo 'Нет подключения к ' . $miner_ip . '<br><br>';
        return false;
    }
    $cmd = array('command' => 'stats',);
    socket_write($socket, json_encode($cmd), strlen(json_encode($cmd)));
    $line = read($socket);
    socket_close($socket);
    if($line){return $line;}
    return false;
}