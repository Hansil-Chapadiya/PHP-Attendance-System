<?php
// Add subject column to classes table
require_once __DIR__ . '/backend/db_connect.php';

try {
    // Add subject column
    $sql = "ALTER TABLE `classes` ADD COLUMN IF NOT EXISTS `subject` VARCHAR(100) DEFAULT NULL AFTER `division`";
    
    if ($conn->query($sql)) {
        echo "✅ Subject column added successfully to classes table\n";
    } else {
        echo "❌ Error: " . $conn->error . "\n";
    }
    
    // Show current structure
    $result = $conn->query("DESCRIBE classes");
    echo "\n📋 Current classes table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

$conn->close();
?>
