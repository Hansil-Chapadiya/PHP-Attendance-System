<?php
/**
 * API Endpoint Testing Script
 * Tests all endpoints to verify they work correctly
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide PHP errors for cleaner output

$base_url = "http://localhost/Hansil/PHP-Attendance-System/api/";
$test_results = [];

echo "===========================================\n";
echo "   API ENDPOINT TESTING\n";
echo "===========================================\n\n";

// Helper function to make API calls
function callAPI($endpoint, $data = null, $method = 'POST', $token = null) {
    global $base_url;
    
    $ch = curl_init($base_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_VERBOSE, false); // Disable verbose for cleaner output
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'GET') {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    return [
        'code' => $http_code,
        'body' => json_decode($response, true),
        'raw' => $response,
        'error' => $curl_error
    ];
}

// Generate unique test usernames
$timestamp = time();
$student_username = "test_student_$timestamp";
$faculty_username = "test_faculty_$timestamp";

// Test 1: Register Student
echo "1. Testing Student Registration\n";
echo "-------------------------------------------\n";
$result = callAPI('register_user.php', [
    'username' => $student_username,
    'password' => 'TestPass123',
    'full_name' => 'Test Student',
    'role' => 'student',
    'branch' => 'Computer Science',
    'division' => 'A',
    'semester' => 5
]);

if ($result['code'] == 201 && isset($result['body']['status']) && $result['body']['status'] === 'success') {
    echo "✓ Student registration successful\n";
    echo "  User ID: " . ($result['body']['user_id'] ?? 'N/A') . "\n";
    $student_token = $result['body']['token'] ?? null;
    echo "  Token received: " . ($student_token ? 'Yes' : 'No') . "\n";
} else {
    echo "❌ Student registration failed\n";
    echo "  HTTP Code: " . $result['code'] . "\n";
    echo "  Response: " . json_encode($result['body']) . "\n";
    $student_token = null;
}
echo "\n";

// Test 2: Register Faculty
echo "2. Testing Faculty Registration\n";
echo "-------------------------------------------\n";
$result = callAPI('register_user.php', [
    'username' => $faculty_username,
    'password' => 'TestPass123',
    'full_name' => 'Test Faculty',
    'role' => 'faculty',
    'branch' => 'Computer Science'
]);

if ($result['code'] == 201 && isset($result['body']['status']) && $result['body']['status'] === 'success') {
    echo "✓ Faculty registration successful\n";
    echo "  User ID: " . ($result['body']['user_id'] ?? 'N/A') . "\n";
    $faculty_token = $result['body']['token'] ?? null;
    echo "  Token received: " . ($faculty_token ? 'Yes' : 'No') . "\n";
} else {
    echo "❌ Faculty registration failed\n";
    echo "  HTTP Code: " . $result['code'] . "\n";
    echo "  Response: " . json_encode($result['body']) . "\n";
    $faculty_token = null;
}
echo "\n";

// Test 3: Student Login
echo "3. Testing Student Login\n";
echo "-------------------------------------------\n";
$result = callAPI('stud_login.php', [
    'username' => $student_username,
    'password' => 'TestPass123'
]);

if ($result['code'] == 200 && isset($result['body']['status']) && $result['body']['status'] === 'success') {
    echo "✓ Student login successful\n";
    echo "  Username: " . ($result['body']['username'] ?? 'N/A') . "\n";
    echo "  Full Name: " . ($result['body']['full_name'] ?? 'N/A') . "\n";
    $student_token = $result['body']['token'] ?? $student_token;
} else {
    echo "❌ Student login failed\n";
    echo "  HTTP Code: " . $result['code'] . "\n";
    echo "  Response: " . json_encode($result['body']) . "\n";
}
echo "\n";

// Test 4: Faculty Login
echo "4. Testing Faculty Login\n";
echo "-------------------------------------------\n";
$result = callAPI('faculty_login.php', [
    'username' => $faculty_username,
    'password' => 'TestPass123'
]);

if ($result['code'] == 200 && isset($result['body']['status']) && $result['body']['status'] === 'success') {
    echo "✓ Faculty login successful\n";
    echo "  Username: " . ($result['body']['username'] ?? 'N/A') . "\n";
    echo "  Full Name: " . ($result['body']['full_name'] ?? 'N/A') . "\n";
    $faculty_token = $result['body']['token'] ?? $faculty_token;
} else {
    echo "❌ Faculty login failed\n";
    echo "  HTTP Code: " . $result['code'] . "\n";
    echo "  Response: " . json_encode($result['body']) . "\n";
}
echo "\n";

// Test 5: Display Profile (Student)
if ($student_token) {
    echo "5. Testing Display Profile (Student)\n";
    echo "-------------------------------------------\n";
    $result = callAPI('display_profile.php', null, 'GET', $student_token);
    
    if ($result['code'] == 200 && isset($result['body']['status']) && $result['body']['status'] === 'success') {
        echo "✓ Profile retrieval successful\n";
        echo "  Role: " . ($result['body']['data']['role'] ?? 'N/A') . "\n";
        echo "  Branch: " . ($result['body']['data']['branch'] ?? 'N/A') . "\n";
        echo "  Division: " . ($result['body']['data']['division'] ?? 'N/A') . "\n";
        echo "  Semester: " . ($result['body']['data']['semester'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Profile retrieval failed\n";
        echo "  HTTP Code: " . $result['code'] . "\n";
        echo "  Response: " . json_encode($result['body']) . "\n";
    }
    echo "\n";
}

// Test 6: Generate Class ID (Faculty)
if ($faculty_token) {
    echo "6. Testing Generate Class ID (Faculty)\n";
    echo "-------------------------------------------\n";
    $result = callAPI('generate_id.php', [
        'branch' => 'Computer Science',
        'division' => 'A'
    ], 'POST', $faculty_token);
    
    if ($result['code'] == 201 && isset($result['body']['status']) && $result['body']['status'] === 'success') {
        echo "✓ Class ID generation successful\n";
        echo "  Class ID: " . ($result['body']['class_id'] ?? 'N/A') . "\n";
        echo "  Faculty IP: " . ($result['body']['faculty_ip'] ?? 'N/A') . "\n";
        echo "  Expires at: " . ($result['body']['expires_at'] ?? 'N/A') . "\n";
        $class_id = $result['body']['class_id'] ?? null;
    } else {
        echo "❌ Class ID generation failed\n";
        echo "  HTTP Code: " . $result['code'] . "\n";
        echo "  Response: " . json_encode($result['body']) . "\n";
        $class_id = null;
    }
    echo "\n";
}

// Test 7: Mark Attendance (Student)
if ($student_token && isset($class_id)) {
    echo "7. Testing Mark Attendance (Student)\n";
    echo "-------------------------------------------\n";
    $result = callAPI('mark_present.php', [
        'class_id' => $class_id
    ], 'POST', $student_token);
    
    if (($result['code'] == 201 || $result['code'] == 200 || $result['code'] == 409) && isset($result['body']['status'])) {
        if ($result['body']['status'] === 'success' || $result['body']['status'] === 'info') {
            echo "✓ Attendance marking processed\n";
            echo "  Message: " . ($result['body']['message'] ?? 'N/A') . "\n";
            if (isset($result['body']['marked_time'])) {
                echo "  Marked Time: " . $result['body']['marked_time'] . "\n";
            }
        } else {
            echo "⚠ Attendance marking: " . ($result['body']['message'] ?? 'Unknown response') . "\n";
        }
    } else {
        echo "❌ Attendance marking failed\n";
        echo "  HTTP Code: " . $result['code'] . "\n";
        echo "  Response: " . json_encode($result['body']) . "\n";
    }
    echo "\n";
}

// Test 8: Show Attendance (Student)
if ($student_token) {
    echo "8. Testing Show Attendance (Student)\n";
    echo "-------------------------------------------\n";
    $result = callAPI('show_attendance.php', null, 'GET', $student_token);
    
    if ($result['code'] == 200 && isset($result['body']['status']) && $result['body']['status'] === 'success') {
        echo "✓ Attendance history retrieved\n";
        echo "  Total records: " . ($result['body']['count'] ?? 0) . "\n";
        if (isset($result['body']['data']) && count($result['body']['data']) > 0) {
            echo "  Latest record:\n";
            $latest = $result['body']['data'][0];
            echo "    - Date: " . ($latest['date'] ?? 'N/A') . "\n";
            echo "    - Status: " . ($latest['status'] ?? 'N/A') . "\n";
            echo "    - Class ID: " . ($latest['class_id'] ?? 'N/A') . "\n";
        }
    } else {
        echo "❌ Attendance history retrieval failed\n";
        echo "  HTTP Code: " . $result['code'] . "\n";
        echo "  Response: " . json_encode($result['body']) . "\n";
    }
    echo "\n";
}

// Summary
echo "===========================================\n";
echo "   TESTING COMPLETE\n";
echo "===========================================\n";
echo "\nAll core API endpoints have been tested.\n";
echo "Review the results above for any failures.\n\n";
