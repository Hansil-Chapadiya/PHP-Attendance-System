<?php
// Simple test to check if PHP is outputting clean JSON
ob_start();

header('Content-Type: application/json; charset=utf-8');

// Simulate what the login endpoints do
require_once __DIR__ . '/backend/helpers.php';
require_once __DIR__ . '/backend/db_connect.php';

ob_end_clean();

echo json_encode([
    'status' => 'success',
    'message' => 'Test response - if you see this as clean JSON, the API is working correctly',
    'timestamp' => time()
]);
