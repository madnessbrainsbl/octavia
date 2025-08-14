<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$toolkit_path = '/usr/local/bin/toolkit_cli';
$hst_path = '/usr/local/bin/hst';


if (!file_exists($toolkit_path)) {
    $toolkit_path = '/var/www/html/toolkit_cli';
}
if (!file_exists($hst_path)) {
    $hst_path = '/var/www/html/hst';
}

$demo_mode = false;

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ip_range = $_POST['ip_range'] ?? $_GET['ip_range'] ?? '';


function validateIpRange($range) {
    if (empty($range)) return false;
    

    if (filter_var($range, FILTER_VALIDATE_IP)) {
        return true;
    }
    
    // Check for IP range (e.g., 192.168.1.1-192.168.1.254)
    if (preg_match('/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})-(\d{1,3}(\.\d{1,3}\.\d{1,3}\.\d{1,3})?)$/', $range, $matches)) {
        $start_ip = $matches[1];
        if (isset($matches[3])) {
            $end_ip = $matches[2];
        } else {
            $parts = explode('.', $start_ip);
            $parts[3] = $matches[2];
            $end_ip = implode('.', $parts);
        }
        
        return filter_var($start_ip, FILTER_VALIDATE_IP) && filter_var($end_ip, FILTER_VALIDATE_IP);
    }
    
    // Check for comma-separated IPs
    $ips = explode(',', $range);
    foreach ($ips as $ip) {
        if (!filter_var(trim($ip), FILTER_VALIDATE_IP)) {
            return false;
        }
    }
    
    return true;
}

