<?php
// Direct test without browser
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up minimal environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/test.php';

// Create a mock authorization header using getallheaders fallback
function getallheaders() {
    return ['Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoyNCwidXNlcm5hbWUiOiJzdHVkZW50MSIsInJvbGUiOiJmYWN1bHR5IiwiaWF0IjoxNzY3MTg5MDg3LCJleHAiOjE3NjcyNzU0ODd9.test'];
}

echo "=== Testing Faculty Schedule API ===\n\n";

// Capture output
ob_start();

try {
    include 'api/get_faculty_schedule.php';
    $output = ob_get_clean();
    echo "SUCCESS!\n";
    echo "Output: " . $output . "\n";
} catch (Throwable $e) {
    $output = ob_get_clean();
    echo "ERROR!\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nOutput before error:\n" . $output . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
