<?php
/**
 * Database Connection Test
 * Tests production database connectivity
 */

header('Content-Type: application/json');

// Load configuration
$config = require __DIR__ . '/backend/config.php';

echo "Testing Database Connection...\n\n";
echo "Configuration:\n";
echo "Host: " . $config['database']['host'] . "\n";
echo "Database: " . $config['database']['name'] . "\n";
echo "User: " . $config['database']['user'] . "\n";
echo "Port: " . $config['database']['port'] . "\n\n";

// Attempt connection
$conn = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['name'],
    $config['database']['port']
);

if ($conn->connect_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Connection failed: ' . $conn->connect_error,
        'errno' => $conn->connect_errno
    ], JSON_PRETTY_PRINT);
    exit(1);
}

echo "✓ Connection successful!\n\n";

// Check tables
$tables = ['users', 'students', 'faculty', 'classes', 'attendance', 'rate_limit'];
echo "Checking tables:\n";

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✓ Table '$table' exists\n";
    } else {
        echo "✗ Table '$table' NOT FOUND\n";
    }
}

echo "\n";

// Get table counts
echo "Table counts:\n";
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "  $table: " . $row['count'] . " records\n";
    }
}

echo "\n";
echo json_encode([
    'status' => 'success',
    'message' => 'Database connection and tables verified',
    'database' => $config['database']['name'],
    'host' => $config['database']['host']
], JSON_PRETTY_PRINT);

$conn->close();
