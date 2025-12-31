<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== LOGIN TEST ===\n\n";

// Test student login
$url = 'http://localhost/Hansil/PHP-Attendance-System/api/stud_login.php';
$data = json_encode(['username' => 'test_student', 'password' => 'password123']);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

echo "Testing Student Login...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: " . print_r(json_decode($response, true), true) . "\n\n";

// Test faculty login  
$url = 'http://localhost/Hansil/PHP-Attendance-System/api/faculty_login.php';
$data = json_encode(['username' => 'test_faculty', 'password' => 'password123']);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

echo "Testing Faculty Login...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: " . print_r(json_decode($response, true), true) . "\n";
