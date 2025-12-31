<?php
// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: application/json');

$diagnostics = [
    'php_version' => phpversion(),
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => []
];

// Test 1: Check if files exist
$diagnostics['tests']['files_exist'] = [
    'backend/config.php' => file_exists(__DIR__ . '/backend/config.php'),
    'backend/db_connect.php' => file_exists(__DIR__ . '/backend/db_connect.php'),
    'backend/helpers.php' => file_exists(__DIR__ . '/backend/helpers.php'),
];

// Test 2: Try to load config
try {
    if (file_exists(__DIR__ . '/backend/config.php')) {
        $config = require __DIR__ . '/backend/config.php';
        $diagnostics['tests']['config_loaded'] = true;
        $diagnostics['tests']['config_has_db'] = isset($config['database']);
    } else {
        $diagnostics['tests']['config_loaded'] = false;
        $diagnostics['tests']['config_error'] = 'config.php not found';
    }
} catch (Exception $e) {
    $diagnostics['tests']['config_loaded'] = false;
    $diagnostics['tests']['config_error'] = $e->getMessage();
}

// Test 3: Try to load helpers
try {
    require_once __DIR__ . '/backend/helpers.php';
    $diagnostics['tests']['helpers_loaded'] = true;
} catch (Exception $e) {
    $diagnostics['tests']['helpers_loaded'] = false;
    $diagnostics['tests']['helpers_error'] = $e->getMessage();
}

// Test 4: Try database connection
try {
    require_once __DIR__ . '/backend/db_connect.php';
    if (isset($conn) && $conn instanceof mysqli) {
        $diagnostics['tests']['db_connected'] = true;
        $diagnostics['tests']['db_host_info'] = $conn->host_info;
    } else {
        $diagnostics['tests']['db_connected'] = false;
        $diagnostics['tests']['db_error'] = 'Connection object not created';
    }
} catch (Exception $e) {
    $diagnostics['tests']['db_connected'] = false;
    $diagnostics['tests']['db_error'] = $e->getMessage();
}

// Test 5: Check required PHP extensions
$diagnostics['tests']['php_extensions'] = [
    'mysqli' => extension_loaded('mysqli'),
    'json' => extension_loaded('json'),
    'mbstring' => extension_loaded('mbstring'),
];

echo json_encode($diagnostics, JSON_PRETTY_PRINT);
?>
