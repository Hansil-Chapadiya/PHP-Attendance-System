<?php
require_once __DIR__ . '/backend/db_connect.php';

echo "Schedule Table Structure:\n";
echo str_repeat('=', 60) . "\n";

$result = $conn->query('DESCRIBE schedule');
while ($row = $result->fetch_assoc()) {
    echo sprintf("%-15s %-30s %-10s\n", $row['Field'], $row['Type'], $row['Key']);
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Sample Schedule Data:\n";
echo str_repeat('=', 60) . "\n";

$result = $conn->query('SELECT day_of_week, subject, division, semester, time_slot FROM schedule LIMIT 5');
while ($row = $result->fetch_assoc()) {
    echo sprintf("%-10s | Sem %-2s | Div %s | %-25s | %s\n", 
        $row['day_of_week'], 
        $row['semester'], 
        $row['division'], 
        $row['subject'],
        $row['time_slot']
    );
}

$conn->close();