function getMinerDetails($ip) {
    $port = 4028;
    $default_data = [
        'ip' => $ip,
        'model' => 'Unknown',
        'mac' => 'Unknown',
        'platform' => 'Unknown',
        'firmware' => 'Unknown',
        'status' => 'Offline',
        'hashrate' => '0 TH/s',
        'temperature' => '0°C',
        'pool' => 'Unknown'
    ];
    

    global $hst_path;
    $cmd = "$hst_path export " . escapeshellarg($ip);
    $res = shell_exec($cmd . ' 2>&1');
    
    if ($res && ($json_data = json_decode($res, true))) {
        if (is_array($json_data) && isset($json_data[0])) {
            $miner = $json_data[0];
            

            $firmware_str = 'Unknown';
            if (isset($miner['firmware'])) {
                $fw_name = $miner['firmware']['name'] ?? $miner['type'] ?? 'Unknown';
                $fw_ver = $miner['firmware']['version'] ?? '';
                $firmware_str = trim($fw_name . ' ' . $fw_ver);
            }
            

            $temp = '0°C';
            if (isset($miner['chipTemp']['max'])) {
                $temp = $miner['chipTemp']['max'] . '°C';
            }
            

            $hashrate_str = '0 TH/s';
            if (isset($miner['hashrate'])) {
                $value = $miner['hashrate']['hashrate'] ?? 0;
                $unit = $miner['hashrate']['unit'] ?? 'TH/s';
                $hashrate_str = $value . ' ' . $unit;
            }
            

            $status = $miner['minerStatus'] ?? 'Unknown';
            if ($status == 'mining') {
                $status = 'Mining';
            } else if ($status == 'failure') {
                $status = 'Error';
            }
            
            return [
                'ip' => isset($miner['ip']['address']) ? $miner['ip']['address'] : $ip,
                'model' => $miner['model'] ?? 'Unknown',
                'mac' => $miner['mac'] ?? 'Unknown',
                'platform' => $miner['platform'] ?? 'Unknown',
                'firmware' => $firmware_str,
                'status' => $status,
                'hashrate' => $hashrate_str,
                'temperature' => $temp,
                'pool' => isset($miner['pools'][0]) ? $miner['pools'][0]['url'] : 'Unknown',
                'worker' => isset($miner['pools'][0]) ? $miner['pools'][0]['worker'] : '',
                'power' => isset($miner['power']) ? $miner['power'] . 'W' : 'N/A',
                'uptime' => isset($miner['uptime']) ? round($miner['uptime'] / 1000 / 60 / 60 / 24, 1) . ' days' : 'N/A',
                'fans' => isset($miner['cooling']['fans']) ? count(array_filter($miner['cooling']['fans'], function($f) { return $f['rpm'] > 0; })) . '/' . count($miner['cooling']['fans']) : 'N/A'
            ];
        }
    }
    

    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket) {
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 1, 'usec' => 0));
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
        
        if (@socket_connect($socket, $ip, $port)) {
            $default_data['status'] = 'Online';
            

            $cmd = json_encode(['command' => 'stats']);
            @socket_write($socket, $cmd, strlen($cmd));
            
            $response = '';
            while ($out = @socket_read($socket, 2048)) {
                $response .= $out;
                if (strpos($out, "\0") !== false) break;
            }
            
            if ($response && ($data = json_decode(trim($response, "\0"), true))) {

                if (isset($data['STATS'])) {
                    foreach ($data['STATS'] as $stat) {
                        if (isset($stat['Type'])) {
                            $default_data['model'] = $stat['Type'];
                        }
                        if (isset($stat['GHS 5s'])) {
                            $ghs = $stat['GHS 5s'];
                            if ($ghs >= 1000000) {
                                // Convert to PH/s
                                $hashrate = round($ghs / 1000000, 2);
                                $default_data['hashrate'] = $hashrate . ' PH/s';
                            } elseif ($ghs >= 1000) {
                                // Convert to TH/s
                                $hashrate = round($ghs / 1000, 2);
                                $default_data['hashrate'] = $hashrate . ' TH/s';
                            } else {
                                // Keep as GH/s
                                $default_data['hashrate'] = round($ghs, 2) . ' GH/s';
                            }
                        } elseif (isset($stat['MHS 5s'])) {
                            $mhs = $stat['MHS 5s'];
                            if ($mhs >= 1000000) {
                                // Convert to TH/s
                                $hashrate = round($mhs / 1000000, 2);
                                $default_data['hashrate'] = $hashrate . ' TH/s';
                            } elseif ($mhs >= 1000) {
                                // Convert to GH/s
                                $hashrate = round($mhs / 1000, 2);
                                $default_data['hashrate'] = $hashrate . ' GH/s';
                            } else {
                                // Keep as MH/s
                                $default_data['hashrate'] = round($mhs, 2) . ' MH/s';
                            }
                        }
                        if (isset($stat['temp2_6'])) {
                            $default_data['temperature'] = $stat['temp2_6'] . '°C';
                        }
                    }
                }
            }
            

            $cmd = json_encode(['command' => 'pools']);
            @socket_write($socket, $cmd, strlen($cmd));
            
            $response = '';
            while ($out = @socket_read($socket, 2048)) {
                $response .= $out;
                if (strpos($out, "\0") !== false) break;
            }
            
            if ($response && ($data = json_decode(trim($response, "\0"), true))) {
                if (isset($data['POOLS'][0]['URL'])) {
                    $default_data['pool'] = $data['POOLS'][0]['URL'];
                }
            }
        }
        socket_close($socket);
    }
    
    // For demo purposes, if it's one of our test IPs, return demo data
    if (in_array($ip, ['192.168.100.101', '192.168.100.102', '192.168.100.103'])) {
        $models = ['S19j Pro', 'S19 XP', 'S19'];
        $hashrates = ['104.5 TH/s', '141.2 TH/s', '95.0 TH/s'];
        $temps = ['65°C', '68°C', '63°C'];
        $index = array_search($ip, ['192.168.100.101', '192.168.100.102', '192.168.100.103']);
        
        return [
            'ip' => $ip,
            'model' => $models[$index],
            'mac' => '00:0A:35:' . substr(md5($ip), 0, 6),
            'platform' => 'Xilinx',
            'firmware' => 'Vnish 1.2.4',
            'status' => 'Online',
            'hashrate' => $hashrates[$index],
            'temperature' => $temps[$index],
            'pool' => 'stratum+tcp://pool.example.com:3333'
        ];
    }
    
    return $default_data;
}

