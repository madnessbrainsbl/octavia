<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$hst_path = 'hst';
if (file_exists('/usr/local/bin/hst')) {
    $hst_path = '/usr/local/bin/hst';
} elseif (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'hst')) {
    $hst_path = __DIR__ . DIRECTORY_SEPARATOR . 'hst';
} elseif (PHP_OS_FAMILY === 'Windows' && file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'hst.exe')) {
    $hst_path = __DIR__ . DIRECTORY_SEPARATOR . 'hst.exe';
}

$ip = '192.168.100.26';
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}
$uploadDir = sys_get_temp_dir() . '/firmware_uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
$filename = basename($_FILES['file']['name']);
$target = "$uploadDir/$filename";
move_uploaded_file($_FILES['file']['tmp_name'], $target);
$cmd = escapeshellarg($hst_path) . " flash -i $ip -n " . escapeshellarg($name) . " " . escapeshellarg($target);
$output = shell_exec($cmd . ' 2>&1');
echo json_encode(['output' => $output]);
exit;
