<?php
// Super simple test - shows actual PHP errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

echo "=== PHP Error Test ===\n\n";

echo "Step 1: Basic PHP working...\n";
echo "PHP Version: " . phpversion() . "\n\n";

echo "Step 2: Checking files...\n";
echo "helpers.php exists: " . (file_exists(__DIR__ . '/backend/helpers.php') ? 'YES' : 'NO') . "\n";
echo "db_connect.php exists: " . (file_exists(__DIR__ . '/backend/db_connect.php') ? 'YES' : 'NO') . "\n";
echo "config.php exists: " . (file_exists(__DIR__ . '/backend/config.php') ? 'YES' : 'NO') . "\n\n";

echo "Step 3: Loading helpers.php...\n";
try {
    require_once __DIR__ . '/backend/helpers.php';
    echo "SUCCESS: helpers.php loaded\n\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    die();
}

echo "Step 4: Loading db_connect.php...\n";
try {
    require_once __DIR__ . '/backend/db_connect.php';
    echo "SUCCESS: db_connect.php loaded\n";
    echo "Connection status: " . (isset($conn) ? 'CREATED' : 'NOT CREATED') . "\n\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
}

echo "=== Test Complete ===\n";
echo "If you see this message, PHP is working!\n";
?>
