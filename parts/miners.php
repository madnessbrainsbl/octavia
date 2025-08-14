<?php
set_time_limit(0);
shell_exec('sudo chmod +x '.__DIR__.'/../hst');
$config_file = '/etc/octava/miners.php';
if (!glob($config_file)) {
    shell_exec('sudo mkdir -p /etc/octava');
    shell_exec('sudo chown -R www-data:www-data /etc/octava');
    shell_exec('touch '.$config_file);
}
$miners = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['scan'])) {
        $scan = scan_miners($_POST['network']);
    }
}

?>


<div class="panel">
    <h1 class="text-center">Майнеры в сети</h1>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Управление сетью майнеров</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>IP диапазон для сканирования:</label>
                                <input type="text" id="scanRange" class="form-control" value="192.168.0.1-192.168.0.254" placeholder="192.168.0.1-254">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="btn-group" role="group">
                                <button id="refreshMiners" class="btn btn-primary">Сканировать</button>
                                <button id="selectAll" class="btn btn-secondary">Выбрать все</button>
                                <button id="deselectAll" class="btn btn-secondary">Снять выбор</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6>Массовые операции:</h6>
                            <div class="btn-toolbar" role="toolbar">
                                <div class="btn-group mr-2" role="group">
                                    <button id="massRestart" class="btn btn-warning btn-sm" disabled>Перезапустить майнинг</button>
                                    <button id="massReboot" class="btn btn-danger btn-sm" disabled>Перезагрузить устройства</button>
                                    <button id="massPause" class="btn btn-info btn-sm" disabled>Приостановить майнинг</button>
                                    <button id="massStart" class="btn btn-success btn-sm" disabled>Запустить майнинг</button>
                                </div>
                                <div class="btn-group" role="group">
                                    <button id="massPoolSwitch" class="btn btn-secondary btn-sm" disabled>Переключить пул</button>
                                    <button id="massNetworkConfig" class="btn btn-secondary btn-sm" disabled>Настройка сети</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Обновление прошивки</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <select id="firmwarePlatform" class="form-control">
                                <option value="xil">Xilinx</option>
                                <option value="aml">Amlogic</option>
                                <option value="bb">BeagleBone</option>
                                <option value="cv">CVITEK</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="firmwareModel" class="form-control">
                                <option value="s19">S19</option>
                                <option value="s19pro">S19 Pro</option>
                                <option value="s19jpro">S19j Pro</option>
                                <option value="s19xp">S19 XP</option>
                                <option value="t19">T19</option>
                                <option value="s21">S21</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" id="firmwareVersion" class="form-control" placeholder="Версия прошивки">
                        </div>
                        <div class="col-md-3">
                            <input type="file" id="firmwareFile" class="form-control-file" accept=".tar,.tar.gz">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <button id="updateFirmware" class="btn btn-primary" disabled>Обновить прошивку выбранных</button>
                            <button id="removeFirmware" class="btn btn-danger" disabled>Удалить прошивку (сброс к заводским)</button>
                        </div>
                    </div>
                    <div id="firmwareStatus" class="mt-2 text-muted"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped table-hover table-sm" id="minersTable">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllCheckbox"></th>
                        <th>Модель</th>
                        <th>IP/MAC</th>
                        <th>Платформа/Прошивка</th>
                        <th>Статус</th>
                        <th>Хешрейт</th>
                        <th>Температура</th>
                        <th>Пул/Воркер</th>
                        <th>Доп. инфо</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

  </div>
  <!-- Include miners.js with absolute path -->
<script src="js/miners.js"></script>