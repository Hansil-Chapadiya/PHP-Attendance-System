<?php
// Test generate_id endpoint manually

// First, login as faculty
$ch = curl_init('http://localhost/Hansil/PHP-Attendance-System/api/faculty_login.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'username' => 'test_faculty_1767084692',
    'password' => 'TestPass123'
]));

$response = curl_exec($ch);
$login_data = json_decode($response, true);
curl_close($ch);

if (!isset($login_data['token'])) {
    die("Login failed: " . json_encode($login_data));
}

echo "Faculty login successful!\n";
$token = $login_data['token'];

// Now test generate_id
$ch = curl_init('http://localhost/Hansil/PHP-Attendance-System/api/generate_id.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'branch' => 'Computer Science',
    'division' => 'A'
]));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\nGenerate Class ID Test:\n";
echo "HTTP Code: $http_code\n";
echo "Response: $response\n";
