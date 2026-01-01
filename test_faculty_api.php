<?php
// Test faculty schedule API
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate GET request
$_SERVER['REQUEST_METHOD'] = 'GET';

// Mock token for testing
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test_token_here';

echo "Testing get_faculty_schedule.php\n";
echo "=====================================\n\n";

try {
    ob_start();
    include 'api/get_faculty_schedule.php';
    $output = ob_get_clean();
    
    echo "Output:\n";
    echo $output . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}
