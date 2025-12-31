<?php
// Configuration file - Update with your actual values
// Add this file to .gitignore

return [
    'database' => [
        // Production Database (InfinityFree) - Only works from their servers
        // 'host' => getenv('DB_HOST') ?: 'sql207.infinityfree.com',
        // 'name' => getenv('DB_NAME') ?: 'if0_40793832_attendance',
        // 'user' => getenv('DB_USER') ?: 'if0_40793832',
        // 'password' => getenv('DB_PASSWORD') ?: '1LadPbIbHs5ZU',
        // 'port' => getenv('DB_PORT') ?: 3306
        
        // Local Database (XAMPP) - For local development
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'attendance_system_php_mini_updated',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'port' => getenv('DB_PORT') ?: 3307
    ],
    'jwt' => [
        'secret_key' => getenv('JWT_SECRET') ?: 'change-this-to-a-very-long-random-secret-key-for-production-use',
        'algorithm' => 'HS256',
        'expiry' => 86400 // 24 hours in seconds
    ],
    'session' => [
        'lifetime' => 86400, // 24 hours
        'secure' => false, // Set to true in production with HTTPS
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
