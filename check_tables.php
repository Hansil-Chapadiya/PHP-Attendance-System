<?php
require 'backend/db_connect.php';

echo "Faculty table structure:\n";
echo str_repeat('=', 50) . "\n";
$result = $conn->query('DESCRIBE faculty');
while ($row = $result->fetch_assoc()) {
    echo sprintf("%-20s %-20s %-10s\n", $row['Field'], $row['Type'], $row['Key']);
}

echo "\n\nStudents table structure:\n";
echo str_repeat('=', 50) . "\n";
$result = $conn->query('DESCRIBE students');
while ($row = $result->fetch_assoc()) {
    echo sprintf("%-20s %-20s %-10s\n", $row['Field'], $row['Type'], $row['Key']);
}
