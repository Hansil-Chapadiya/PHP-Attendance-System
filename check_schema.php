<?php
$config = require __DIR__ . '/backend/config.php';
$conn = new mysqli($config['database']['host'], $config['database']['user'], $config['database']['password'], $config['database']['name'], $config['database']['port']);

$tables = ['user', 'students', 'faculty', 'classes', 'attendance', 'rate_limit'];

foreach($tables as $t) {
    echo "\n=== $t ===\n";
    $r = $conn->query("DESCRIBE $t");
    if ($r) {
        while($row = $r->fetch_assoc()) {
            echo $row['Field'] . ' (' . $row['Type'] . ') ' . ($row['Null'] == 'NO' ? 'NOT NULL' : 'NULL') . "\n";
        }
    } else {
        echo "Table does not exist\n";
    }
}

$conn->close();
