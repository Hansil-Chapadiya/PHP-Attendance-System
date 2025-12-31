<?php
/**
 * Database Setup Script
 * Run this once to initialize the database with required tables
 * 
 * Usage: php backend/setup_database.php
 */

// Load configuration
$config = require __DIR__ . '/config.php';

// Connect to database
$conn = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['name'],
    $config['database']['port']
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to database successfully.\n\n";

// Read and execute SQL file
$sql = file_get_contents(__DIR__ . '/schema_updates.sql');

if ($sql === false) {
    die("Error: Could not read schema_updates.sql\n");
}

// Split into individual queries
$queries = array_filter(array_map('trim', explode(';', $sql)));

$success_count = 0;
$error_count = 0;

foreach ($queries as $query) {
    if (empty($query) || strpos($query, '--') === 0) {
        continue;
    }
    
    echo "Executing: " . substr($query, 0, 50) . "...\n";
    
    if ($conn->query($query)) {
        $success_count++;
        echo "✓ Success\n\n";
    } else {
        // Ignore "already exists" errors
        if (strpos($conn->error, 'already exists') !== false || 
            strpos($conn->error, 'Duplicate') !== false) {
            echo "⚠ Already exists (skipping)\n\n";
        } else {
            $error_count++;
            echo "✗ Error: " . $conn->error . "\n\n";
        }
    }
}

echo "========================================\n";
echo "Database setup complete!\n";
echo "Successful: $success_count\n";
echo "Errors: $error_count\n";
echo "========================================\n\n";

// Verify tables exist
echo "Verifying tables...\n";
$required_tables = ['user', 'students', 'faculty', 'classes', 'attendance', 'rate_limit'];

foreach ($required_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✓ Table '$table' exists\n";
    } else {
        echo "✗ Table '$table' NOT FOUND - you may need to create it manually\n";
    }
}

echo "\nSetup complete! You can now use the attendance system.\n";

$conn->close();
