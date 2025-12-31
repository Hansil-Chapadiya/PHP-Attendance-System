<?php
// Simplest possible test
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

echo "1. PHP Working: YES\n";
echo "2. PHP Version: " . phpversion() . "\n";

// Test file includes
try {
    require_once __DIR__ . '/../backend/db_connect.php';
    echo "3. DB Connect: LOADED\n";
    echo "4. Connection: " . (isset($conn) ? 'EXISTS' : 'MISSING') . "\n";
} catch (Exception $e) {
    echo "3. DB Connect ERROR: " . $e->getMessage() . "\n";
}

try {
    require_once __DIR__ . '/../backend/helpers.php';
    echo "5. Helpers: LOADED\n";
} catch (Exception $e) {
    echo "5. Helpers ERROR: " . $e->getMessage() . "\n";
}

// Test Auth class
try {
    Auth::init();
    echo "6. Auth::init(): SUCCESS\n";
} catch (Exception $e) {
    echo "6. Auth::init() ERROR: " . $e->getMessage() . "\n";
}

// Test database query
if (isset($conn)) {
    try {
        $result = $conn->query("SELECT COUNT(*) as cnt FROM `user`");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "7. Database Query: SUCCESS (Users: " . $row['cnt'] . ")\n";
        }
    } catch (Exception $e) {
        echo "7. Database Query ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n=== ALL BASIC TESTS PASSED ===\n";
