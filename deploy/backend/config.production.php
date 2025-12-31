<?php
// Production Configuration for InfinityFree
// Rename this to config.php after uploading

return [
    'database' => [
        'host' => 'sql207.infinityfree.com',
        'name' => 'if0_40793832_attendance',
        'user' => 'if0_40793832',
        'password' => '1LadPbIbHs5ZU',
        'port' => 3306
    ],
    'jwt' => [
        'secret_key' => '6b99a0109fdb88a8fb741ae7e9e9eb9744fe619bd3689e8c41690dd4fec49069',
        'algorithm' => 'HS256',
        'expiry' => 86400 // 24 hours in seconds
    ],
    'session' => [
        'lifetime' => 86400, // 24 hours
        'secure' => true, // HTTPS enabled on InfinityFree
        'httponly' => true,
        'samesite' => 'Strict'
    ],
    'class' => [
        'session_duration' => 7200 // 2 hours in seconds
    ],
    'rate_limit' => [
        'max_attempts' => 5,
        'lockout_time' => 900 // 15 minutes
    ]
];
