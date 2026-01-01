<?php
require_once __DIR__ . '/backend/db_connect.php';

echo "Adding semester column to schedule table...\n";

$sql = "ALTER TABLE schedule ADD COLUMN semester INT(11) DEFAULT NULL AFTER division";

if ($conn->query($sql)) {
    echo "✓ Semester column added successfully!\n";
} else {
    if (strpos($conn->error, 'Duplicate column') !== false) {
        echo "⚠ Semester column already exists\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n";
    }
}

$conn->close();
