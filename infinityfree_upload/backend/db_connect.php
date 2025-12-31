<?php
// Load configuration - supports both config file and environment variables
$config_file = __DIR__ . '/config.php';

if (file_exists($config_file)) {
    // Local development - use config.php
    $config = require_once $config_file;
    $host = $config['database']['host'];
    $db_name = $config['database']['name'];
    $username = $config['database']['user'];
    $password = $config['database']['password'];
    $port = $config['database']['port'];
} else {
    // Production (Vercel) - use environment variables directly
    $host = getenv('DB_HOST') ?: 'localhost';
    $db_name = getenv('DB_NAME') ?: '';
    $username = getenv('DB_USER') ?: '';
    $password = getenv('DB_PASSWORD') ?: '';
    $port = getenv('DB_PORT') ?: 3306;
}

// Create mysqli connection
$conn = mysqli_connect($host, $username, $password, $db_name, $port);

// Check connection
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");