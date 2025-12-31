<?php
// Load configuration
$config = require_once __DIR__ . '/config.php';

// Extract database configuration
$host = $config['database']['host'];
$db_name = $config['database']['name'];
$username = $config['database']['user'];
$password = $config['database']['password'];
$port = $config['database']['port'];

// Create mysqli connection
$conn = mysqli_connect($host, $username, $password, $db_name, $port);

// Check connection
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");