<?php
$error = false;
$token=false;
$server=false;

$config_file='/etc/octava/config.php';
if(!glob($config_file)){
    shell_exec('sudo mkdir -p /etc/octava');
    shell_exec('sudo chown -R www-data:www-data /etc/octava');
    shell_exec('touch '.$config_file);
    shell_exec('sudo ntpdate pool.ntp.org');
}
shell_exec('chomd +x ./hst');

// Initialize default values
$sn = 'DEMO-SN-001';
$dev = 'DEMO-DEVICE';

// Try to read real values if file exists
if (file_exists('/proc/sv')) {
    $sysinfo = explode("\n", file_get_contents('/proc/sv'));
    foreach ($sysinfo as $info) {
        $parts = explode(':', $info);
        if (count($parts) >= 2) {
            if ($parts[0] == 'SN') {
                $sn = trim($parts[1]);
            }
            if ($parts[0] == 'Device') {
                $dev = trim($parts[1]);
            }
        }
    }
}


$ip= get_local_ip('eth');

exec('sudo mv /var/www/vnc/vnc.php /var/www/vnc/vnc.php.bak');
exec('sudo cp vnc.php /var/www/vnc/');
if(!check_port('octava.online', 443)){
    echo 'Нет подключения к серверу';
    goto page;
}

if (isset($_POST['token'])) {
    exec('sudo rm -f /etc/openvpn/*.crt /etc/openvpn/*.key /etc/openvpn/*.conf');
    $token= explode('-', trim($_POST['token']));    
    if (count($token) != 2 || !is_numeric($token[0]) || !is_numeric($token[1]) || strlen($token[1]) != 32) {
        $error = 'Неверный формат токена';
    }else{        
        $uri='https://control.octava.online/api/?cmd=init&t='.$sn.'-'.$dev.'-'.$token[0].'-'.$token[1].'-'.$ip;
        // echo $uri.'<br>';
        //$res=file_get_contents($uri);
        $res= shell_exec('wget --no-check-certificate -qO - "'.$uri.'"');
        // var_dump($res);
        $html = json_decode($res,true);
        // var_dump($html);
        if(isset($html['status']) && $html['status'] == 'true'){
            $config='<?php'. PHP_EOL.'$token="'.$token[0].'-'.$token[1].'";'. PHP_EOL;
            $config.='$server="'.$html['server_ip'].'";'. PHP_EOL.'?>'. PHP_EOL;
            if(!file_put_contents($config_file, $config)){
                echo 'Невозможно сохранить настройки';
            }
            $tcode=time();
            $hash=$hash_str=md5(md5($sn).'+'.md5($dev).'+'.md5($token[0]).'+'.md5($tcode).'+octava+'.md5($token[1]));
            $geturi='https://control.octava.online/api/?cmd=getconfig&t='.$sn.'-'.$dev.'-'.$token[0].'-'.$hash.'-'.$tcode;
            // echo $geturi.'<br>';
            $ovpn_config='/tmp/'.$sn.'_'.$dev.'.tgz';
            //file_put_contents($ovpn_config, get_http($geturi));
            shell_exec('wget --no-check-certificate -qO '.$ovpn_config.' "'.$geturi.'"');
            if(glob($ovpn_config)){
                $tarresult=shell_exec('sudo tar zxf '.$ovpn_config.' -C /etc/openvpn');
                $ovpnresult=shell_exec('sudo service openvpn restart');
                // echo '<br>'.$tarresult;
                // echo '<br>'.$ovpnresult;
            }else{
                echo '<br>Файл не найден';
            }
        }
    }
}

if(glob($config_file)){
    include $config_file;    
    // echo $token.' '.$server;
}

page:
?>

<div class="panel text-center">
    <h1>Подключение к панели управления</h1>
    <span><a href="https://control.octava.online" target="_blank">control.octava.online</a></span>
    <div class="row">
        <div class="col">
            <?php
            echo 'Серийный номер: ' . $sn.' | Устройство: '.$dev.' | IP address: '.$ip;
            ?>
        </div>
    </div>
    <br>
    <?php
    if(!$token || strlen($token) < 32){
        ?>
    <div class="row">
        <div class="col">
            <?php
            if ($error) {
                ?>
                <span class="text-danger"><?php echo $error; ?></span>
                <?php
            }
            // echo $uri;
            ?>
            <form method="POST">
                <input type="text" name="token" class="form-control" placeholder="Введите цифровой токен"><br>
                <button type="submit" class="btn btn-sm btn-primary">Подключить</button>
            </form>
        </div>
    </div>
        <?php
    }else{
        $tmp=explode('-', $token);
        $tcode=time();
        $hash=$hash_str=md5(md5($sn).'+'.md5($dev).'+'.md5($tmp[0]).'+'.md5($tcode).'+octava+'.md5($tmp[1]));
        $geturi='https://control.octava.online/api/?cmd=getconfig&t='.$sn.'-'.$dev.'-'.$tmp[0].'-'.$hash.'-'.$tcode;
        // echo $geturi;
        //file_put_contents('/tmp/'.$sn.'.tgz', file_get_contents($geturi));
    }
    ?>
    
    
    
</div>

<?php 
echo 'eth: '. get_local_ip('eth').' | tun: '. get_local_ip('tun').'<br>';

// var_dump($sysinfo);
