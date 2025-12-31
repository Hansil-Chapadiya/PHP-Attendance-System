<?php
// Example configuration file - Copy this to config.php and update with your values
// DO NOT commit config.php to version control

return [
    'database' => [
        'host' => 'your_database_host',
        'name' => 'your_database_name',
        'user' => 'your_database_user',
        'password' => 'your_database_password',
        'port' => 3306
    ],
    'jwt' => [
        'secret_key' => 'your-secret-key-change-this-to-random-string-minimum-32-characters',
        'algorithm' => 'HS256',
        'expiry' => 86400 // 24 hours in seconds
    ],
    'session' => [
        'lifetime' => 86400, // 24 hours
        'secure' => true, // Set to true in production with HTTPS
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
