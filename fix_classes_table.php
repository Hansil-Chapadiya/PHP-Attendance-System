<?php
$config = require __DIR__ . '/backend/config.php';
$conn = new mysqli($config['database']['host'], $config['database']['user'], $config['database']['password'], $config['database']['name'], $config['database']['port']);

echo "Adding missing columns to classes table...\n\n";

// Check current structure
$result = $conn->query("DESCRIBE classes");
echo "Current columns:\n";
while($row = $result->fetch_assoc()) {
    echo "  - " . $row['Field'] . "\n";
}

// Add created_at column
$sql = "ALTER TABLE classes ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
if ($conn->query($sql)) {
    echo "\n✓ Added created_at column\n";
} else {
    if (strpos($conn->error, 'Duplicate') !== false) {
        echo "\n⚠ created_at column already exists\n";
    } else {
        echo "\n❌ Error adding created_at: " . $conn->error . "\n";
    }
}

// Add expires_at column
$sql = "ALTER TABLE classes ADD COLUMN expires_at TIMESTAMP NULL DEFAULT NULL";
if ($conn->query($sql)) {
    echo "✓ Added expires_at column\n";
} else {
    if (strpos($conn->error, 'Duplicate') !== false) {
        echo "⚠ expires_at column already exists\n";
    } else {
        echo "❌ Error adding expires_at: " . $conn->error . "\n";
    }
}

// Check new structure
echo "\nNew structure:\n";
$result = $conn->query("DESCRIBE classes");
while($row = $result->fetch_assoc()) {
    echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

$conn->close();
