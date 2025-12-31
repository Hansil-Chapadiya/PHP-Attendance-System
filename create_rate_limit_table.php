<?php
// Temporary script to create rate_limit table

$config = require __DIR__ . '/backend/config.php';

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

$sql = "CREATE TABLE IF NOT EXISTS `rate_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `attempt_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_identifier_time` (`identifier`, `attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql)) {
    echo "✓ rate_limit table created successfully\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// Verify it exists
$result = $conn->query("SHOW TABLES LIKE 'rate_limit'");
if ($result->num_rows > 0) {
    echo "✓ Verified: rate_limit table exists\n";
} else {
    echo "✗ Error: rate_limit table not found\n";
}

$conn->close();
