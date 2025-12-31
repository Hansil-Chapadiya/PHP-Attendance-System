<?php
/**
 * Production Database Test
 * Upload this file to InfinityFree and access it via browser
 * Example: https://yoursite.infinityfreeapp.com/test_production_db.php
 */

header('Content-Type: application/json');

// Production database credentials
$host = 'sql207.infinityfree.com';
$dbname = 'if0_40793832_attendance';
$username = 'if0_40793832';
$password = '1LadPbIbHs5ZU';
$port = 3306;

$response = [
    'timestamp' => date('Y-m-d H:i:s'),
    'database' => $dbname,
    'host' => $host
];

// Test connection
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    http_response_code(500);
    $response['status'] = 'error';
    $response['message'] = 'Connection failed: ' . $conn->connect_error;
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

$response['status'] = 'success';
$response['message'] = 'Database connected successfully';

// Check tables
$tables = ['students', 'faculty', 'classes', 'attendance', 'rate_limit'];
$response['tables'] = [];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    $exists = ($result && $result->num_rows > 0);
    
    $tableInfo = ['exists' => $exists];
    
    if ($exists) {
        $countResult = $conn->query("SELECT COUNT(*) as count FROM `$table`");
        if ($countResult) {
            $row = $countResult->fetch_assoc();
            $tableInfo['records'] = (int)$row['count'];
        }
    }
    
    $response['tables'][$table] = $tableInfo;
}

// Test a simple query
$result = $conn->query("SELECT COUNT(*) as total FROM students");
if ($result) {
    $row = $result->fetch_assoc();
    $response['students_count'] = (int)$row['total'];
}

$result = $conn->query("SELECT COUNT(*) as total FROM faculty");
if ($result) {
    $row = $result->fetch_assoc();
    $response['faculty_count'] = (int)$row['total'];
}

$conn->close();

echo json_encode($response, JSON_PRETTY_PRINT);
