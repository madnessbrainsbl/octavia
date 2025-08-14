<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$ip_range = $_GET['ip_range'] ?? '100.184.143.1-255';


function parseIpRange($range) {
    $ips = [];
    
    if (strpos($range, '-') !== false) {
        list($start_ip, $end_part) = explode('-', $range);
        $start_parts = explode('.', $start_ip);
        
        if (strpos($end_part, '.') !== false) {
            // Full IP range like 192.168.1.1-192.168.1.255
            $end_ip = $end_part;
        } else {
            // Short range like 192.168.1.1-255
            $start_parts[3] = $end_part;
            $end_ip = implode('.', $start_parts);
        }
        
        $start_long = ip2long($start_ip);
        $end_long = ip2long($end_ip);
        
        for ($i = $start_long; $i <= $end_long; $i++) {
            $ips[] = long2ip($i);
        }
    } else {
        $ips[] = $range;
    }
    
    return $ips;
}


function isValidIP($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP);
}

function parseAndValidateIpRange($range) {
    $ips = parseIpRange($range);
    return array_filter($ips, 'isValidIP');
}
function getMinerInfo($ip, $timeout = 1) {
    $port = 4028;
    $miner_data = [
        'ip' => $ip,
        'status' => 'Offline',
        'model' => 'Unknown',
        'mac' => 'Unknown',
        'hashrate' => '0',
        'temperature' => '0',
        'pool' => 'Unknown',
        'worker' => '',
        'platform' => 'Unknown',
        'firmware' => 'Unknown'
    ];
    
    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (!$socket) {
        return null;
    }
    
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $timeout, 'usec' => 0]);
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout, 'usec' => 0]);
    
    if (!@socket_connect($socket, $ip, $port)) {
        socket_close($socket);
        return null;
    }
    
    $miner_data['status'] = 'Online';
    $miner_data['ip'] = $ip;
    
    // Get stats
    $cmd = json_encode(['command' => 'stats']);
    @socket_write($socket, $cmd, strlen($cmd));
    
    $response = '';
    while ($out = @socket_read($socket, 4096)) {
        $response .= $out;
        if (strpos($out, "\0") !== false) break;
    }
    
    if ($response && ($data = json_decode(trim($response, "\0"), true))) {
        if (isset($data['STATS'])) {
            foreach ($data['STATS'] as $stat) {

                if (isset($stat['Type'])) {
                    $miner_data['model'] = $stat['Type'];
                }
                

                if (isset($stat['MAC'])) {
                    $miner_data['mac'] = $stat['MAC'];
                }
                

                if (isset($stat['GHS 5s'])) {
                    $ghs = $stat['GHS 5s'];
                    if ($ghs >= 1000) {
                        $miner_data['hashrate'] = round($ghs / 1000, 2) . ' TH/s';
                    } else {
                        $miner_data['hashrate'] = round($ghs, 2) . ' GH/s';
                    }
                } elseif (isset($stat['MHS 5s'])) {
                    $mhs = $stat['MHS 5s'];
                    if ($mhs >= 1000) {
                        $miner_data['hashrate'] = round($mhs / 1000, 2) . ' GH/s';
                    } else {
                        $miner_data['hashrate'] = round($mhs, 2) . ' MH/s';
                    }
                }
                

                $temps = [];
                for ($i = 1; $i <= 10; $i++) {
                    if (isset($stat["temp2_$i"])) {
                        $temps[] = $stat["temp2_$i"];
                    }
                }
                if (!empty($temps)) {
                    $miner_data['temperature'] = max($temps) . '°C';
                }
                

                if (isset($stat['Miner']) && strpos($stat['Miner'], 'BMMiner') !== false) {
                    $miner_data['platform'] = 'Bitmain';
                    $miner_data['firmware'] = 'Stock';
                }
            }
        }
    }
    

    $cmd = json_encode(['command' => 'pools']);
    @socket_write($socket, $cmd, strlen($cmd));
    
    $response = '';
    while ($out = @socket_read($socket, 4096)) {
        $response .= $out;
        if (strpos($out, "\0") !== false) break;
    }
    
    if ($response && ($data = json_decode(trim($response, "\0"), true))) {
        if (isset($data['POOLS'][0])) {
            $pool = $data['POOLS'][0];
            $miner_data['pool'] = $pool['URL'] ?? 'Unknown';
            $miner_data['worker'] = $pool['User'] ?? '';
        }
    }
    

    $cmd = json_encode(['command' => 'version']);
    @socket_write($socket, $cmd, strlen($cmd));
    
    $response = '';
    while ($out = @socket_read($socket, 4096)) {
        $response .= $out;
        if (strpos($out, "\0") !== false) break;
    }
    
    if ($response && ($data = json_decode(trim($response, "\0"), true))) {
        if (isset($data['VERSION'][0])) {
            $ver = $data['VERSION'][0];
            if (isset($ver['Type'])) {
                $miner_data['model'] = $ver['Type'];
            }
            if (isset($ver['BMMiner'])) {
                $miner_data['firmware'] = 'BMMiner ' . $ver['BMMiner'];
            }
        }
    }
    
    socket_close($socket);
    

    $miner_data['power'] = 'N/A';
    $miner_data['uptime'] = 'N/A';
    $miner_data['fans'] = 'N/A';
    $miner_data['coolingMode'] = 'N/A';
    $miner_data['devFee'] = 'N/A';
    
    return $miner_data;
}


$ips = parseAndValidateIpRange($ip_range);
$miners = [];


$max_ips = 255;
$ips = array_slice($ips, 0, $max_ips);

foreach ($ips as $ip) {
    $miner = getMinerInfo($ip, 0.5); // 500ms timeout
    if ($miner) {
        $miners[] = $miner;
    }
}

echo json_encode($miners);
?>
