<?php
$config = require __DIR__ . '/backend/config.php';
$conn = new mysqli($config['database']['host'], $config['database']['user'], $config['database']['password'], $config['database']['name'], $config['database']['port']);

echo "Checking faculty records...\n\n";

$result = $conn->query("SELECT f.faculty_id, f.user_id, u.username, u.full_name, f.branch FROM faculty f JOIN user u ON f.user_id = u.user_id ORDER BY f.faculty_id DESC LIMIT 5");

while ($row = $result->fetch_assoc()) {
    echo "Faculty ID: " . $row['faculty_id'] . "\n";
    echo "  User ID: " . $row['user_id'] . "\n";
    echo "  Username: " . $row['username'] . "\n";
    echo "  Name: " . $row['full_name'] . "\n";
    echo "  Branch: " . ($row['branch'] ?? 'NULL') . "\n\n";
}

$conn->close();
