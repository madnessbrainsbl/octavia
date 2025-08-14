<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


function parseIpRange($range) {
    $ips = [];
    
    if (strpos($range, '-') !== false) {
        list($start_ip, $end_part) = explode('-', $range);
        $start_parts = explode('.', $start_ip);
        
        if (strpos($end_part, '.') !== false) {

            $end_ip = $end_part;
        } else {

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


function checkPortQuick($ip, $port = 4028, $timeout = 1) {
    $fp = @fsockopen($ip, $port, $errno, $errstr, $timeout);
    if ($fp) {
        fclose($fp);
        return true;
    }
    return false;
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
        'firmware' => 'Unknown',
        'power' => 'N/A',
        'uptime' => 'N/A',
        'fans' => 'N/A',
        'coolingMode' => 'N/A',
        'devFee' => 'N/A'
    ];
    

    if (!checkPortQuick($ip, $port, 0.2)) {
        return null;
    }
    
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
    

    $cmd = json_encode(['command' => 'stats']);
    @socket_write($socket, $cmd, strlen($cmd));
    
    $response = '';
    $max_read = 10; 
    $read_count = 0;
    while ($read_count < $max_read && ($out = @socket_read($socket, 4096))) {
        $response .= $out;
        if (strpos($out, "\0") !== false) break;
        $read_count++;
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
                    if ($mhs >= 1000000) {
                        $miner_data['hashrate'] = round($mhs / 1000000, 2) . ' TH/s';
                    } elseif ($mhs >= 1000) {
                        $miner_data['hashrate'] = round($mhs / 1000, 2) . ' GH/s';
                    } else {
                        $miner_data['hashrate'] = round($mhs, 2) . ' MH/s';
                    }
                }
                

                $temps = [];
                for ($i = 1; $i <= 10; $i++) {
                    if (isset($stat["temp2_$i"]) && $stat["temp2_$i"] > 0) {
                        $temps[] = $stat["temp2_$i"];
                    }
                    if (isset($stat["temp$i"]) && $stat["temp$i"] > 0) {
                        $temps[] = $stat["temp$i"];
                    }
                }
                if (!empty($temps)) {
                    $miner_data['temperature'] = max($temps) . '°C';
                }
                

                if (isset($stat['Miner'])) {
                    if (strpos($stat['Miner'], 'BMMiner') !== false) {
                        $miner_data['platform'] = 'Bitmain';
                        $miner_data['firmware'] = 'Stock';
                    } elseif (strpos($stat['Miner'], 'cgminer') !== false) {
                        $miner_data['platform'] = 'CGMiner';
                    }
                }
                

                if (isset($stat['Elapsed'])) {
                    $days = round($stat['Elapsed'] / 86400, 1);
                    $miner_data['uptime'] = $days . ' days';
                }
            }
        }
    }
    

    $cmd = json_encode(['command' => 'pools']);
    @socket_write($socket, $cmd, strlen($cmd));
    
    $response = '';
    $read_count = 0;
    while ($read_count < $max_read && ($out = @socket_read($socket, 4096))) {
        $response .= $out;
        if (strpos($out, "\0") !== false) break;
        $read_count++;
    }
    
    if ($response && ($data = json_decode(trim($response, "\0"), true))) {
        if (isset($data['POOLS'][0])) {
            $pool = $data['POOLS'][0];
            $miner_data['pool'] = $pool['URL'] ?? 'Unknown';
            $miner_data['worker'] = $pool['User'] ?? '';
            

            if (isset($pool['Status']) && $pool['Status'] == 'Alive') {
                $miner_data['status'] = 'Mining';
            }
        }
    }
    

    $cmd = json_encode(['command' => 'version']);
    @socket_write($socket, $cmd, strlen($cmd));
    
    $response = '';
    $read_count = 0;
    while ($read_count < $max_read && ($out = @socket_read($socket, 4096))) {
        $response .= $out;
        if (strpos($out, "\0") !== false) break;
        $read_count++;
    }
    
    if ($response && ($data = json_decode(trim($response, "\0"), true))) {
        if (isset($data['VERSION'][0])) {
            $ver = $data['VERSION'][0];
            if (isset($ver['Type'])) {
                $miner_data['model'] = $ver['Type'];
            }
            if (isset($ver['BMMiner'])) {
                $miner_data['firmware'] = 'BMMiner ' . $ver['BMMiner'];
            } elseif (isset($ver['CGMiner'])) {
                $miner_data['firmware'] = 'CGMiner ' . $ver['CGMiner'];
            }
        }
    }
    
    socket_close($socket);
    
    return $miner_data;
}


$ip_range = $_GET['ip_range'] ?? '192.168.0.1-192.168.0.254';
$max_threads = $_GET['max_threads'] ?? 50;


$ips = parseIpRange($ip_range);
$miners = [];


$max_ips = 255;
$ips = array_slice($ips, 0, $max_ips);


$batch_size = 10;
$batches = array_chunk($ips, $batch_size);

foreach ($batches as $batch) {
    $batch_miners = [];
    

    foreach ($batch as $ip) {
        if (checkPortQuick($ip, 4028, 1)) {
            $miner = getMinerInfo($ip, 1);
            if ($miner && $miner['status'] !== 'Offline') {
                $batch_miners[] = $miner;
            }
        }
    }
    
    $miners = array_merge($miners, $batch_miners);
    

    if (count($miners) >= 20) {
        break;
    }
}



echo json_encode($miners);
