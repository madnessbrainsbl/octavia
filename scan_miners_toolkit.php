<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Path to toolkit_cli
$toolkit_path = __DIR__ . DIRECTORY_SEPARATOR . 'toolkit_cli';

// Check if toolkit_cli exists
if (!file_exists($toolkit_path)) {
    echo json_encode(['error' => 'toolkit_cli not found', 'path' => $toolkit_path]);
    exit;
}

// Make toolkit_cli executable on Linux/Unix
if (PHP_OS_FAMILY !== 'Windows') {
    chmod($toolkit_path, 0755);
}

// Get IP ranges - support both individual IPs and ranges
$ips = isset($_GET['ips']) ? $_GET['ips'] : (isset($_GET['network']) ? $_GET['network'] : '100.184.143.142,100.184.143.145');

// Convert individual IPs to ranges if needed
$ip_ranges = [];
if (strpos($ips, '-') === false) {
    // Individual IPs provided, convert to ranges
    $ip_array = array_map('trim', explode(',', $ips));
    foreach ($ip_array as $ip) {
        $ip_ranges[] = $ip . '-' . $ip;
    }
} else {
    // Already in range format
    $ip_ranges = array_map('trim', explode(',', $ips));
}

// Build export command with IP ranges
$cmd = escapeshellarg($toolkit_path) . " export -f json";
foreach ($ip_ranges as $range) {
    $cmd .= " -i " . escapeshellarg($range);
}

// Execute command
$output = shell_exec($cmd . ' 2>&1');

error_log("Toolkit command: $cmd");
error_log("Toolkit output: $output");

if ($output === null || $output === false) {
    echo json_encode([
        'error' => 'Failed to execute toolkit_cli command', 
        'command' => $cmd, 
        'details' => 'Command returned null'
    ]);
    exit;
}

// Try to parse JSON output from toolkit_cli
$json_output = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo json_encode($json_output);
} else {
    // If not JSON, return raw output
    echo json_encode([
        'raw_output' => $output,
        'command' => $cmd,
        'ips' => $ips
    ]);
}
