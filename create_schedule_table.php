<?php
require_once __DIR__ . '/backend/db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `subject` varchar(100) NOT NULL,
  `division` varchar(10) NOT NULL,
  `time_slot` varchar(50) DEFAULT NULL,
  `faculty_id` int(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_faculty_day` (`faculty_id`, `day_of_week`),
  KEY `idx_division_day` (`division`, `day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql)) {
    echo "✓ Schedule table created successfully!\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// Verify the table exists
$result = $conn->query("SHOW TABLES LIKE 'schedule'");
if ($result->num_rows > 0) {
    echo "✓ Schedule table verified\n";
} else {
    echo "✗ Schedule table not found\n";
}

$conn->close();
