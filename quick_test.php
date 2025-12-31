<?php
// Quick test of display_profile with actual token

// First, login to get a token
$ch = curl_init('http://localhost/Hansil/PHP-Attendance-System/api/stud_login.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'username' => 'testuser123',
    'password' => 'TestPass123'
]));

$response = curl_exec($ch);
$login_data = json_decode($response, true);
curl_close($ch);

if (!isset($login_data['token'])) {
    die("Login failed: " . json_encode($login_data));
}

echo "Login successful!\n";
echo "Token: " . substr($login_data['token'], 0, 20) . "...\n\n";

// Now test display_profile
$token = $login_data['token'];
$ch = curl_init('http://localhost/Hansil/PHP-Attendance-System/api/display_profile.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Display Profile Test:\n";
echo "HTTP Code: $http_code\n";
echo "Response: $response\n";
