<?php
require_once __DIR__ . '/backend/db_connect.php';

echo "=== Recent Classes ===\n";
$result = $conn->query("SELECT class_id, branch, division, subject, faculty_in_charge, created_at FROM classes ORDER BY created_at DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== Recent Attendance ===\n";
$result = $conn->query("SELECT a.*, c.subject, c.division FROM attendance a JOIN classes c ON a.class_id = c.class_id ORDER BY a.marked_time DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
?>