switch ($action) {
    case 'scan':

        if (!validateIpRange($ip_range)) {
            echo json_encode(['error' => 'Invalid IP range format']);
            exit;
        }
        

        global $toolkit_path;
        

        $use_direct_scan = isset($_GET['direct_scan']) && $_GET['direct_scan'] == '1';
        

        if (filter_var($ip_range, FILTER_VALIDATE_IP)) {
            error_log("[SCAN] Direct IP scan for: $ip_range");
            $miner_data = getMinerDetails($ip_range);
            if ($miner_data['status'] !== 'Offline') {
                echo json_encode([$miner_data]);
                exit;
            }
        }
        
        if (file_exists($toolkit_path)) {

            error_log("[SCAN] Starting scan for IP range: $ip_range");
            

            $cmd = "$toolkit_path export --format json -i " . escapeshellarg($ip_range);
            error_log("[SCAN] Command: $cmd");
            $res = shell_exec($cmd . ' 2>&1');
            error_log("[SCAN] Raw result: " . substr($res, 0, 200));
            
 
            if (strpos($res, 'There is no miners') !== false || strpos($res, 'No miners found') !== false) {
                // Try different platforms without specifying model
                $platforms = ['xil', 'aml', 'bb', 'cv'];
                foreach ($platforms as $platform) {
                    $cmd = "$toolkit_path export --format json -p $platform -i " . escapeshellarg($ip_range);
                    error_log("[SCAN] Trying platform $platform: $cmd");
                    $res = shell_exec($cmd . ' 2>&1');
                    if ($res && strpos($res, '[') === 0) {
                        error_log("[SCAN] Found miners with platform $platform");
                        break;
                    }
                }
                

                if (!$res || strpos($res, '[') !== 0) {
                    $models = ['s19', 's19pro', 's19jpro', 's19xp', 't19', 's21'];
                    foreach ($models as $model) {
                        $cmd = "$toolkit_path export --format json -m $model -i " . escapeshellarg($ip_range);
                        error_log("[SCAN] Trying model $model: $cmd");
                        $res = shell_exec($cmd . ' 2>&1');
                        if ($res && strpos($res, '[') === 0) {
                            error_log("[SCAN] Found miners with model $model");
                            break;
                        }
                    }
                }
            }
            

            if ($res && strpos($res, 'There is no miners') !== false) {
                error_log("[SCAN] Toolkit_cli did not find miners, switching to direct scan");
                include 'direct_scan.php';
                exit;
            }
            
            if ($res && ($json_data = json_decode($res, true)) && is_array($json_data)) {
                $miners = [];
                foreach ($json_data as $miner) {

                $firmware_str = 'Unknown';
                if (isset($miner['firmware'])) {
                    $fw_name = $miner['firmware']['name'] ?? $miner['type'] ?? 'Unknown';
                    $fw_ver = $miner['firmware']['version'] ?? '';
                    $firmware_str = trim($fw_name . ' ' . $fw_ver);
                }
                

                $temp = '0°C';
                if (isset($miner['chipTemp']['max'])) {
                    $temp = $miner['chipTemp']['max'] . '°C';
                }
                
  
                $hashrate_str = '0 TH/s';
                if (isset($miner['hashrate'])) {
                    $value = $miner['hashrate']['hashrate'] ?? 0;
                    $unit = $miner['hashrate']['unit'] ?? 'TH/s';
                    $hashrate_str = $value . ' ' . $unit;
                }
                

                $status = $miner['minerStatus'] ?? 'Unknown';
                if ($status == 'mining') {
                    $status = 'Mining';
                } else if ($status == 'failure') {
                    $status = 'Error';
                }
                

                $fans_info = 'N/A';
                if (isset($miner['cooling']['fans'])) {
                    $working_fans = count(array_filter($miner['cooling']['fans'], function($f) { return $f['rpm'] > 0; }));
                    $total_fans = count($miner['cooling']['fans']);
                    $fans_info = $working_fans . '/' . $total_fans;
                }
                
                $miners[] = [
                    'ip' => isset($miner['ip']['address']) ? $miner['ip']['address'] : 'Unknown',
                    'model' => $miner['model'] ?? 'Unknown',
                    'mac' => $miner['mac'] ?? 'Unknown',
                    'platform' => $miner['platform'] ?? 'Unknown',
                    'firmware' => $firmware_str,
                    'status' => $status,
                    'hashrate' => $hashrate_str,
                    'temperature' => $temp,
                    'pool' => isset($miner['pools'][0]) ? $miner['pools'][0]['url'] : 'Unknown',
                    'worker' => isset($miner['pools'][0]) ? $miner['pools'][0]['worker'] : '',
                    'power' => isset($miner['power']) ? $miner['power'] . 'W' : 'N/A',
                    'uptime' => isset($miner['uptime']) ? round($miner['uptime'] / 1000 / 60 / 60 / 24, 1) . ' days' : 'N/A',
                    'fans' => $fans_info,
                    'coolingMode' => $miner['cooling']['coolingMode'] ?? 'N/A',
                    'devFee' => isset($miner['devFeePercent']) ? $miner['devFeePercent'] . '%' : 'N/A'
                ];
            }
                echo json_encode($miners);
                break;
            }
        }
        

        if ($use_direct_scan || !file_exists($toolkit_path)) {
            error_log("[SCAN] Using direct scan method");
            include 'direct_scan.php';
            exit;
        }
        

        echo json_encode([]);
        break;
        
    case 'reboot':
        $cmd = "$toolkit_path reboot " . escapeshellarg($ip_range);
        $output = shell_exec($cmd . ' 2>&1');
        echo json_encode(['status' => 'success', 'output' => $output]);
        break;
        
    case 'restart':
        $cmd = "$toolkit_path restart " . escapeshellarg($ip_range);
        $output = shell_exec($cmd . ' 2>&1');
        echo json_encode(['status' => 'success', 'output' => $output]);
        break;
        
    case 'pause':
        $cmd = "$toolkit_path pause " . escapeshellarg($ip_range);
        $output = shell_exec($cmd . ' 2>&1');
        echo json_encode(['status' => 'success', 'output' => $output]);
        break;
        
    case 'start':
        $cmd = "$toolkit_path start " . escapeshellarg($ip_range);
        $output = shell_exec($cmd . ' 2>&1');
        echo json_encode(['status' => 'success', 'output' => $output]);
        break;
        
    case 'switch_pool':
        $pool_id = $_POST['pool_id'] ?? $_GET['pool_id'] ?? 0;
        $cmd = "$toolkit_path switch-pool -p $pool_id " . escapeshellarg($ip_range);
        $output = shell_exec($cmd . ' 2>&1');
        echo json_encode(['status' => 'success', 'output' => $output]);
        break;
        
    case 'update_pools':
        $pools = $_POST['pools'] ?? '';
        $pool_file = tempnam(sys_get_temp_dir(), 'pools');
        file_put_contents($pool_file, $pools);
        $cmd = "$toolkit_path update-pools -f " . escapeshellarg($pool_file) . " " . escapeshellarg($ip_range);
        $output = shell_exec($cmd . ' 2>&1');
        unlink($pool_file);
        echo json_encode(['status' => 'success', 'output' => $output]);
        break;
        
    case 'network_config':
        $config = $_POST['config'] ?? '';
        $config_file = tempnam(sys_get_temp_dir(), 'netconf');
        file_put_contents($config_file, $config);
        $cmd = "$toolkit_path config-network -f " . escapeshellarg($config_file) . " " . escapeshellarg($ip_range);
        $output = shell_exec($cmd . ' 2>&1');
        unlink($config_file);
        echo json_encode(['status' => 'success', 'output' => $output]);
        break;
        
    case 'clone':
        $source_ip = $_POST['source_ip'] ?? '';
        $cmd = "$toolkit_path clone -s " . escapeshellarg($source_ip) . " " . escapeshellarg($ip_range);
        $output = shell_exec($cmd . ' 2>&1');
        echo json_encode(['status' => 'success', 'output' => $output]);
        break;
        
    case 'firmware_update':
        $firmware_path = $_POST['firmware_path'] ?? '';
        $version = $_POST['version'] ?? '';
        $platform = $_POST['platform'] ?? 'xil';
        $model = $_POST['model'] ?? 's19';
        $cmd = "$toolkit_path update -p $platform -m $model -d " . escapeshellarg($firmware_path) . " -v " . escapeshellarg($version) . " " . escapeshellarg($ip_range);
        $output = shell_exec($cmd . ' 2>&1');
        echo json_encode(['status' => 'success', 'output' => $output]);
        break;
        
    case 'remove_firmware':
        $cmd = "$toolkit_path remove-firmware " . escapeshellarg($ip_range);
        $output = shell_exec($cmd . ' 2>&1');
        echo json_encode(['status' => 'success', 'output' => $output]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}

?>
