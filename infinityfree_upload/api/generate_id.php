<?php
// CORS headers FIRST (before any other code)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Prevent output buffering and force JSON
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../backend/helpers.php';
require_once __DIR__ . '/../backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Require faculty authentication
    $user = Auth::requireRole('faculty');
    $faculty_id = $user['user_id'];

    // Get raw POST data
    $postData = file_get_contents("php://input");
    $data = json_decode($postData, true);

    // Check if required fields are provided
    if (!isset($data['branch'], $data['division'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Branch and division are required']);
        exit;
    }

    $branch = $data['branch'];
    $division = $data['division'];

    // Validate branch and division
    $branchValidation = Validator::validateBranch($branch);
    if (!$branchValidation['valid']) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $branchValidation['message']]);
        exit;
    }
    $branch = $branchValidation['value'];

    $divisionValidation = Validator::validateDivision($division);
    if (!$divisionValidation['valid']) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $divisionValidation['message']]);
        exit;
    }
    $division = $divisionValidation['value'];

    // Verify faculty belongs to the branch
    $stmt = $conn->prepare("SELECT faculty_id, branch FROM faculty WHERE user_id = ?");
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Faculty record not found']);
        exit;
    }
    
    $faculty_data = $result->fetch_assoc();
    $faculty_record_id = $faculty_data['faculty_id']; // Get the actual faculty table ID
    if ($faculty_data['branch'] !== $branch) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'You can only create classes for your assigned branch']);
        exit;
    }
    $stmt->close();

    // Generate a unique class ID
    $class_id = strtoupper(substr($branch, 0, 3)) . '-' . strtoupper($division) . '-' . time();

    // Get the IP address of the faculty
    $faculty_ip = NetworkHelper::getClientIP();

    // Load config for session duration
    $config = require __DIR__ . '/../backend/config.php';
    $session_duration = $config['class']['session_duration'];
    
    // Calculate expiry time
    $created_at = date('Y-m-d H:i:s');
    $expires_at = date('Y-m-d H:i:s', time() + $session_duration);

    // Insert into classes table using prepared statement (use faculty_record_id, not faculty_id which is user_id)
    $stmt = $conn->prepare("INSERT INTO `classes` (class_id, branch, division, faculty_in_charge, faculty_ip, created_at, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisss", $class_id, $branch, $division, $faculty_record_id, $faculty_ip, $created_at, $expires_at);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Class session created successfully',
            'class_id' => $class_id,
            'faculty_ip' => $faculty_ip,
            'expires_at' => $expires_at,
            'valid_for_minutes' => ($session_duration / 60)
        ]);
    } else {
        error_log("Error creating class: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to create class session']);
    }
    
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
