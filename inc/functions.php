<?php

// Debug functions
function outarray($array) { // выводим содержимое массива
    echo '<pre>';
    var_dump($array);
    echo '</pre>';
}

function get_local_ip($interface) {
    $out = explode(PHP_EOL, shell_exec("/usr/sbin/ip a"));
    foreach ($out as $str) {
        $arr = explode(' ', $str);
        $ip = false;
        $iface = false;
        foreach ($arr as $key => $value) {
            if ($value == 'inet') {
                $ip = $arr[$key + 1];
            }
            if (substr($value, 0, 3) === $interface) {
                $iface = true;
            }
            if ($ip && $iface) {
                return explode('/', $ip)[0];
            }
        }
    }
    return false;
}

function check_port($host, $port) {
    $connection = @fsockopen($host, $port, $errno, $errstr, 5);
    if (is_resource($connection)) {
        fclose($connection);
        return true;
    } else {
        return false;
    }
}

function get_http($uri, $post = array()) {
    if (!empty($post)) {
        $cmd = '/usr/bin/wget --no-check-certificate -qO - ' . $uri;
    } else {
        $cmd = '/usr/bin/wget --no-check-certificate -qO - "' . $uri.'"';
    }
    return shell_exec($cmd);
}

function scan_miners($net = false, $config_file = '/etc/octava/miners.php') {
    if (!$net) {
        $local_ip = get_local_ip('eth');
        if (!$local_ip) {
            return false;
        }
    } else {
        $octets = explode('.', $net);
        if (count($octets) != 4) {
            return 'Неверная сеть';
        }
        if (!is_numeric($octets[0]) || !is_numeric($octets[1]) || !is_numeric($octets[2]) || !is_numeric($octets[3]) || $octets[0] < 0 || $octets[0] > 255 || $octets[1] < 0 || $octets[1] > 255 || $octets[2] < 0 || $octets[2] > 255 || $octets[3] < 0 || $octets[3] > 254) {
            return 'Неверная сеть';
        }
        $local_ip = $octets[0] . '.' . $octets[1] . '.' . $octets[2] . '.0';
    }
    $octets = explode('.', $local_ip);
    $miner_port = 4028;

    $miner_net = $octets[0] . '.' . $octets[1] . '.' . $octets[2] . '.1-' . $octets[0] . '.' . $octets[1] . '.' . $octets[2] . '.254';
    $miners = false;
    $res = shell_exec('./hst export -i ' . $miner_net);
    if(!$miners= json_decode($res, true)){
        return false;
    }
    $config='<?php'. PHP_EOL;
    foreach ($miners as $miner){
        $config.='$miners[]="'.$miner["ip"]["address"].'";'.PHP_EOL;
    }
    if(!file_put_contents($config_file, $config)){
        echo 'Невозможно сохранить настройки';
        return false;
    }
    return true;
}

function connect($ip, $port, $timeout = array('sec' => 5, 'usec' => 0)) {
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
    return $line;
}
