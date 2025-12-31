<?php
$config = require __DIR__ . '/backend/config.php';
$conn = new mysqli($config['database']['host'], $config['database']['user'], $config['database']['password'], $config['database']['name'], $config['database']['port']);

echo "Checking attendance table...\n\n";

// Check current structure
$result = $conn->query("DESCRIBE attendance");
$columns = [];
while($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
    echo "  - " . $row['Field'] . "\n";
}

// Add student_ip column if missing
if (!in_array('student_ip', $columns)) {
    echo "\nAdding student_ip column...\n";
    $sql = "ALTER TABLE attendance ADD COLUMN student_ip VARCHAR(15) NULL AFTER status";
    if ($conn->query($sql)) {
        echo "✓ Added student_ip column\n";
    } else {
        echo "❌ Error: " . $conn->error . "\n";
    }
} else {
    echo "\n✓ student_ip column already exists\n";
}

$conn->close();
